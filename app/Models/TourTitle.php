<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourTitle extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title',
        'time'
    ];

    public function info() {
        return $this->hasOne('App\Models\TourInfo', 'tour_id');
    }

    public function availabilities() {
        return $this->hasMany('App\Models\Availability', 'tour_id');
    }

    public function departures() {
        return $this->hasMany('App\Models\TourDeparture', 'tour_id', 'id');
    }
}
