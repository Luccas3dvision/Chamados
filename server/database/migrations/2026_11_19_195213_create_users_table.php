<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            // Identificador único do usuário (BIGINT UNSIGNED AUTO_INCREMENT)
            $table->id();

            // Nome completo do usuário
            $table->string('name');

            // E-mail único para login
            $table->string('email')->unique();

            // Senha armazenada em hash, nunca em texto puro
            $table->string('password');

            // Define o nível de acesso do usuário
            // Por padrão, todo novo usuário será um cliente (user)
            $table->enum('role', ['admin', 'user'])->default('user');

            // Cria created_at e updated_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Remove a tabela caso a migration seja revertida
        Schema::dropIfExists('users');
    }
};