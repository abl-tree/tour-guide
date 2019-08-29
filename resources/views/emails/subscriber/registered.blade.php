@component('mail::message')
# Subscription

<p>Name: {{$user->full_name}}</p>
<p>Username: {{$user->username}}</p>
<p>Temporary Password: {{$user->readable_password}}</p>

Please change your password.

@component('mail::button', ['url' => config('app.url')])
Click here
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
