@extends('layouts.app')

@section('subtitle')
Coordinator Listing
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tour Coordinator Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <coordinators-display-component></coordinators-display-component>
        </div>
    </div>
</div>
@endsection
