<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use App\Models\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:business_admin,admin');
    }

    /**
     * Show the business settings form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function settings(Request $request)
    {
        $user = $request->user();
        $business = $user->business;
        $timezones = $this->getTimezoneList();
        $businessHours = $business->business_hours ?? $this->getDefaultBusinessHours();

        return view('business.settings', [
            'business' => $business,
            'timezones' => $timezones,
            'businessHours' => $businessHours,
            'locations' => $business->locations,
        ]);
    }

    /**
     * Update the business settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $user = $request->user();
        $business = $user->business;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('businesses')->ignore($business->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'timezone' => 'required|string|max:50',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'business_hours' => 'nullable|array',
            'notification_settings' => 'nullable|array',
            'queue_settings' => 'nullable|array',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($business->logo_path) {
                Storage::delete($business->logo_path);
            }

            // Store new logo
            $path = $request->file('logo')->store('business-logos', 'public');
            $validated['logo_path'] = $path;
        }

        // Generate or update slug if name changed
        if ($business->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $baseSlug = $validated['slug'];
            $count = 1;

            while (Business::where('slug', $validated['slug'])->where('id', '!=', $business->id)->exists()) {
                $validated['slug'] = $baseSlug . '-' . $count++;
            }
        }

        // Update business hours
        if (isset($validated['business_hours'])) {
            $business->business_hours = $validated['business_hours'];
        }

        // Update notification settings
        if (isset($validated['notification_settings'])) {
            $business->notification_settings = $validated['notification_settings'];
        }

        // Update queue settings
        if (isset($validated['queue_settings'])) {
            $business->queue_settings = $validated['queue_settings'];
        }

        // Remove these from validated array as they're already handled
        unset($validated['business_hours'], $validated['notification_settings'], $validated['queue_settings']);

        // Update business
        $business->update($validated);

        return redirect()->route('business.settings')
            ->with('success', 'Business settings updated successfully.');
    }

    /**
     * Show the staff management page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function staffManagement(Request $request)
    {
        $user = $request->user();
        $business = $user->business;

        // Check subscription limits for staff members
        $subscription = $business->activeSubscription();
        $maxStaff = null;
        $canAddMore = true;

        if ($subscription) {
            $maxStaff = $subscription->getQuota('max_staff');
            $currentStaffCount = $business->users()->count();

            if ($maxStaff !== null) { // null means unlimited
                $canAddMore = $currentStaffCount < $maxStaff;
            }
        }

        return view('business.staff', [
            'business' => $business,
            'maxStaff' => $maxStaff,
            'canAddMore' => $canAddMore,
            // The actual staff list is loaded by the Livewire component
        ]);
    }

    /**
     * Get a list of all available timezones.
     *
     * @return array
     */
    private function getTimezoneList()
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $timezoneList = [];

        foreach ($timezones as $timezone) {
            $timezoneList[$timezone] = $timezone;
        }

        return $timezoneList;
    }

    /**
     * Get default business hours.
     *
     * @return array
     */
    private function getDefaultBusinessHours()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            if ($day === 'saturday' || $day === 'sunday') {
                $hours[$day] = [
                    'open' => false,
                    'start' => '09:00',
                    'end' => '17:00'
                ];
            } else {
                $hours[$day] = [
                    'open' => true,
                    'start' => '09:00',
                    'end' => '17:00'
                ];
            }
        }

        return $hours;
    }
}