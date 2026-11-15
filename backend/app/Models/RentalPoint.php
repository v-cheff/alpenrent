<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalPoint extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'opening_hours',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function toApiArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'openingHours' => $this->opening_hours,
        ];

        if (array_key_exists('equipment_count', $this->attributes)) {
            $data['equipmentCount'] = (int) $this->equipment_count;
        }

        return $data;
    }
}
