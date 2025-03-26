<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FeatureService;

class EnsureFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $featureCode
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $featureCode)
    {
        $user = $request->user();

        if (!$user || !$user->business) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Subscription required'], 403);
            }

            return redirect()->route('subscription.required');
        }

        if (!FeatureService::hasFeature($user->business, $featureCode)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Feature not available on your current plan'], 403);
            }

            return redirect()->route('subscription.upgrade')
                ->with('error', "Your current plan doesn't include this feature. Please upgrade to access it.");
        }

        return $next($request);
    }
}