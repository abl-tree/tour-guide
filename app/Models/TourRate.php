<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourRate extends Model
{
    protected $fillable = [
        'tour_history_id',
        'payment_type_id',
        'amount'
    ];

    protected $appends = [
        'type'
    ];

    public function getTypeAttribute() {
        return $this->payment_type->code;
    }

    public function payment_type() {
        return $this->hasOne('App\Models\PaymentType', 'id', 'payment_type_id');
    }
}
