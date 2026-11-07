<?php

namespace App\Http\Middleware;

use App\Support\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // O identificador é criado no servidor.
        $request->attributes->set('activity_request_id', (string) Str::uuid());
        $request->attributes->set('activity_started_at', microtime(true));
        $response = $next($request);
        $response->headers->set('X-Request-ID', $request->attributes->get('activity_request_id'));

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if (!$request->attributes->has('activity_started_at')) {
            return;
        }

        $status = $response->getStatusCode();
        ActivityLog::write('http.completed', [
            'method' => $request->method(),
            // Nome/padrão da rota, sem URL completa, parâmetros ou busca.
            'route' => $request->route()?->getName() ?? $request->route()?->uri() ?? 'unmatched',
            'status' => $status,
            'duration_ms' => round((microtime(true) - $request->attributes->get('activity_started_at')) * 1000, 2),
        ], $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info'));
    }
}
