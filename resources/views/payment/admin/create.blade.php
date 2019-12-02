@extends('layouts.app')

@section('subtitle')
Anticipi/Incassi
@endsection

@section('content')
<div class="container">
    @if(isset($dates) && $dates)
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('statistics.index')}}">Economics & Statistics</a></li>
            <li class="breadcrumb-item active" aria-current="page">Anticipi & Incassi</li>
        </ol>
    </nav>

    <admin-payment-component v-bind:tour_titles="{{$titles}}" v-bind:guide="{{$guide}}" v-bind:stats-dates="{{json_encode($dates)}}"></admin-payment-component>
    @else
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('tourguide.index')}}">Tour Guide Listing</a></li>
            <li class="breadcrumb-item active" aria-current="page">Anticipi & Incassi</li>
        </ol>
    </nav>

    <admin-payment-component v-bind:tour_titles="{{$titles}}" v-bind:guide="{{$guide}}"></admin-payment-component>
    @endif
</div>
@endsection
