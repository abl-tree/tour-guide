@component('mail::message')
# Tour Departure Notification

You have a scheduled tour on {{$date->format('l jS \\of F Y')}} in the {{$data->schedule->shift}}.

<p>PS: Please do not reply to this automatic email, in case of problem send a message to onceinrometours@gmail.com</p>

Kind Regard,<br>
Once in Rome Team
@endcomponent
