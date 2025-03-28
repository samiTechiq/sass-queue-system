<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Business extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'timezone',
        'logo_path',
        'website',
        'business_hours',
        'notification_settings',
        'queue_settings',
        'is_active',
        'is_verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'business_hours' => 'array',
        'notification_settings' => 'array',
        'queue_settings' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Create a slug from the name if not set
        static::creating(function ($business) {
            if (!$business->slug) {
                $business->slug = Str::slug($business->name);

                // Ensure slug is unique
                $count = 1;
                $originalSlug = $business->slug;
                while (static::where('slug', $business->slug)->exists()) {
                    $business->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    /**
     * Get the users for the business.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the locations for the business.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class);
    }

    /**
     * Get the queues for the business.
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Get the customers for the business.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get all queue entries for the business.
     */
    public function queueEntries(): HasManyThrough
    {
        return $this->hasManyThrough(QueueEntry::class, Queue::class);
    }

    /**
     * Get the subscriptions for the business.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the payment methods for the business.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the invoices for the business.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the active subscription for this business
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if business has access to a feature
     */
    public function hasFeature(string $featureCode): bool
    {
        return FeatureService::hasFeature($this, $featureCode);
    }

    /**
     * Get remaining quota for a feature
     */
    public function getRemainingQuota(string $featureCode): ?int
    {
        return FeatureService::getRemainingQuota($this, $featureCode);
    }

    /**
     * Check if business can use a feature (has quota)
     */
    public function canUseFeature(string $featureCode, int $amount = 1): bool
    {
        return FeatureService::canUseFeature($this, $featureCode, $amount);
    }

    /**
     * Use a feature (increment usage counter)
     */
    public function useFeature(string $featureCode, int $amount = 1): bool
    {
        return FeatureService::useFeature($this, $featureCode, $amount);
    }

    /**
     * Get a setting value
     */
    public function getSettings(string $key, $default = null)
    {
        // Check queue settings
        if (isset($this->queue_settings[$key])) {
            return $this->queue_settings[$key];
        }

        // Check notification settings
        if (isset($this->notification_settings[$key])) {
            return $this->notification_settings[$key];
        }

        return $default;
    }

    /**
     * Update a setting value
     */
    public function updateSettings(string $key, $value, string $type = 'queue'): void
    {
        if ($type === 'queue') {
            $settings = $this->queue_settings ?? [];
            $settings[$key] = $value;
            $this->queue_settings = $settings;
        } elseif ($type === 'notification') {
            $settings = $this->notification_settings ?? [];
            $settings[$key] = $value;
            $this->notification_settings = $settings;
        }

        $this->save();
    }

    /**
     * Get the public display URL
     */
    public function getDisplayUrl(): string
    {
        return route('display.queue-board', $this->slug);
    }

    /**
     * Get the kiosk URL
     */
    public function getKioskUrl(): string
    {
        return route('display.kiosk', $this->slug);
    }


    /*
     * Get list of all features and their status for this business
     */
    public function getFeatures(): array
    {
        return FeatureService::getBusinessFeatures($this);
    }
}