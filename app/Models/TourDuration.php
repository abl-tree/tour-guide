<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDuration extends Model
{
    protected $fillable = [
        'tour_history_id',
        'duration_day',
        'duration_time'
    ];
}
