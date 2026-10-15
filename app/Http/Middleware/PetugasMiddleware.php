<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PetugasMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(session("loggedIn") && session("role") !== "petugas") {
            return redirect()->back()->with("error", "anda tidak memiliki akses ke halaman ini");
        }

        return $next($request);
    }
}
