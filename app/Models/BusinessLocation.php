<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'is_active',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}