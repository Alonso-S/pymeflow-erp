<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Valida que el usuario autenticado tenga uno de los roles autorizados.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado.',
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta cuenta de usuario ha sido desactivada.',
                ], 403);
            }
            return redirect()->route('login')->withErrors(['email' => 'Su cuenta se encuentra inactiva. Contacte al administrador.']);
        }

        if (!in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado: No cuenta con permisos suficientes para esta operación.',
                ], 403);
            }
            abort(403, 'Acceso denegado: Esta acción requiere permisos de ' . implode(' o ', $roles) . '.');
        }

        return $next($request);
    }
}
