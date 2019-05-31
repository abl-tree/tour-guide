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
        'user_id', 'available_at', 'flag', 'shift'
    ];

    protected $appends = [
        'title', 'date', 'full_name', 'is_locked'
    ];

    /*
    * Full name of the tour guide
    */
    public function getFullNameAttribute() {
        $first_name = $this->users->first()->info()->first()->first_name;
        $middle_name = $this->users->first()->info()->first()->middle_name;
        $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        $last_name = $this->users->first()->info()->first()->last_name;

        return $first_name.' '.$middle_initial.$last_name;
    }

    /*
    * Date of the event
    */
    public function getDateAttribute() {
        return $this->available_at;
    }

    /*
    * Title of the event
    */
    public function getTitleAttribute() {
        return $this->where('available_at', $this->available_at)->count();
    }

    /*
    * Locked event
    */
    public function getIsLockedAttribute() {
        return $this->flag === 1 ? true : false;
    }

    public function users() {
        return $this->hasMany('App\User', 'id', 'user_id');
    }
}
