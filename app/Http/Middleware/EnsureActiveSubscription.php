<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->business) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Business not found'], 404);
            }

            return redirect()->route('home');
        }

        $subscription = $user->business->activeSubscription();

        if (!$subscription) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Active subscription required'], 403);
            }

            return redirect()->route('subscription.required');
        }

        return $next($request);
    }
}
