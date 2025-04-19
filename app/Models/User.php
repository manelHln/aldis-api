<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Contracts\MustVerifyPhoneNumber;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyPhoneNumber
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles;

    public $incrementing = false;
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullname',
        'email',
        'password',
        'profile_picture_url',
        'phone',
        'phone_verified_at',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'role_id'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function userLocations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function wishLists()
    {
        return $this->hasMany(FavoriteProduct::class);
    }

    // public function isAdministrator(): bool
    // {
    //     return $this->role->name === 'admin';
    // }

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
        ];
    }
    /**
     * Determine if the user has verified their phone number.
     *
     * @return bool
     */
    public function hasVerifiedPhoneNumber(): bool
    {
        // return !empty($this->phone_verified_at);
        // TODO: Implement a way to verify the phone number
        return true; // Returning true for now until client accepts twilio or any third party integration
    }
    /**
     * Mark the authenticated user's phone number as verified.
     *
     * @return bool
     */
    public function markPhoneNumberAsVerified(): bool
    {
        return $this->fill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
    /**
     * Send the phone number verification notification.
     *
     * @return void
     */
    public function sendPhoneNumberVerificationNotification(): void
    {
        // $this->notify();
    }
    /**
     * Get the phone number that should be used for verification.
     *
     * @return string
     */
    public function getPhoneNumberForVerification()
    {
        return $this->phone;
    }
}
