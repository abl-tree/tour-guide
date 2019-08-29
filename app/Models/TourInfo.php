<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourInfo extends Model
{
    protected $fillable = [
        'color', 
        'tour_code', 
        'image_link', 
        'cash', 
        'invoice', 
        'payoneer', 
        'paypal', 
        'adult_price',
        'children_price',
        'duration_day',
        'duration_time',
        'type_id'
    ];
 
    public function type() {
        return $this->hasOne('App\Models\TourType', 'id', 'type_id');
    }
}
