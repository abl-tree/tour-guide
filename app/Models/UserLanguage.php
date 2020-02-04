<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    protected $fillable = [
        'user_id', 'language_id'
    ];

    public function language() {
        return $this->hasOne('App\Models\Language', 'id', 'language_id');
    }
}
