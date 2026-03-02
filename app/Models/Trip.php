<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trip extends Model
{
    protected $fillable = [
        'city_one_id',
        'city_two_id',
        'price',
        'round_trip_price',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'round_trip_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_one_id');
    }
    
    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_two_id');
    }

    public function scheduleTrips(): HasMany
    {
        return $this->hasMany(ScheduleTrip::class);
    }

    public function buses(): BelongsToMany
    {
        return $this->belongsToMany(Bus::class, 'trip_bus');
    }
}
