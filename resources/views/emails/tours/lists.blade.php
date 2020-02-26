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
        <p>Tour Guide: {{ $departure->schedule && $departure->schedule->user ? $departure->schedule->user->fullname : 'NULL' }}</p>
        <p>Date: {{ \Carbon\Carbon::parse($departure->date)->format('M jS Y') }}</p>
        <p>Timing: {{ $departure->departure }}</p>
        <p>Tour Type: {{ $departure->tour->title }}</p>
        <p>No. of Participants: {{ $departure->total_participants.' ('.$departure->adult_participants.' Adults | '.$departure->child_participants.' Children)' }}</p>
        
        <h1 class="text-center">Tour Listing</h1>

        <div class="content">
        @component('mail::table')
        | Contact       | Phone         | Ada  | Ch       | Due         | Affiliate  | Voucher       | Email         | Notes  |
        | ------------- |:-------------:| :--------:| :-------------: |:-------------:| :--------:| :-------------: |:-------------:| --------:|
        @foreach($departure->bookings as $booking)
        | {{ $booking->name }} |  | {{ $booking->party_size }} |  |  | {{ $booking->source }} |  |  |  |
        @endforeach
        @endcomponent
        </div>
        
    </div>

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent