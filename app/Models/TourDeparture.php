<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\PaymentType;

class TourDeparture extends Model
{
    protected $fillable = [
        'tour_id', 'schedule_id', 'tour_rate_id', 'info_id', 'departure', 'date', 'voucher_complete'
    ];

    protected $appends = [
        'tour_rate_code'
    ];

    public function getDepartureAttribute($value) {
        return $value ? Carbon::parse($value)->format('H:i') : '9:00';
    }

    public function getTourRateIdAttribute($value) {
        $payment = PaymentType::first();

        return $value ? $value : $payment->id;
    }

    public function getTourRateCodeAttribute() {
        // $payment = PaymentType::find($this->tour_rate_id);

        $rate = $this->rate ? $this->rate->type : null;

        return $rate;
        // return $payment->code;
    }

    public function tour() {
        return $this->hasOne('App\Models\TourTitle', 'id', 'tour_id');
    }

    public function schedule() {
        return $this->hasOne('App\Models\Schedule', 'id', 'schedule_id');
    }

    public function rate() {
        return $this->hasOne('App\Models\TourRate', 'id', 'tour_rate_id');
    }

    public function serial_numbers() {
        return $this->hasMany('App\Models\SerialNumber', 'tour_departure_id', 'id');
    }
}
