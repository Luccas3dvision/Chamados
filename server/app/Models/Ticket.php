<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    // Relacionamento: cada chamado pertence a um usuário
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento: cada chamado pertence a uma categoria
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}