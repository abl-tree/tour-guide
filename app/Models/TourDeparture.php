<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TourDeparture extends Model
{
    protected $fillable = [
        'tour_id', 'schedule_id', 'departure', 'date'
    ];

    public function getDepartureAttribute($value) {
        return $value ? Carbon::parse($value)->format('H:i') : '9:00';
    }

    public function tour() {
        return $this->hasOne('App\Models\TourTitle', 'id', 'tour_id');
    }

    public function schedule() {
        return $this->hasOne('App\Models\Schedule', 'id', 'schedule_id');
    }
}
