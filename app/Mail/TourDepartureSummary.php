<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryExport;
use App\Models\TourDeparture;
use Carbon\Carbon;
use App\User;

class TourDepartureSummary extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $excelAttachment;

    public $month, $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->month = Carbon::parse($data['start'])->format('F');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $admins = User::whereHas('access_levels', function($q) {
                    $q->whereHas('info', function($q) {
                        $q->where('code', 'admin');
                    });
                })->get()->toArray();

        $admins = array_column($admins, 'email');

        $departures = $this->getSummary($this->data['start'], $this->data['end'], $this->data['guide'])->get();

        $filename = Carbon::parse($this->data['start'])->format('F').' Tours Updates.xlsx';

        $summaryExcel = Excel::download(new SummaryExport($departures), $filename)->getFile();

        return $this->subject('Tour Guide'. ((!$this->data['guide']) ? ' Missed' : '' ) .' Assignment Updates')
                ->to($admins)
                ->attach($summaryExcel, ['as' => $this->month.' Tour Updates.xlsx'])
                ->markdown('emails.tours.departures.summary');
    }

    public function getSummary($start, $end, $guide = false) {
        $departures = TourDeparture::with('tour.info', 'schedule')->whereDate('date', '>=', $start)
                    ->whereDate('date', '<=', $end);

        if($guide) {
            $departures = $departures->whereNotNull('schedule_id');
        } else {
            $departures = $departures->whereNull('schedule_id');
        }

        return $departures;
    }
}
