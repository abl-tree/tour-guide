<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourInfo extends Model
{
    protected $fillable = [
        'color', 
        'tour_code', 
        'image_link', 
        'description',
        'cash', 
        'invoice', 
        'payoneer', 
        'paypal', 
        'adult_price',
        'children_price',
        'duration_day',
        'duration_time',
        'type_id',
        'tour_code'
    ];

    protected $appends = [
        'duration'
    ];

    public function getDurationAttribute() {
        return $this->duration_day.'d'.' '.$this->duration_time;
    }
 
    public function type() {
        return $this->hasOne('App\Models\TourType', 'id', 'type_id');
    }
}
