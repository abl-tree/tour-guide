<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDepartureCoordinator extends Model
{
    protected $fillable = [
        'tour_id', 'coordinator_id', 'date'
    ];

    public function coordinator() {
        return $this->hasOne('App\Models\TourCoordinator', 'id', 'coordinator_id');
    }
}
