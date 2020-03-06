<style>
    div.content p {
        text-indent: 3em;
    }

    table.inner-body {
        width: 60%;
    }
</style>
@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url'), 
        'style' => 'display: inline-flex; align-items: center;'])
            <img src="{{ asset('images/logo.png') }}" alt="logo" style="max-height: 50px;">
            <span>Once in Rome Authentic Experiences</span>
        @endcomponent
    @endslot

    <div>

        @component('emails.tours.departures.list', ['departures' => $departures, 'coordinator' => true])
        @endcomponent
        
    </div>

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent