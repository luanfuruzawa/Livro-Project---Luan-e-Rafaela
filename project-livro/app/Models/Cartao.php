<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cartao extends Model
{
    use HasFactory;

    protected $table = 'cartoes';

    protected $fillable = [
        'user_id',
        'titular',
        'bandeira',
        'ultimos_quatro',
        'validade_mes',
        'validade_ano',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
