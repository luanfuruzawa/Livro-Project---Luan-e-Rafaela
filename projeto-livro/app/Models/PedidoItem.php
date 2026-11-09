<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PedidoItem extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoItemFactory> */
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('itens')
            ->logOnly(['pedido_id', 'livro_id', 'quantidade', 'valor_total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
