@extends('layouts.app')

@section('subtitle')
Tour Profile
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('tours.index')}}">Tour Listing</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            @if($isAdmin)
            <tours-profile-component is-admin v-bind:tour="{{$tour}}"></tours-profile-component>
            @else
            <tours-profile-component v-bind:tour="{{$tour}}"></tours-profile-component>
            @endif
        </div>
    </div>
</div>
@endsection
