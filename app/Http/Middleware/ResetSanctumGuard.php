<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResetSanctumGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (! is_string($authorization) || ! str_starts_with(strtolower($authorization), 'bearer ')) {
            return $next($request);
        }

        $token = trim(substr($authorization, 7));
        $guard = Auth::guard('sanctum');
        $usuario = $guard->user();

        if ($usuario && ! $usuario->tokens()->where('token', hash('sha256', $token))->exists()) {
            $guard->forgetUser();
        }

        return $next($request);
    }
}
