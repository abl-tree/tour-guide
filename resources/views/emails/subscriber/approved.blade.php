@component('mail::message')
# Dear {{$user->username}}

Thank you for your registration, now you are in the team!

@component('mail::button', ['url' => config('app.url')])
Click here
@endcomponent

Thanks,<br>
Once in Rome Authentic Experiences
@endcomponent
