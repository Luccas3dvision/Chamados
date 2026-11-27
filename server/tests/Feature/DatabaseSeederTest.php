<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_creates_only_the_admin_and_user_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('tickets', 0);

        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();
        $demoUser = User::where('email', 'keebler.marjolaine@example.net')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('12345678', $admin->password));
        $this->assertSame('user', $demoUser->role);
        $this->assertTrue(Hash::check('password', $demoUser->password));
    }
}
