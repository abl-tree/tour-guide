@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/home">Availabilities</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/payment/create">Anticipi & Incassi</a>
                </div>
            </div>
        </div>

        @if(Auth::user()->access_levels()->whereHas('info', function($q) {
        $q->where('code', 'admin');
        })->first())
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/tours">Tours Listing</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/tourcalendar">Calendars</a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/notification">Notifications</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/statistics">Economics & Statistics</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="{{ route('tourguide.index') }}">Tour Guides Listing</a>
                </div>
            </div>
        </div>
        @else
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/guide/statistics">Economics</a>
                </div>
            </div>
        </div>
        @endif
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/myprofile">My Profile</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/profile">My Account Settings</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">Logout</a>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection