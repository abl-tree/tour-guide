<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourCoordinator extends Model
{
    protected $appends = [
        'full_name'
    ];

    public function setFirstNameAttribute($value) {
        $this->attributes['first_name'] = ucwords($value);
    }
    
    public function setLastNameAttribute($value) {
        $this->attributes['last_name'] = ucwords($value);
    }

    public function getFullNameAttribute() {
        return $this->first_name.' '.$this->last_name;
    }
}
