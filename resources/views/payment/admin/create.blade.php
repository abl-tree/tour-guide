@extends('layouts.app')

@section('content')
<div class="container">
    @if(isset($dates) && $dates)
    <admin-payment-component v-bind:tour_titles="{{$titles}}" v-bind:guide="{{$guide}}" v-bind:stats-dates="{{json_encode($dates)}}"></admin-payment-component>
    @else
    <admin-payment-component v-bind:tour_titles="{{$titles}}" v-bind:guide="{{$guide}}"></admin-payment-component>
    @endif
</div>
@endsection
