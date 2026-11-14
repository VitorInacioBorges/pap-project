<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidaSeUsuarioAtivo
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($usuario instanceof User && ! $usuario->ativo) {
            return response()->json(['error' => 'usuario inativo'], 403);
        }

        return $next($request);
    }
}
