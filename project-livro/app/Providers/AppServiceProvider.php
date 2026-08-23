<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    // cria uma restrioção chamada admin, que funciona mesmo sem um usuario logado
    Gate::define('admin', function (?User $user) {
        //pega o usuario pela sessão
        $usuario = session('user');
        //pega o nivel de acesso do usuario
        $nivel = $usuario['nivel_acesso'] ?? '';
        //retorna se é um admin ou não
        return strtolower(trim($nivel)) === 'admin';
    });
}
}