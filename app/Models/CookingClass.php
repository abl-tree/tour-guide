<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CookingClass extends Model
{
    protected $fillable = [
        'date',
        'no_of_chef',
        'cost_per_chef',
        'no_of_assistant',
        'cost_per_assistant',
        'fuel_cost',
        'ingredient_cost',
        'other_cost',
        'no_of_participant',
        'cost_per_participant'
    ];

    public function setDateAttribute($value) {
        $date = Carbon::parse($value)->format('Y-m-d');

        $this->attributes['date'] = $date;
    }
}
