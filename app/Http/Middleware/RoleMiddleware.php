<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->role_id) {
            $roleModel = $user->role_id ? \App\Models\Role::find($user->role_id) : null;
            if ($roleModel && $roleModel->name === $role) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak.');
    }
}
