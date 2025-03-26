<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'subscription_id',
        'gateway',
        'gateway_invoice_id',
        'number',
        'total',
        'currency',
        'status',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'total' => 'float',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * The business this invoice belongs to
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The subscription this invoice is for
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(): bool
    {
        $this->status = 'paid';
        $this->paid_at = now();

        return $this->save();
    }
}