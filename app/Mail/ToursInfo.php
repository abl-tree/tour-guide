<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TourInfoExport;
use Carbon\Carbon;
use App\User;

class ToursInfo extends Mailable
{
    use Queueable, SerializesModels;

    public $departures, $month;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($departures, $date)
    {
        $this->departures = $departures;

        $this->month = Carbon::parse($date)->format('F');
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

        $emails = array_column($admins, 'email');

        $filename = $this->month.' Tours with No or Incomplete Voucher.xlsx';

        $summaryExcel = Excel::download(new TourInfoExport($this->departures), $filename)->getFile();

        return $this->subject('Tours with No or Incomplete Voucher')
                    ->to($emails)
                    ->attach($summaryExcel, ['as' => $filename])
                    ->markdown('emails.tours.departures.info');
    }
}
