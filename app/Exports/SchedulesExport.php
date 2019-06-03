<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromArray;

class SchedulesExport implements FromArray
{
    protected $start;
    
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $schedules = Schedule::where('available_at', '>=', $this->start)->where('available_at', '<=', $this->end)->orderBy('available_at', 'asc')->get();
        $data = array();

        foreach ($schedules as $key => $value) {
            $temp['full_name'] = $value['full_name'];
            $temp['available_at'] = $value['available_at'];
            $temp['shift'] = $value['shift'];

            array_push($data, $temp);
        }

        return $data;
    }
}
