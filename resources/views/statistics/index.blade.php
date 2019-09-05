@extends('layouts.app')

@section('content')
<div class="container">

    @if(Auth::user()->access_levels()->whereHas('info', function($q) {
    $q->where('code', 'admin');
    })->first())
    <statistics-component is_admin v-bind:date="{{json_encode($current_month)}}" v-bind:tours="{{$tours}}"></statistics-component>
    @else
    <guide-statistics-component></guide-statistics-component>
    @endif
</div>
@endsection
