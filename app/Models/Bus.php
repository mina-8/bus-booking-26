<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bus extends Model
{
    protected $fillable = ['name', 'plate_number', 'seats', 'is_active'];

    protected $casts = [
        'seats' => 'integer',
        'is_active' => 'boolean',
    ];

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_bus');
    }

    public function workerBuses(): BelongsToMany
    {
        return $this->belongsToMany(WorkerBus::class, 'bus_worker_bus');
    }
}
