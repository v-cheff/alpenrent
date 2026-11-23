<?php

namespace App\Models;

use Carbon\CarbonPeriod;
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

    /**
     * Whether the owner offers this item for every date of the period (both ends inclusive).
     * "Available now" means it can be rented any day; otherwise only on the listed dates.
     */
    public function isInStockFor(string $startDate, string $endDate): bool
    {
        if ($this->available_now) {
            return true;
        }

        $availableDates = $this->available_dates ?? [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if (! in_array($date->toDateString(), $availableDates, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Seeded images are stored as paths ("/images/equipment/…") so they work on any host;
     * uploads and external links are stored as full URLs.
     */
    public function imageUrl(): ?string
    {
        if ($this->image && str_starts_with($this->image, '/')) {
            return url($this->image);
        }

        return $this->image;
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
            'image' => $this->imageUrl(),
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
