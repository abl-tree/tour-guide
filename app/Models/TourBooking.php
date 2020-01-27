<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TourBooking extends Model
{
    protected $fillable = [
        'tour_departure_id',
        'name',
        'party_size',
        'source'
    ];
}
