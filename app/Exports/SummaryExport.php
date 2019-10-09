<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class SummaryExport implements FromView, ShouldQueue
{
    use Queueable, SerializesModels, Exportable;

    protected $departures;

    public function __construct($departures) {
        $this->departures = $departures;
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
