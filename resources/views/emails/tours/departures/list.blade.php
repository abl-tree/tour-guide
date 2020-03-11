@if(isset($departures) && $departures && isset($coordinator))
    <style>
        hr.end-line {
            margin: 50px 0;
            padding: 0;
            border: none;
            border-top: medium double #333;
            color: #333;
            text-align: center;
        }
    </style>

    @php
        $departures_count = count($departures);
    @endphp
    @foreach($departures as $key => $departure )
    <p>Tour Guide: {{ $departure->schedule && $departure->schedule->user ? $departure->schedule->user->fullname : 'NULL' }} <span style="float: right; font-weight: bold;">Departure {{$key + 1}} of {{$departures_count}}</span></p>
    <p>Contact: {{ $departure->schedule && $departure->schedule->user && $departure->schedule->user->info ? $departure->schedule->user->info->contact_number : 'NULL' }}</p>
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

    <div>
        <h3>Vouchers: </h3>
        @foreach($departure->serial_numbers()->whereNotNUll('file_link')->get() as $key=>$voucher)
            <a href="{{$voucher->file_link}}">Link {{$key + 1}}</a> <br>
        @endforeach
    </div>

    <hr class="end-line">
    @endforeach
@elseif($departure)
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

    <div>
        <h3>Vouchers: </h3>
        @foreach($departure->serial_numbers()->whereNotNUll('file_link')->get() as $key=>$voucher)
            <a href="{{$voucher->file_link}}">Link {{$key + 1}}</a> <br>
        @endforeach
    </div>
@endif