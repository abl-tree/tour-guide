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

    protected $departures, $guide;

    public function __construct($departures, $guide) {
        $this->departures = $departures;
        $this->guide = $guide;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view() : View
    {
        return view('exports.summaries', [
            'departures' => $this->departures,
            'guide' => $this->guide
        ]);
    }
}
