@extends('layouts.app')

@section('content')
<div class="container">
    @if($isAdmin)
    <payment-component v-bind:tour_titles="{{$titles}}" is-admin></payment-component>
    @else
    <payment-component v-bind:tour_titles="{{$titles}}"></payment-component>
    @endif
</div>
@endsection
