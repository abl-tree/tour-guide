<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TourCoordinatorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $departures;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($departures)
    {
        $this->departures = $departures;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tour Manifest')
                    ->markdown('emails.tours.coordinator');
    }
}
