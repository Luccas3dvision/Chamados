<?php

namespace App\Models;

// Trait que permite criar usuários através de factories
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Classe base de Model do Eloquent
use Illuminate\Foundation\Auth\User as Authenticatable;

// Trait que permite enviar notificações
use Illuminate\Notifications\Notifiable;

// Permite definir relacionamentos entre Models
use Illuminate\Database\Eloquent\Relations\HasMany;

// Trait que permite autenticação via tokens (Sanctum)
use Laravel\Sanctum\HasApiTokens;

// Importa o modelo Ticket para definir o relacionamento
use App\Models\Ticket;

class User extends Authenticatable
{
      use HasApiTokens, HasFactory, Notifiable;

    // Campos que podem ser preenchidos em operações de mass assignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    // Campos que não devem ser retornados em respostas, como JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Define conversões automáticas de dados
    protected function casts(): array
    {
        return [
            // Garante que a senha seja armazenada com hash
            'password' => 'hashed',
        ];
    }

    // Relacionamento: um usuário pode possuir vários chamados
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

}