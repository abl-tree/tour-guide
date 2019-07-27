<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Receipt extends Model
{
    protected $fillable = [
        'event_date',
        'paid_at'
    ];

    protected $appends = [
        'remarks',
        'balance'
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

    public function getRemarksAttribute() {
        if($this->paid_at) return 'Paid';

        return $this->balance() > 0 ? 'To Balance' : null;
    }

    public function balance() {
        $anticipi_total = $this->payment ? $this->payment->anticipi : 0;
        $incossi_total = $this->payment ? $this->payment->incassi : 0;

        if($this->paid_at) $incossi_total = $anticipi_total;

        $number = $incossi_total - $anticipi_total;

        return number_format((float)$number, 2, '.', '');
    }

    public function payment() {
        return $this->hasOne('App\Models\Payment', 'receipt_id', 'id');
    }
}
