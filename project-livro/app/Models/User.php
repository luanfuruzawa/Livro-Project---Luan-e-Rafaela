<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('usuarios')
            ->logOnly(['nivel_acesso'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public function carrinho()
    {
        return $this->belongsToMany(Livro::class, 'carrinhos')
            ->withPivot('quantidade');
    }

    public function cartoes()
    {
        return $this->hasMany(Cartao::class);
    }

    protected $fillable = [
        'username',
        'email',
        'nivel_acesso',
        'password',
    ];
}
