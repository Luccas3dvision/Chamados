<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {

            // Identificador único do chamado
            $table->id();

            // Relaciona o chamado ao usuário que o abriu
            // BIGINT UNSIGNED compatível com users.id
            $table->foreignId('user_id');

            // Relaciona o chamado a uma categoria
            // BIGINT UNSIGNED compatível com categories.id
            $table->foreignId('category_id');

            // Título curto do chamado
            $table->string('title');

            // Descrição detalhada do problema ou solicitação
            $table->text('description');

            // Estado atual do chamado
            // open       = aberto
            // in_progress = em andamento
            // closed     = fechado
            $table->enum('status', ['open', 'in_progress', 'closed'])
                ->default('open');

            // Prioridade do chamado
            // low    = baixa
            // medium  = média
            // high   = alta
            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium');

            // Datas de criação e atualização
            $table->timestamps();

            // Relacionamento com usuários
            // Se o usuário for excluído, seus chamados também serão
            $table->foreign('user_id', 'fk_tickets_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Relacionamento com categorias
            // Se a categoria for excluída, seus chamados também serão
            $table->foreign('category_id', 'fk_tickets_category')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Exclui a tabela de chamados ao desfazer a migration
        Schema::dropIfExists('tickets');
    }
};