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
        <p>Date: {{ $departure->date }}</p>
        <p>Timing: {{ $departure->date }}</p>
        <p>Tour Type: {{ $departure->tour->title }}</p>
        
        <h1 class="text-center">Tour Manifest</h1>

        <p class="text-center">Dear Tour Guide, please read the following instructions</p>

        <div class="content">
            {!! $departure->tour->manifest->content !!}
        </div>
        
    </div>

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent