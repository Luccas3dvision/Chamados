<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Relacionamento: cria um usuário caso não seja informado
            'user_id' => User::factory(),

            // Relacionamento: cria uma categoria caso não seja informada
            'category_id' => Category::factory(),

            // Título resumido do chamado
            'title' => fake()->sentence(4),

            // Descrição fictícia do problema
            'description' => fake()->paragraph(),

            // Define aleatoriamente o status do chamado
            'status' => fake()->randomElement([
                'open',
                'in_progress',
                'closed',
            ]),

            // Define aleatoriamente a prioridade
            'priority' => fake()->randomElement([
                'low',
                'medium',
                'high',
            ]),
        ];
    }
}