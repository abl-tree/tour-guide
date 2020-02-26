<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\PaymentType;

class TourDeparture extends Model
{
    protected $fillable = [
        'tour_id', 'schedule_id', 'tour_rate_id', 'custom_rate', 'info_id', 'departure', 'date', 'voucher_complete', 'child_participants', 'adult_participants', 'earning'
    ];

    protected $appends = [
        'tour_rate_code', 'remarks', 'has_fareharbor', 'has_airbnb', 'pax_total'
    ];

    public function getTotalParticipantsAttribute() {
        return $this->child_participants + $this->adult_participants;
    }

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

    public function getCustomRateAttribute($value) {
        return ($value) ? $value : $this->rate->amount;
    }

    public function getRemarksAttribute() {
        return ($this->complete_voucher !== 0 ? 'Completed' : (($this->serial_numbers) ? 'Incomplete' : 'Empty'));
    }

    public function getHasFareharborAttribute() {
        $total = 0;

        if($this->bookings && $bookings = $this->bookings->where('source', 'fareharbor')) {
            foreach ($bookings as $key => $value) {
                $total += $value->party_size;
            }

            return $total;
        }

        return $total;
    }

    public function getHasAirbnbAttribute() {
        $total = 0;

        if($this->bookings && $bookings = $this->bookings->where('source', 'airbnb')) {
            foreach ($bookings as $key => $value) {
                $total += $value->party_size;
            }

            return $total;
        }

        return $total;
    }

    public function getPaxTotalAttribute() {
        $bookings = $this->bookings;
        $pax = 0;

        foreach ($bookings as $key => $booking) {
            $pax += $booking->party_size;
        }

        return $pax;
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

    public function bookings() {
        return $this->hasMany('App\Models\TourBooking', 'tour_departure_id', 'id');
    }
}
