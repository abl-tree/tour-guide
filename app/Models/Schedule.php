<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'available_at', 'flag'
    ];

    protected $appends = [
        'full_name'
    ];

    public function getFullNameAttribute() {
        $first_name = $this->users->first()->info()->first()->first_name;
        $middle_name = $this->users->first()->info()->first()->middle_name;
        $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        $last_name = $this->users->first()->info()->first()->last_name;

        return $first_name.' '.$middle_initial.$last_name;
    }

    public function users() {
        return $this->hasMany('App\User', 'id', 'user_id');
    }
}
