<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Keeps customer ("C") and driver ("D") accounts out of back-office areas such as /admin/logistics.
 * Permission checks still apply on top of this.
 */
class InternalStaff
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && in_array($user->user_type, ['C', 'D'], true)) {
            abort(403, 'This area is for staff only.');
        }

        return $next($request);
    }
}
