@extends('layouts.app')

@section('subtitle')
Econimics/Statistics
@endsection

@section('content')
<div class="container">

    @if(Auth::user()->access_levels()->whereHas('info', function($q) {
    $q->where('code', 'admin');
    })->first())
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Economics and Statistics</li>
        </ol>
    </nav>

    <statistics-component is_admin v-bind:payment_types="{{$payments}}" v-bind:date="{{json_encode($current_month)}}" v-bind:guides="{{$guides}}" v-bind:tours="{{$tours}}"></statistics-component>
    @else
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Economics</li>
        </ol>
    </nav>

    <guide-statistics-component v-bind:guide="{{$guides}}"></guide-statistics-component>
    @endif
</div>
@endsection
