<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! in_array(app()->environment(), ['local', 'testing'], true)) {
            throw new RuntimeException('DatabaseSeeder só pode ser executado em ambiente local ou de teste.');
        }

        // =========================================================
        // 1. CRIAR USUÁRIO ADMINISTRADOR
        // =========================================================

        // Cria um administrador com dados previsíveis para desenvolvimento
        // updateOrCreate evita duplicar o administrador se rodarmos o Seeder novamente
        User::updateOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        $demoUser = User::where('email', 'keebler.marjolaine@example.net')->first()
            ?? User::where('email', 'user@sistema.com')->first()
            ?? new User();

        $demoUser->fill([
            'name' => 'Usuário de demonstração',
            'email' => 'keebler.marjolaine@example.net',
            'password' => Hash::make('password'),
            'role' => 'user',
        ])->save();

        // =========================================================
        // 2. CRIAR CATEGORIAS
        // =========================================================

        // Categorias fixas utilizadas na classificação dos chamados
        $categories = [
            'Suporte Técnico',
            'Financeiro',
            'Dúvidas',
            'Infraestrutura',
            'Acesso e Login',
        ];

        // Percorre a lista e cria as categorias
        foreach ($categories as $category) {

            // firstOrCreate evita duplicar categorias existentes
            Category::firstOrCreate([
                'name' => $category,
            ]);
        }

    }
}