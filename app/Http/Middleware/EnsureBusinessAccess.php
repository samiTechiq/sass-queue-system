<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// app/Http/Middleware/EnsureBusinessAccess.php
class EnsureBusinessAccess
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $businessId = $request->route('business');

        if ($businessId && $user->business_id != $businessId && !$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'You do not have access to this business.');
        }

        return $next($request);
    }
}