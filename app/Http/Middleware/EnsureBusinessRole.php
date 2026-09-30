<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var string|null $role */
        $role = $request->attributes->get('activeBusinessRole');
        abort_unless(in_array($role, $roles, true), 403);

        return $next($request);
    }
}
