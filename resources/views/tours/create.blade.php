@extends('layouts.app')

@section('subtitle')
Tours Listing
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('tours.index')}}">Tour Listing</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registration</li>
        </ol>
    </nav>

    <tours-list-component v-bind:tour_types="{{$types}}"></tours-list-component>
</div>
@endsection
