<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentType;

class UserInfo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'birthdate', 'gender_id', 'note', 'contact_number', 'rating', 'payment_type_id'
    ];

    protected $appends = [
        'payment'
    ];

    public function getPaymentAttribute() {
        return $this->payment_type ? $this->payment_type : PaymentType::first();
    }

    public function payment_type() {
        return $this->hasOne('App\Models\PaymentType', 'id', 'payment_type_id');
    }
}
