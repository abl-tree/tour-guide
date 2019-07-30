<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromArray;

class SchedulesExport implements FromArray
{
    protected $start;
    
    protected $end;

    protected $user;

    public function __construct($start, $end, $user = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->user = $user;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $schedules = $this->user ? Schedule::where('user_id', $this->user)->where('available_at', '>=', $this->start)->where('available_at', '<=', $this->end)->orderBy('available_at', 'asc')->get() : Schedule::where('available_at', '>=', $this->start)->where('available_at', '<=', $this->end)->orderBy('available_at', 'asc')->get();
        $data = array();

        foreach ($schedules as $key => $value) {
            $temp['full_name'] = $value['full_name'];
            $temp['available_at'] = $value['available_at'];
            $temp['shift'] = $value['shift'];
            $temp['flag'] = $value['flag'] ? 'Confirmed' : '';

            array_push($data, $temp);
        }

        return $data;
    }
}
