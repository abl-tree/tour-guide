<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    protected $fillable = [
        'user_id', 'language'
    ];

    public function getLanguageAttribute($value) {
        return ucwords(strtolower($value));
    }
}
