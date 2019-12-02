@extends('layouts.app')

@section('subtitle')
Tour Guides Listing
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tour Guides Listing</li>
        </ol>
    </nav>

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
