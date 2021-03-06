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
            <li class="breadcrumb-item active" aria-current="page">Update</li>
        </ol>
    </nav>

    <tours-list-component v-bind:tour_types="{{$types}}" v-bind:tour="{{$tour}}"></tours-list-component>
</div>
@endsection
