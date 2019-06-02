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
        'date', 'full_name', 'is_locked'
    ];

    /*
    * Full name of the tour guide
    */
    public function getFullNameAttribute() {
        $first_name = $this->users->first() && $this->users->first()->info()->first() ? $this->users->first()->info()->first()->first_name : null;
        $middle_name = $this->users->first() && $this->users->first()->info()->first() ? $this->users->first()->info()->first()->middle_name : null;
        $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        $last_name = $this->users->first() && $this->users->first()->info()->first() ? $this->users->first()->info()->first()->last_name : null;

        return  ($first_name && $last_name) ? $first_name.' '.$middle_initial.$last_name : null;
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

    public function users() {
        return $this->hasMany('App\User', 'id', 'user_id');
    }
}
