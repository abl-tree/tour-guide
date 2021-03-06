@extends('layouts.app')

@section('subtitle')
Subscription
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Subscription Approved Email
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        {{$markdown->render('emails.subscriber.approved')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
