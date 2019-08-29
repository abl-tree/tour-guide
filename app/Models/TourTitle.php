<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourTitle extends Model
{
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
}
