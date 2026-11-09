<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cartao extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cartoes')
            ->logOnly(['user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
