<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Otp extends Model
{
    protected $table = 'otps';

    protected $fillable = [
        'email',
        'mobile',
        'otp',
        'expires_at',
        'is_used',
        'attempts'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    // Check if OTP is expired
    public function isExpired()
    {
        return Carbon::now('Asia/Kolkata')->gt($this->expires_at);
    }

    // Mark OTP as used
    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    // Increment attempts
    public function incrementAttempts()
    {
        $this->increment('attempts');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // Get active OTP (not used and not expired)
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
                     ->where('expires_at', '>', Carbon::now('Asia/Kolkata'));
    }

    // Find latest OTP by email or mobile
    public function scopeByIdentifier($query, $email = null, $mobile = null)
    {
        return $query->when($email, fn($q) => $q->where('email', $email))
                     ->when($mobile, fn($q) => $q->where('mobile', $mobile));
    }
}