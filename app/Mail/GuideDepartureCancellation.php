<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class GuideDepartureCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule, $date, $departure;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($schedule, $departure)
    {
        $this->schedule = $schedule;

        $this->date = Carbon::parse($schedule->date);

        $this->departure = $departure;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->schedule->user->email)
                    ->subject('Tour Cancellation')
                    ->markdown('emails.tours.departures.cancel');
    }
}
