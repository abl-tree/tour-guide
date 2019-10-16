<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    protected $fillable = [
        'tour_departure_id',
        'serial_number'
    ];

    public function departure() {
        return $this->hasOne('App\Models\TourDeparture', 'id', 'tour_departure_id');
    }
}
