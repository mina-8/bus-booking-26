<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleWork extends Model
{
    protected $fillable = ['date_from', 'date_to'];
}
