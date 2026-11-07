<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pedido extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoFactory> */
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pedidos')
            ->logOnly(['user_id', 'cartao_id', 'valor_total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $fillable = [
        'user_id',
        'cartao_id',
        'valor_total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartao()
    {
        return $this->belongsTo(Cartao::class);
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
