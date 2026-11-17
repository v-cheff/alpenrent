<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    /** Every date from start_date to end_date, both inclusive. */
    public const FULL_DAY = 'full_day';

    /** A single date, from noon. */
    public const HALF_DAY = 'half_day';

    public const RENTAL_TYPES = [self::FULL_DAY, self::HALF_DAY];

    protected $fillable = [
        'user_id',
        'equipment_id',
        'size',
        'rental_type',
        'start_date',
        'end_date',
    ];

    protected $attributes = [
        'rental_type' => self::FULL_DAY,
    ];

    /**
     * Rental days, counted inclusively the way ski rentals bill them (1–4 Dec = 4 days).
     */
    public function days(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Rental price at the equipment's current rates (null when equipment isn't loaded).
     */
    public function total(): ?float
    {
        if (! $this->relationLoaded('equipment') || ! $this->equipment) {
            return null;
        }

        if ($this->rental_type === self::HALF_DAY) {
            return (float) $this->equipment->half_day_rate;
        }

        return round((float) $this->equipment->daily_rate * $this->days(), 2);
    }

    /**
     * Store dates as plain Y-m-d: the default `date` cast writes "Y-m-d H:i:s",
     * which breaks string date comparisons on SQLite.
     */
    protected function startDate(): Attribute
    {
        return $this->dateOnlyAttribute();
    }

    protected function endDate(): Attribute
    {
        return $this->dateOnlyAttribute();
    }

    private function dateOnlyAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function scopeOverlapping(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'equipmentId' => $this->equipment_id,
            'size' => $this->size,
            'rentalType' => $this->rental_type,
            'startDate' => $this->start_date?->toDateString(),
            'endDate' => $this->end_date?->toDateString(),
            'days' => $this->days(),
            'total' => $this->total(),
            'equipment' => $this->relationLoaded('equipment') && $this->equipment
                ? $this->equipment->toApiArray()
                : null,
            'renter' => $this->relationLoaded('user') && $this->user
                ? ['id' => $this->user->id, 'name' => $this->user->name, 'email' => $this->user->email]
                : null,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
};
