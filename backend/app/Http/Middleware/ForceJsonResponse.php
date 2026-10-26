<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuerza que todas las peticiones a la API sean tratadas como JSON,
 * independientemente del header "Accept" enviado por el cliente. Esto
 * asegura respuestas de error (404, 419, 500, etc.) consistentes en
 * formato JSON en lugar de vistas HTML de error.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
