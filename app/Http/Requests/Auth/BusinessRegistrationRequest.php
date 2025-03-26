<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class BusinessRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can register a new business
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Business Information
            'business_name' => ['required', 'string', 'max:100'],
            'business_email' => ['required', 'string', 'email', 'max:255', 'unique:businesses,email'],
            'business_phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'website' => ['nullable', 'url', 'max:255'],

            // Administrator Account
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Terms and Privacy Policy
            'terms' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'business_name' => 'business name',
            'business_email' => 'business email',
            'business_phone' => 'business phone',
            'name' => 'administrator name',
            'email' => 'administrator email',
            'terms' => 'terms and conditions',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business_email.unique' => 'This business email is already registered.',
            'email.unique' => 'This email address is already taken.',
            'terms.accepted' => 'You must accept the terms and conditions to register.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize phone numbers by removing spaces and special characters
        if ($this->has('business_phone')) {
            $this->merge([
                'business_phone' => preg_replace('/[^0-9+]/', '', $this->business_phone),
            ]);
        }

        // Convert business name to title case for consistency
        if ($this->has('business_name')) {
            $this->merge([
                'business_name' => ucwords(strtolower($this->business_name)),
            ]);
        }
    }
}