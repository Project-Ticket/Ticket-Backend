<?php

namespace App\Http\Middleware;

use App\Facades\MessageResponseJson;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah user sudah login
        if (!Auth::check()) {
            return redirect('login')->with('error', 'Anda harus login terlebih dahulu');
        }

        if (!$request->user()->hasRole('Organizer')) {
            return MessageResponseJson::unauthorized('Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
