@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Subscription Approved Email
                </div>
                <div class="card-body">
                    <form action="route('settings.edit')" method="post">
                        <input type="hidden" name="type" value="subscriber.approved">

                        <div class="form-group">
                            <label for="introduction">Introduction</label>
                            <input class="form-control" type="text" name="" id="introduction">
                        </div>
                        
                        <div class="form-group">
                            <label for="body">Body</label>
                            <textarea class="form-control" name="body-content" id="body" cols="30" rows="10"></textarea>
                        </div>

                        <input class="btn btn-primary" type="submit" value="Submit">
                        <a class="btn btn-warning" target="_blank" href="{{route('settings.show', 'subscriber.approved')}}">View Current Email</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
