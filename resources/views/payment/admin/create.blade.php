@extends('layouts.app')

@section('content')
<div class="container">
    <admin-payment-component v-bind:tour_titles="{{$titles}}" v-bind:guide="{{$guide}}"></admin-payment-component>
</div>
@endsection
