<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResidentProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->user()->role->name === 'resident'
            &&
            ! auth()->user()->resident
        ) {
            return redirect()
                ->route('resident.profile.create');
        }

        return $next($request);
    }
}
