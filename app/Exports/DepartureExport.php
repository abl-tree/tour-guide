<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use App\User;

class DepartureExport implements FromArray
{

    protected $users;

    public function __construct($users = null)
    {
        $this->users = $users;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function array():array
    {
        $data = [];

        foreach ($this->users as $key => $user) {
            foreach ($user->schedules as $key => $schedule) {
                $temp['full_name'] = $schedule['full_name'];
                $temp['type'] = $schedule['departure']->tour->info->type->name;
                $temp['tour'] = $schedule['departure']->tour->title;
                $temp['date'] = $schedule['departure']->date;

                array_push($data, $temp);
            }
        }

        return $data;
    }
}
