<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class TourInfoExport implements FromView
{
    protected $departures;

    public function __construct($departures) {
        $this->departures = $departures;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view() : View
    {
        return view('exports.tours.info', [
            'departures' => $this->departures
        ]);
    }
}
