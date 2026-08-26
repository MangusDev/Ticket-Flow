<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->check()) {
            return redirect()->route('login');
        }
        if(auth()->user()->role?->name !== 'admin') {
            abort(403, 'Acesso negado. Você não tem permissão para acessar esta página.');
        }
        return $next($request);
    }
}
