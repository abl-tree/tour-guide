<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourInfoHistory extends Model
{
    protected $fillable = [
        'tour_id'
    ];

    public function tour() {
        return $this->hasOne('App\Models\TourTitle', 'id', 'tour_id');
    }

    public function tour_rates() {
        return $this->hasMany('App\Models\TourRate', 'tour_history_id', 'id');
    }

    public function participant_rates() {
        return $this->hasMany('App\Models\TourParticipantRate', 'tour_history_id', 'id');
    }

    public function duration() {
        return $this->hasOne('App\Models\TourDuration', 'tour_history_id', 'id');
    }
}
