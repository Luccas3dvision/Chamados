<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    // Mantém a senha em hash para não recalcular em cada usuário
    protected static ?string $password;

    public function definition(): array
    {
        return [
            // Gera um nome fictício
            'name' => fake()->name(),

            // Gera um e-mail único
            'email' => fake()->unique()->safeEmail(),

            // Gera uma senha criptografada
            'password' => static::$password ??= Hash::make('password'),

            // Gera um token aleatório
            // erro no seed 'remember_token' => Str::random(10),

            // Define o perfil padrão como cliente
            'role' => 'user',
        ];
    }

    // Estado para criar um usuário administrador
    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
        ]);
    }
}