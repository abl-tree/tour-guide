<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessLevel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'access_level_id'
    ];

    public function info() {
        return $this->hasOne('App\Models\AccessLevel', 'id', 'access_level_id');
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
