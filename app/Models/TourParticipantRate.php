<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourParticipantRate extends Model
{
    protected $fillable = [
        'participant_type_id',
        'tour_history_id',
        'amount'
    ];

    protected $appends = [
        'type'
    ];

    public function getTypeAttribute() {
        return $this->participant_type->code;
    }

    public function participant_type() {
        return $this->hasOne('App\Models\ParticipantType', 'id', 'participant_type_id');
    }
}
