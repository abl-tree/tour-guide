<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'receipt_id', 'incassi', 'anticipi', 'receipt_url'
    ];

    protected $appends = [
        'balance'
    ];

    public function getBalanceAttribute() {
        $balance = $this->incassi - $this->anticipi;

        return number_format($balance, 2, '.', '');
    }

    public function getAnticipiAttribute($value) {
        return number_format($value, 2, '.', '');
    }

    public function getIncassiAttribute($value) {
        return number_format($value, 2, '.', '');
    }

    public function receipt() {
        $this->belongsTo('App\Models\Receipt');
    }
}
