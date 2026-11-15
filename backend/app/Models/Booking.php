<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'equipment_id',
        'size',
        'start_date',
        'end_date',
    ];

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
            'startDate' => $this->start_date?->toDateString(),
            'endDate' => $this->end_date?->toDateString(),
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
