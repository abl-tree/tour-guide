<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'receipt_id', 'amount', 'category', 'receipt_url'
    ];

    public function getCategoryAttribute($value) {
        return $value ? 'Incossi' : 'Anticipi';
    }

    public function receipt() {
        $this->belongsTo('App\Models\Receipt');
    }
}
