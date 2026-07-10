<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            abort(403);
        }

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! in_array(auth()->user()->role->name, $roles)) {
            return redirect()->back()->with([
                'message' => 'You do not have access for this page.',
                'status' => 'error',
            ]);
        }

        return $next($request);
    }
}
