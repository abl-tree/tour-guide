<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\TourDeparture;

class TourManifest extends Mailable
{
    use Queueable, SerializesModels;

    public $departure;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TourDeparture $departure)
    {
        $this->departure = $departure;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.tours.manifest');
    }
}
