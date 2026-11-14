<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidaCargos
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$cargos): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! in_array($usuario->cargo, $cargos, true)) {
            return response()->json(['error: voce não tem permissão para realizar essa ação'], 403);
        }

        return $next($request);
    }
}
