<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Cria uma restrição chamada admin, que funciona mesmo sem um usuario logado
        Gate::define('admin', function (?User $user) {
            // Pega o usuario pela sessão
            $usuario = session('user');
            // Pega o nivel de acesso do usuario
            $nivel = $usuario['nivel_acesso'] ?? '';
            // Retorna se é um admin ou não
            return strtolower(trim($nivel)) === 'admin';
        });
    }
}