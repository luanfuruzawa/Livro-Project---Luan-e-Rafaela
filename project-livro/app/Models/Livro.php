<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livro extends Model
{
    /** @use HasFactory<\Database\Factories\LivroFactory> */
    use HasFactory;
    public function emCarrinhosDe()
    {
        return $this->belongsToMany(User::class, 'carrinhos')
            ->withPivot('quantidade');
    }
    protected $fillable = [
        'titulo',
        'genero',
        'preco',
        'estoque',
        'caminho_imagem',
    ];
}
