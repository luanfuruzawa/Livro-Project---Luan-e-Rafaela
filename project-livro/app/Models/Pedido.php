<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'valor_total',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}