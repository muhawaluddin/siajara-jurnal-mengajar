<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $allowed = collect($roles)
            ->map(fn (string $role) => UserRole::from(trim($role)))
            ->contains(fn (UserRole $role) => $role === $user->role);

        abort_if(! $allowed, 403);

        return $next($request);
    }
}
