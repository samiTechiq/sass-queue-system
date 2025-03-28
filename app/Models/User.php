<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // User attributes
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'business_id',
        'phone',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationship with business
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Role-based authorization helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isBusinessAdmin()
    {
        return $this->role === 'business_admin';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }
}