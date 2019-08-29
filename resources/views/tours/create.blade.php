@extends('layouts.app')

@section('content')
<div class="container">
    <tours-list-component v-bind:tour_types="{{$types}}"></tours-list-component>
</div>
@endsection
