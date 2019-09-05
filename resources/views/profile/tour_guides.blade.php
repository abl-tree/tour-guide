@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Tour Guides') }}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <tour-guide-component v-bind:payments="{{$payments}}"></tour-guide-component>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
