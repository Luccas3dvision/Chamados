<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    // Define quais campos podem ser preenchidos em massa
    protected $fillable = [
        'name',
    ];

    // Relacionamento: uma categoria pode possuir vários chamados
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}