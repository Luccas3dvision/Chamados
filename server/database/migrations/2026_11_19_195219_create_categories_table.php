<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {

            // Identificador único da categoria
            $table->id();

            // Nome da categoria, por exemplo: Suporte, Financeiro, Dúvidas
            $table->string('name');

            // Datas de criação e atualização
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Exclui a tabela ao desfazer a migration
        Schema::dropIfExists('categories');
    }
};