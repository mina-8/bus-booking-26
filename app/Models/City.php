<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['name'];

    public function tripsAsOrigin(): HasMany
    {
        return $this->hasMany(Trip::class, 'city_one_id');
    }

    public function tripsAsDestination(): HasMany
    {
        return $this->hasMany(Trip::class, 'city_two_id');
    }

    /**
     * جميع الرحلات المرتبطة بهذه المدينة (سواء كنقطة انطلاق أو وصول)
     */
    public function allTrips()
    {
        return Trip::where('city_one_id', $this->id)
            ->orWhere('city_two_id', $this->id)
            ->get();
    }
}
