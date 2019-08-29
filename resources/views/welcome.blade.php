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

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/tours">Tours</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/smallgroup">Small Group Calendar</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6 col-sm-12" style="float: none; margin: 0 auto;">
                    <a class="btn btn-primary" style="width: 100%" href="/privategroup">Private Group Calendar</a>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection