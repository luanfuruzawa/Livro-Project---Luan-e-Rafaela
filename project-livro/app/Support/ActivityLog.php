<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

final class ActivityLog
{
    public static function write(string $event, array $context = [], string $level = 'info'): void
    {
        // Aceita apenas os campos operacionais listados abaixo.
        $allowed = array_flip([
            'user_id', 'livro_id', 'pedido_id', 'quantidade', 'item_count',
            'valor_total', 'method', 'route', 'status', 'duration_ms',
            'exception_class', 'source_file', 'source_line',
        ]);
        $request = app()->bound('request') ? request() : null;
        $base = [
            'request_id' => $request?->attributes->get('activity_request_id'),
            'user_id' => $request?->hasSession() ? $request->session()->get('user.id') : null,
        ];

        try {
            Log::channel('activity')->log($level, $event, array_merge($base, array_intersect_key($context, $allowed)));
        } catch (Throwable $exception) {
            // Falhas de escrita não devem interromper a operação.
            error_log('Activity log unavailable: '.get_class($exception));
        }

        $accessEvents = [
            'auth.registered' => 'Cadastro realizado',
            'auth.logged_in' => 'Login realizado',
            'auth.login_failed' => 'Falha no login',
            'auth.logged_out' => 'Logout realizado',
        ];

        if (isset($accessEvents[$event])) {
            try {
                activity('acesso')
                    ->event($event)
                    ->withProperties(['request_id' => $base['request_id']])
                    ->log($accessEvents[$event]);
            } catch (Throwable $exception) {
                error_log('History log unavailable: '.get_class($exception));
            }
        }
    }
}
