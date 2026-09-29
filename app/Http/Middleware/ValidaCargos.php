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
        if(!in_array($request->user()->cargo, $cargos)){
            return response()->json(['error: voce não tem permição para realizar essa ação'], 403);
        }
        return $next($request);
    }
}
