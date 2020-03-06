<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\SuspendedScope;

class TourTitle extends Model
{
    use SoftDeletes;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SuspendedScope);
    }
    
    protected $fillable = [
        'title',
        'time'
    ];

    protected $appends = [
        'other_info'
    ];
    
    public function getOtherInfoAttribute() {
        return $this->histories() ? $this->histories()->with('tour_rates', 'participant_rates', 'duration')->first() : null;
    }

    public function info() {
        return $this->hasOne('App\Models\TourInfo', 'tour_id');
    }

    public function availabilities() {
        return $this->hasMany('App\Models\Availability', 'tour_id');
    }

    public function departures() {
        return $this->hasMany('App\Models\TourDeparture', 'tour_id', 'id');
    }

    public function departures_incomplete() {
        return $this->hasMany('App\Models\TourDeparture', 'tour_id', 'id');
    }

    public function histories() {
        return $this->hasMany('App\Models\TourInfoHistory', 'tour_id', 'id')->latest();
    }

    public function receipts() {
        return $this->hasMany('App\Models\Receipt', 'title_id', 'id');
    }

    public function manifest() {
        return $this->hasOne('App\Models\TourManifest', 'tour_title_id', 'id');
    }

    public function coordinators() {
        return $this->hasMany('App\Models\TourDepartureCoordinator', 'tour_id', 'id');
    }
}
