<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Livro extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('livros')
            ->logOnly(['titulo', 'genero', 'preco', 'estoque'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    use SoftDeletes;
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
