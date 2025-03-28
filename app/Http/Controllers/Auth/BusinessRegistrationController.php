<?php

namespace App\Http\Controllers\Auth;

use App\Models\Plan;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\BusinessRegistrationRequest;

class BusinessRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.business-register');
    }

    public function register(BusinessRegistrationRequest $request)
    {
        // dd($request->all());
        DB::transaction(function () use ($request) {
            // Create business
            $business = Business::create([
                'name' => $request->business_name,
                'address' => $request->address,
                'phone' => $request->business_phone,
                'email' => $request->business_email,
            ]);

            // Create initial business admin user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'business_admin',
                'business_id' => $business->id,
            ]);

            // Create trial subscription
            $business->subscriptions()->create([
                'plan_id' => Plan::where('name', 'Free')->first()->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays(14),
            ]);
        });

        // Log the user in
        return redirect()->route('dashboard');
    }
}