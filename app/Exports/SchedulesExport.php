<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use App\Models\Schedule;
use App\User;

class SchedulesExport implements FromArray
{
    protected $start;
    
    protected $end;

    protected $user;

    public function __construct($start, $end, User $user = null)
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
        $schedules = $this->user ? $this->user->schedules()->where('available_at', '>=', $this->start)->where('available_at', '<=', $this->end)->orderBy('available_at', 'asc')->get() : Schedule::with('departure.tour')->where('available_at', '>=', $this->start)->where('available_at', '<=', $this->end)->orderBy('available_at', 'asc')->get();
        $data = array();

        foreach ($schedules as $key => $value) {
            $temp['full_name'] = $value['full_name'];
            $temp['available_at'] = $value['available_at'];
            $temp['shift'] = $value['shift'];
            $temp['departure'] = $value['departure'] && $value['departure']->tour ? $value['departure']->tour->title : '';
            $temp['flag'] = $value['departure'] ? 'Confirmed' : '';

            array_push($data, $temp);
        }

        return $data;
    }
}
