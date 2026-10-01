<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== $role) {
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Akses khusus admin.');
            } else {
                return redirect()->route('intern.dashboard')->with('error', 'Akses dibatasi hanya untuk peran terkait.');
            }
        }

        return $next($request);
    }
}
