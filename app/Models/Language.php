<?php

namespace App\Models;

use ErnySans\Laraworld\Models\Languages;

class Language extends Languages
{
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}