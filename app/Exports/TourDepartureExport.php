<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TourDepartureExport implements FromArray, ShouldAutoSize
{
    protected $tour_departure;

    public function __construct($tourDeparture = null)
    {
        $this->tour_departure = $tourDeparture;
    }

    /**
    * @return \Maatwebsite\Excel\Concerns\FromArray
    */
    public function array():array
    {
        $data = [];

        foreach ($this->tour_departure as $key => $value) {
            $temp['date'] = $value->date;
            $temp['tour'] = $value->tour->title;
            $temp['serial'] = ($departure->complete_voucher !== 0 ? 'Completed' : ($value->serial_numbers_count ? 'Incomplete' : 'Empty'));
            $temp['guide'] = $value->schedule ? $value->schedule->user->full_name : 'Not Assigned';
            array_push($data, $temp);
        }

        return $data;
    }
}
