@component('mail::message')
# You have new subscriber.

<p>Name: {{$subscriber->full_name}}</p>
<p>Username: {{$subscriber->username}}</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
