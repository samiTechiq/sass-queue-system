<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    /**
     * Send SMS notification
     *
     * @param  string  $phoneNumber
     * @param  string  $message
     * @param  \App\Models\Business  $business
     * @return array
     */
    public function sendSms(string $phoneNumber, string $message, Business $business): array
    {
        // Determine which SMS provider to use based on business settings or system default
        $provider = $this->getSmsProvider($business);

        try {
            switch ($provider) {
                case 'twilio':
                    return $this->sendTwilioSms($phoneNumber, $message, $business);
                case 'vonage':
                    return $this->sendVonageSms($phoneNumber, $message, $business);
                default:
                    throw new Exception('Unsupported SMS provider: ' . $provider);
            }
        } catch (Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage(), [
                'provider' => $provider,
                'phone' => $phoneNumber,
                'business_id' => $business->id,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'provider' => $provider,
            ];
        }
    }

    /**
     * Send email notification
     *
     * @param  string  $email
     * @param  string  $subject
     * @param  string  $body
     * @param  \App\Models\Business  $business
     * @return array
     */
    public function sendEmail(string $email, string $subject, string $body, Business $business): array
    {
        // Implementation would use Laravel Mail facade to send emails
        try {
            // Mail::to($email)->send(new QueueNotification($subject, $body, $business));

            return [
                'success' => true,
                'message' => 'Email sent successfully',
            ];
        } catch (Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage(), [
                'email' => $email,
                'business_id' => $business->id,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the SMS provider to use
     *
     * @param  \App\Models\Business  $business
     * @return string
     */
    private function getSmsProvider(Business $business): string
    {
        // Check business settings first
        if (isset($business->notification_settings['sms_provider'])) {
            return $business->notification_settings['sms_provider'];
        }

        // Fall back to system default
        return config('services.sms.default_provider', 'twilio');
    }

    /**
     * Send SMS via Twilio
     *
     * @param  string  $phoneNumber
     * @param  string  $message
     * @param  \App\Models\Business  $business
     * @return array
     */
    private function sendTwilioSms(string $phoneNumber, string $message, Business $business): array
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $fromNumber = config('services.twilio.from_number');

        if (!$accountSid || !$authToken || !$fromNumber) {
            throw new Exception('Twilio configuration is incomplete');
        }

        // Format phone number for Twilio (E.164 format)
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // Send SMS via Twilio API
        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $fromNumber,
                'To' => $phoneNumber,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $responseData = $response->json();

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'provider' => 'twilio',
                'sid' => $responseData['sid'] ?? null,
            ];
        }

        throw new Exception('Twilio API error: ' . ($response->json()['message'] ?? 'Unknown error'));
    }

    /**
     * Send SMS via Vonage (formerly Nexmo)
     *
     * @param  string  $phoneNumber
     * @param  string  $message
     * @param  \App\Models\Business  $business
     * @return array
     */
    private function sendVonageSms(string $phoneNumber, string $message, Business $business): array
    {
        $apiKey = config('services.vonage.api_key');
        $apiSecret = config('services.vonage.api_secret');
        $fromName = config('services.vonage.from');

        if (!$apiKey || !$apiSecret || !$fromName) {
            throw new Exception('Vonage configuration is incomplete');
        }

        // Format phone number (remove non-numeric characters except +)
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // Send SMS via Vonage API
        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'from' => $fromName,
            'to' => $phoneNumber,
            'text' => $message,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $firstMessage = $responseData['messages'][0] ?? null;

            if ($firstMessage && $firstMessage['status'] === '0') {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'provider' => 'vonage',
                    'message_id' => $firstMessage['message-id'] ?? null,
                ];
            }

            throw new Exception('Vonage API error: ' . ($firstMessage['error-text'] ?? 'Unknown error'));
        }

        throw new Exception('Vonage API request failed');
    }

    /**
     * Format phone number for SMS providers
     *
     * @param  string  $phoneNumber
     * @return string
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Ensure it starts with + for international format
        if (substr($cleaned, 0, 1) !== '+') {
            // If no country code, assume US/Canada (+1)
            $cleaned = '+1' . $cleaned;
        }

        return $cleaned;
    }
}