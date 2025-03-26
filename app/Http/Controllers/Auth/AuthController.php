<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Check if the user exists and is active
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated. Please contact support.'],
            ]);
        }

        // Check for too many login attempts
        $this->checkLoginAttempts($request);

        // Attempt to authenticate
        if (Auth::attempt($this->credentials($request), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Handle post-login logic
            return $this->authenticated($request, Auth::user());
        }

        // Track failed login attempt
        $this->incrementLoginAttempts($request);

        // Failed login response
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Rate limiting for login attempts
     */
    protected function checkLoginAttempts(Request $request)
    {
        $key = 'login.' . md5($request->ip());
        $maxAttempts = 5;
        $decayMinutes = 1;

        if ($request->session()->has($key)) {
            $attempts = $request->session()->get($key) + 1;
            $request->session()->put($key, $attempts);

            if ($attempts >= $maxAttempts) {
                $retryAfter = $decayMinutes * 60;
                $request->session()->put($key . ':locked', now()->addSeconds($retryAfter));

                throw ValidationException::withMessages([
                    'email' => [trans('auth.throttle', ['seconds' => $retryAfter])],
                ])->status(429);
            }
        } else {
            $request->session()->put($key, 1);
        }

        // Check if currently locked out
        if ($request->session()->has($key . ':locked')) {
            $lockExpires = $request->session()->get($key . ':locked');

            if (now()->lt($lockExpires)) {
                $remainingSeconds = now()->diffInSeconds($lockExpires);

                throw ValidationException::withMessages([
                    'email' => [trans('auth.throttle', ['seconds' => $remainingSeconds])],
                ])->status(429);
            }

            // Lock expired, remove it
            $request->session()->forget($key . ':locked');
        }
    }

    /**
     * Increment the login attempts
     */
    protected function incrementLoginAttempts(Request $request)
    {
        $key = 'login.' . md5($request->ip());

        if ($request->session()->has($key)) {
            $request->session()->put($key, $request->session()->get($key) + 1);
        } else {
            $request->session()->put($key, 1);
        }
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            'email' => $request->email,
            'password' => $request->password,
            'active' => true
        ];
    }

    /**
     * Handle post-authentication logic
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if the user's business has an active subscription
        if ($user->business && !$user->isAdmin()) {
            $hasActiveSubscription = $user->business->activeSubscription() !== null;

            if (!$hasActiveSubscription) {
                // Redirect to subscription page if no active subscription
                return redirect()->route('subscription.required')
                    ->with('warning', 'Your business needs an active subscription to continue.');
            }
        }

        // Record login timestamp
        $user->last_login_at = now();
        $user->save();

        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        }

        if ($user->isBusinessAdmin()) {
            return redirect('/business/dashboard');
        }

        return redirect('/queues');  // Default for staff
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }
}