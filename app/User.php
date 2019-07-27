<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'user_info_id', 'accepted_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'full_name',
        'balance',
        'to_balance'
    ];

    /*
    * Full name of the tour guide
    */
    public function getFullNameAttribute() {
        $first_name = $this->info()->first() ? $this->info()->first()->first_name : null;
        $middle_name = $this->info()->first() ? $this->info()->first()->middle_name : null;
        $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        $last_name = $this->info()->first() ? $this->info()->first()->last_name : null;

        return  ($first_name && $last_name) ? $last_name.', '.$first_name.' '.$middle_initial : null;
    }

    public function getToBalanceAttribute() {
        return $this->balance == 0 ? 0 : 1;
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = ucwords($value);
    }

    public function getNameAttribute($value) {
        return ucwords($value);
    }

    public function setPasswordAttribute($value) {
        $this->attributes['password'] = bcrypt($value);
    }

    public function getBalanceAttribute() {
        $balance = 0;

        foreach ($this->receipts as $key => $value) {
            $balance += $value->balance;
        }

        return number_format($balance, 2, '.', '');
    }

    public function info() {
        return $this->hasOne('App\Models\UserInfo', 'id', 'user_info_id');
    }

    public function access_levels() {
        return $this->hasMany('App\Models\UserAccessLevel', 'user_id');
    }

    public function schedules() {
        return $this->hasMany('App\Models\Schedule', 'user_id');
    }

    public function receipts() {
        return $this->hasMany('App\Models\Receipt', 'user_id', 'id');
    }
}
