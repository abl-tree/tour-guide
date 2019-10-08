<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use App\Models\TourDeparture;

class SummaryExport implements FromView
{
    private $departures;

    public function __contruct(TourDeparture $departures) {
        $this->departures = $departures->toArray();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view() : View
    {
        return view('exports.summaries', [
            'departures' => $this->departures
        ]);
    }
}
