<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'available_at', 'flag', 'shift', 'tour_title_id'
    ];

    protected $appends = [
        'date', 'full_name', 'is_locked'
    ];

    /*
    * Full name of the tour guide
    */
    public function getFullNameAttribute() {
        // $first_name = $this->user->first() && $this->user->first()->info()->first() ? $this->user->first()->info()->first()->first_name : null;
        // $middle_name = $this->user->first() && $this->user->first()->info()->first() ? $this->user->first()->info()->first()->middle_name : null;
        // $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        // $last_name = $this->user->first() && $this->user->first()->info()->first() ? $this->user->first()->info()->first()->last_name : null;

        return  $this->user->full_name;
    }

    /*
    * Date of the event
    */
    public function getDateAttribute() {
        return $this->available_at;
    }

    /*
    * Locked event
    */
    public function getIsLockedAttribute() {
        return $this->flag === 1 ? true : false;
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function payments() {
        return $this->hasMany('App\Models\Payment', 'schedule_id', 'id');
    }

    public function departure() {
        return $this->hasOne('App\Models\TourDeparture', 'schedule_id', 'id');
    }
}
