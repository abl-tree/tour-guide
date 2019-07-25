<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'schedule_id', 'amount', 'category', 'receipt_url'
    ];

    public function getReceiptUrlAttribute($value) {
        return $value ? Storage::url($value) : null;
    }

    public function getCategoryAttribute($value) {
        return $value ? 'Incossi' : 'Anticipi';
    }
}
