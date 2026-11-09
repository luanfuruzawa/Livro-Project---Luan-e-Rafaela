<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Carrinho extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('carrinho')
            ->logOnly(['user_id', 'livro_id', 'quantidade'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'carrinhos';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'livro_id',
        'quantidade',
    ];

    public function livro()
    {
        return $this->belongsTo(Livro::class, 'livro_id');
    }
}
