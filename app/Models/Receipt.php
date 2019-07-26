<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
        'event_date',
        'paid_at'
    ];

    protected $appends = [
        'categorized_payments', 
        'remarks'
    ];

    public function getCategorizedPaymentsAttribute() {
        $data = array(
            'anticipi' => [], 
            'incossi' => [],
            'balance' => $this->balance() ? $this->balance() : 0
        );
        
        foreach ($this->payments as $key => $value) {
            if($value->category === 'Anticipi') {
                array_push($data['anticipi'], $value);
            } else if($value->category === 'Incossi') {
                array_push($data['incossi'], $value);
            }
        }

        return $data;
    }

    public function getRemarksAttribute() {
        if($this->paid_at) return 'Paid';

        return $this->balance() > 0 ? 'To Balance' : null;
    }

    public function balance() {
        $anticipi_total = 0;
        $incossi_total = 0;
        $anticipi = $this->payments->where('category', 'Anticipi');
        $incossi = $this->payments->where('category', 'Incossi');

        foreach ($anticipi as $key => $value) {
            $anticipi_total += $value->amount;
        }

        foreach ($incossi as $key => $value) {
            $incossi_total += $value->amount;
        }

        if($this->paid_at) $incossi_total = $anticipi_total;

        $number = $anticipi_total - $incossi_total;

        return number_format((float)$number, 2, '.', '');
    }

    public function payments() {
        return $this->hasMany('App\Models\Payment', 'receipt_id', 'id');
    }
}
