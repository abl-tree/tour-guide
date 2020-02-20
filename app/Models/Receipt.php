<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\PaymentType;

class Receipt extends Model
{
    protected $fillable = [
        'event_date',
        'paid_at',
        'title_id',
        'user_id',
        'payment_type_id'
    ];

    protected $appends = [
        'remarks',
        'balance',
        'delete_attempt',
        'payment_info',
        'payment_type_code',
        'total'
    ];

    public function getEventDateAttribute($value) {
        $parsedDate = Carbon::parse($value);

        $result = array(
            'date' => $value, 
            'month' => $parsedDate->isoFormat('MMMM'),
            'day' => $parsedDate->format('d'),
            'year' => $parsedDate->format('Y')
        );

        return $result;
    }

    public function getBalanceAttribute() {
        return $this->balance();
    }

    public function getDeleteAttemptAttribute() {
        return false;
    }

    public function getRemarksAttribute() {
        if($this->paid_at) return 'Paid';

        return $this->balance() > 0 ? 'To Balance' : null;
    }

    public function getPaymentTypeCodeAttribute() {
        return $this->payment_type ? $this->payment_type->code : 'cash';
    }

    public function balance() {
        $anticipi_total = $this->payment ? $this->payment->anticipi : 0;
        $incossi_total = $this->payment ? $this->payment->incassi : 0;

        $number = $incossi_total - $anticipi_total;

        return number_format((float)$number, 2, '.', '');
    }

    public function getTotalAttribute() {
        $anticipi_total = $this->payment ? $this->payment->anticipi : 0;
        $incossi_total = $this->payment ? $this->payment->incassi : 0;

        $number = $incossi_total + $anticipi_total;

        return number_format((float)$number, 2, '.', '');
    }

    public function getPaymentInfoAttribute() {
        $payment_type = PaymentType::first();

        return $this->payment_type ? $this->payment_type : $payment_type;
    }

    public function payment_type() {
        return $this->hasOne('App\Models\PaymentType', 'id', 'payment_type_id');
    }

    public function payment() {
        return $this->hasOne('App\Models\Payment', 'receipt_id', 'id');
    }

    public function title() {
        return $this->hasOne('App\Models\TourTitle', 'id', 'title_id');
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    
    public function scopeExclude($query, $value = array()) {
        return $query->select( array_diff( $this->appends,(array) $value) );
    }
}
