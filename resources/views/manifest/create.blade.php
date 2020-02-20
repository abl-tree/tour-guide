@extends('layouts.app')

@section('subtitle')
Tours Listing
@endsection

@section('content')
<div class="container">
    <booking-component v-bind:tour_titles="{{$titles}}" payment></booking-component>
</div>
@endsection
