<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourManifest extends Model
{
    protected $fillable = [
        'tour_title_id',
        'content'
    ];

    public function tour() {
        return $this->hasOne('App\Models\TourTitle', 'id', 'tour_title_id');
    }
}
