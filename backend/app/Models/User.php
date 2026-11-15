<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Books equipment from the catalog. */
    public const ROLE_RENTER = 'renter';

    /** Runs rental points and lists equipment. */
    public const ROLE_OWNER = 'owner';

    public const ROLES = [self::ROLE_RENTER, self::ROLE_OWNER];

    protected $attributes = [
        'role' => self::ROLE_RENTER,
    ];

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

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

    public function tokens(): HasMany
    {
        return $this->hasMany(PersonalAccessToken::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function rentalPoints(): HasMany
    {
        return $this->hasMany(RentalPoint::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
