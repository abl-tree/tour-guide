@extends('layouts.app')

@section('content')
<div class="container">
    <tours-list-component v-bind:tour_types="{{$types}}" v-bind:tour="{{$tour}}"></tours-list-component>
</div>
@endsection