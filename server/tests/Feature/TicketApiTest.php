<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_and_category_routes_require_authentication(): void
    {
        $user = User::factory()->create();
        $ticket = $user->tickets()->create([
            'category_id' => Category::factory()->create()->id,
            'title' => 'Chamado protegido',
            'description' => 'Descrição do chamado.',
        ]);

        $requests = [
            fn () => $this->getJson('/api/categories'),
            fn () => $this->getJson('/api/tickets'),
            fn () => $this->postJson('/api/tickets', []),
            fn () => $this->getJson("/api/tickets/{$ticket->id}"),
            fn () => $this->patchJson("/api/tickets/{$ticket->id}", []),
            fn () => $this->deleteJson("/api/tickets/{$ticket->id}"),
        ];

        foreach ($requests as $request) {
            $request()->assertUnauthorized();
        }
    }

    public function test_regular_user_sees_only_tickets_they_reported(): void
    {
        $user = User::factory()->create();
        $ownTicket = Ticket::factory()->create(['user_id' => $user->id]);
        Ticket::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $ownTicket->id);
    }

    public function test_administrator_can_see_all_tickets(): void
    {
        $admin = User::factory()->admin()->create();
        Ticket::factory()->count(2)->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_authenticated_user_can_create_a_ticket_as_the_owner(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/tickets', [
            'category_id' => $category->id,
            'title' => 'Acesso indisponível',
            'description' => 'Não consigo acessar o sistema.',
            'priority' => 'high',
        ])
            ->assertCreated()
            ->assertJsonPath('title', 'Acesso indisponível')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('category.id', $category->id)
            ->assertJsonPath('status', 'open');

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Acesso indisponível',
        ]);
    }

    public function test_ticket_creation_rejects_client_supplied_owner_and_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/tickets', [
            'user_id' => $otherUser->id,
            'category_id' => $category->id,
            'title' => 'Chamado com autor informado pelo cliente',
            'description' => 'A API deve rejeitar o autor enviado.',
            'status' => 'closed',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'status']);

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Chamado com autor informado pelo cliente',
        ]);
    }

    public function test_ticket_creation_rejects_missing_required_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tickets', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'category_id',
                'title',
                'description',
            ]);
    }

    public function test_regular_user_can_view_and_update_their_own_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'status' => 'open',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('id', $ticket->id);

        $this->patchJson("/api/tickets/{$ticket->id}", [
            'title' => 'Título atualizado',
        ])
            ->assertOk()
            ->assertJsonPath('title', 'Título atualizado')
            ->assertJsonPath('status', 'open');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Título atualizado',
            'status' => 'open',
        ]);
    }

    public function test_regular_user_cannot_view_or_update_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertNotFound();

        $this->patchJson("/api/tickets/{$ticket->id}", [
            'title' => 'Alteração não autorizada',
        ])->assertNotFound();
    }

    public function test_regular_user_cannot_change_ticket_status(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed',
        ])->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_administrator_can_validate_and_close_a_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'closed');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
        ]);
    }

    public function test_administrator_can_delete_a_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Chamado removido com sucesso.');

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_regular_user_cannot_delete_their_own_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/tickets/{$ticket->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_authenticated_user_can_list_categories(): void
    {
        Category::factory()->count(5)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => ['id', 'name'],
            ]);
    }

    public function test_ticket_list_can_filter_by_category_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $matchingTicket = Ticket::factory()->create([
            'category_id' => $category->id,
            'status' => 'open',
        ]);
        Ticket::factory()->create([
            'category_id' => $category->id,
            'status' => 'closed',
        ]);
        Ticket::factory()->create([
            'status' => 'open',
        ]);
        Sanctum::actingAs($admin);

        $this->getJson("/api/tickets?category_id={$category->id}&status=open")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $matchingTicket->id);
    }
}
