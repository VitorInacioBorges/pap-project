<?php

namespace App\Http\Middleware;

use App\Models\Paciente;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigeTrocaSenhaPrimeiroAcesso
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (($usuario instanceof Paciente || $usuario instanceof User) && $usuario->first_login) {
            return response()->json([
                'message' => 'Altere sua senha antes de continuar.',
                'first_login' => true,
            ], 403);
        }

        return $next($request);
    }
}
