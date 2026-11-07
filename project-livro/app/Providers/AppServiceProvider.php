<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\CauserResolver;
use Spatie\Activitylog\Models\Activity;

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

        app(CauserResolver::class)->resolveUsing(function ($causer = null) {
            if ($causer instanceof Model) {
                return $causer;
            }

            if ($causer !== null) {
                return User::find($causer);
            }

            $request = app()->bound('request') ? request() : null;
            $id = $request?->hasSession() ? $request->session()->get('user.id') : null;

            return $id ? User::find($id) : null;
        });

        Activity::creating(function (Activity $activity) {
            $request = app()->bound('request') ? request() : null;
            $requestId = $request?->attributes->get('activity_request_id');
            if ($requestId) {
                $activity->properties = $activity->properties->put('request_id', $requestId);
            }
        });

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
