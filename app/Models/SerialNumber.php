<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    protected $fillable = [
        'tour_departure_id',
        'serial_number',
        'cost'
    ];

    public function getCostAttribute($value) {
        $number = number_format($value, 2, '.', '');

        return $number;
    }

    public function departure() {
        return $this->hasOne('App\Models\TourDeparture', 'id', 'tour_departure_id');
    }
}
