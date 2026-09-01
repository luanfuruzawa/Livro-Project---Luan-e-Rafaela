<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    public function carrinho()
    {
        return $this->belongsToMany(Livro::class, 'carrinhos')
            ->withPivot('quantidade');
    }
    protected $fillable = [
        'username',
        'email',
        'nivel_acesso',
        'password',
    ];
}