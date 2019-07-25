<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'available_at', 'flag', 'shift', 'tour_title_id', 'paid_at'
    ];

    protected $appends = [
        'date', 'full_name', 'is_locked', 'categorized_payments', 'remarks'
    ];

    /*
    * Full name of the tour guide
    */
    public function getFullNameAttribute() {
        $first_name = $this->users->first() && $this->users->first()->info()->first() ? $this->users->first()->info()->first()->first_name : null;
        $middle_name = $this->users->first() && $this->users->first()->info()->first() ? $this->users->first()->info()->first()->middle_name : null;
        $middle_initial = $middle_name ? $middle_name[0].'. ':'';
        $last_name = $this->users->first() && $this->users->first()->info()->first() ? $this->users->first()->info()->first()->last_name : null;

        return  ($first_name && $last_name) ? $first_name.' '.$middle_initial.$last_name : null;
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

    public function users() {
        return $this->hasMany('App\User', 'id', 'user_id');
    }

    public function payments() {
        return $this->hasMany('App\Models\Payment', 'schedule_id', 'id');
    }
}
