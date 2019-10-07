<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use App\User;

class TourModification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $guide, $month, $schedules;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $guide, $month)
    {
        $this->guide = $guide;
        $this->schedules = $guide->schedules;
        $this->month = Carbon::parse($month)->format('F');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tour Assignment Updates')
                ->to($this->guide->email)
                ->markdown('emails.tours.modification');
    }
}
