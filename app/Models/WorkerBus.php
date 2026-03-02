<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerBus extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'type',
    ];

    public function buses()
    {
        return $this->belongsToMany(Bus::class, 'bus_worker_bus');
    }
}
