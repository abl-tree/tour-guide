<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentType;
use Auth;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'available_at', 'flag', 'shift', 'tour_title_id'
    ];

    protected $appends = [
        'date', 'full_name', 'is_locked', 'payment_type_id', 'payment_type_code', 'rate'
    ];

    /*
    * Full name of the tour guide
    */
    public function getFullNameAttribute() {
        // $first_name = $this->user->first() && $this->user->first()->info()->first() ? $this->user->first()->info()->first()->first_name : null;
        // $middle_name = $this->user->first() && $this->user->first()->info()->first() ? $this->user->first()->info()->first()->middle_name : null;
        // $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        // $last_name = $this->user->first() && $this->user->first()->info()->first() ? $this->user->first()->info()->first()->last_name : null;

        return  $this->user->full_name;
    }

    /*
    * Date of the event
    */
    public function getDateAttribute() {
        return $this->available_at;
    }

    /*
    * Locked event
    */
    public function getIsLockedAttribute() {
        return $this->flag === 1 ? true : false;
    }

    public function getPaymentTypeCodeAttribute() {
        $payment_type = PaymentType::first();

        return $this->departure && $this->departure->tour_rate_code ? $this->departure->tour_rate_code : $payment_type->code;
    }

    public function getPaymentTypeIdAttribute() {
        $payment_type = PaymentType::first();

        return $this->departure && $this->departure->tour_rate_id ? $this->departure->tour_rate_id : $payment_type->id;
    }

    public function getRateAttribute() {
        // $default_rate = $this->departure->tour()->histories()->first()->tour_rates()->where('payment_type_id', $this->payment_type_id);
        if(!$this->departure) {
            return 0;
        }

        if($this->departure->custom_rate) return $this->departure->custom_rate;

        $default_rate = $this->departure->tour()->first() && $this->departure->tour()->first()->histories()->first() && $this->departure->tour()->first()->histories()->first()->tour_rates()->where('payment_type_id', $this->payment_type_id)->first() ? $this->departure->tour()->first()->histories()->first()->tour_rates()->where('payment_type_id', $this->payment_type_id)->first()->amount : 0;

        return $this->departure && $this->departure->rate ? $this->departure->rate->amount : $default_rate;
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function payments() {
        return $this->hasMany('App\Models\Payment', 'schedule_id', 'id');
    }

    public function departure() {
        return $this->hasOne('App\Models\TourDeparture', 'schedule_id', 'id');
    }
}
