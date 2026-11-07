<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoItemFactory> */
    use HasFactory;

    protected $table = 'pedido_itens'; 

    protected $fillable = [
        'pedido_id',
        'livro_id',
        'quantidade',
        'valor_total',
    ];
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
    public function livro()
    {
        return $this->belongsTo(Livro::class, 'livro_id');
    }
}