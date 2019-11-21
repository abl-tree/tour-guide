<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class NotifyGuideDeparture extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;

        $this->date = Carbon::parse($data->schedule->date);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tour Departure')
                    ->markdown('emails.tours.departures.guide');
    }
}
