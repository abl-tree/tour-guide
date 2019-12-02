@extends('layouts.app')

@section('subtitle')
Anticipi/Incassi
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Anticipi & Incassi</li>
        </ol>
    </nav>

    @if($isAdmin)
    <payment-component v-bind:tour_titles="{{$titles}}" is-admin></payment-component>
    @else
    <payment-component v-bind:tour_titles="{{$titles}}"></payment-component>
    @endif
</div>
@endsection
