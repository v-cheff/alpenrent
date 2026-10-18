<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'category',
        'category_label',
        'badge',
        'badge_tone',
        'image',
        'specs',
        'daily_rate',
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
            'deposit' => 'decimal:2',
        ];
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'categoryLabel' => $this->category_label,
            'badge' => $this->badge,
            'badgeTone' => $this->badge_tone,
            'image' => $this->image,
            'specs' => $this->specs ?? [],
            'dailyRate' => (float) $this->daily_rate,
            'deposit' => (float) $this->deposit,
            'sizes' => $this->sizes ?? [],
            'availableNow' => $this->available_now,
            'availableDates' => $this->available_dates ?? [],
        ];
    }
}
