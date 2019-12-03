@component('mail::message')
# Assignment Cancellation

Your scheduled tour on {{$date->format('l jS \\of F Y')}} in the {{$schedule->shift}} has been cancelled | Tour type: {{$departure->tour->title}}.

<p>PS: Please do not reply to this automatic email, in case of problem send a message to onceinrometours@gmail.com</p>

Kind Regard,<br>
Once in Rome Team
@endcomponent
