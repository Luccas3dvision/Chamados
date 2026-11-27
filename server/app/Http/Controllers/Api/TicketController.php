<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // Lista chamados com autor e categoria e aplica filtros vindos da query string
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::with(['user:id,name,email,role', 'category:id,name'])->latest();

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        // Filtra por status apenas se um valor válido for informado
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Filtra por prioridade para facilitar triagem no painel
        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        // Filtra por categoria quando o usuário seleciona um tipo de atendimento
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return response()->json($query->get());
    }

    // Valida e cria um novo chamado garantindo integridade com usuário, categoria e enums
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['prohibited'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['prohibited'],
            'priority' => ['sometimes', 'in:low,medium,high'],
        ]);

        $ticket = $request->user()->tickets()->create($validated)->refresh();
        $ticket->load(['user:id,name,email,role', 'category:id,name']);

        return response()->json($ticket, 201);
    }

    // Retorna um chamado específico com seus relacionamentos
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->findVisibleTicket($request, $ticket);
        $ticket->load(['user:id,name,email,role', 'category:id,name']);

        return response()->json($ticket);
    }

    // Atualiza dados, status ou prioridade de um chamado existente
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->findVisibleTicket($request, $ticket);

        if ($request->user()->role !== 'admin' && $request->exists('status')) {
            return response()->json([
                'message' => 'Somente administradores podem alterar o status do chamado.',
            ], 403);
        }

        $rules = [
            'user_id' => ['prohibited'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'in:low,medium,high'],
        ];

        if ($request->user()->role === 'admin') {
            $rules['status'] = ['sometimes', 'in:open,in_progress,closed'];
        }

        $validated = $request->validate($rules);
        $ticket->update($validated);
        $ticket->load(['user:id,name,email,role', 'category:id,name']);

        return response()->json($ticket);
    }

    // Remove um chamado do banco com acesso de administrador, garantindo que apenas usuários com papel 'admin' possam realizar a exclusão
    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        // Garante que apenas usuários com papel 'admin' possam excluir chamados
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado: apenas administradores podem excluir chamados.',
            ], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Chamado removido com sucesso.',
        ]);
    }

    private function findVisibleTicket(Request $request, Ticket $ticket): Ticket
    {
        if ($request->user()->role === 'admin') {
            return $ticket;
        }

        return $request->user()->tickets()->whereKey($ticket->getKey())->firstOrFail();
    }
}