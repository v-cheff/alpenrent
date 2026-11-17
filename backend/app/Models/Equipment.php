<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'rental_point_id',
        'name',
        'category',
        'category_label',
        'badge',
        'badge_tone',
        'image',
        'specs',
        'daily_rate',
        'half_day_rate',
        'deposit',
        'sizes',
        'available_now',
        'available_dates',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'sizes' => 'array',
            'available_now' => 'boolean',
            'available_dates' => 'array',
            'daily_rate' => 'decimal:2',
            'half_day_rate' => 'decimal:2',
            'deposit' => 'decimal:2',
        ];
    }

    public function rentalPoint(): BelongsTo
    {
        return $this->belongsTo(RentalPoint::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Equipment listed at any rental point owned by the given user.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->whereHas('rentalPoint', fn (Builder $points) => $points->where('user_id', $user->id));
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->rentalPoint !== null && $this->rentalPoint->user_id === $user->id;
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'rentalPointId' => $this->rental_point_id,
            'rentalPoint' => $this->relationLoaded('rentalPoint') && $this->rentalPoint
                ? $this->rentalPoint->toApiArray()
                : null,
            'name' => $this->name,
            'category' => $this->category,
            'categoryLabel' => $this->category_label,
            'badge' => $this->badge,
            'badgeTone' => $this->badge_tone,
            'image' => $this->image,
            'specs' => $this->specs ?? [],
            'dailyRate' => (float) $this->daily_rate,
            'halfDayRate' => $this->half_day_rate === null ? null : (float) $this->half_day_rate,
            'deposit' => (float) $this->deposit,
            'sizes' => $this->sizes ?? [],
            'availableNow' => $this->available_now,
            'availableDates' => $this->available_dates ?? [],
        ];
    }
}
