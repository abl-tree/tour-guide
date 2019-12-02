@extends('layouts.app')

@section('subtitle')
Tours Listing
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tour Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-12">
            @if($isAdmin)
            <tours-display-component v-bind:types="{{$types}}" is_admin></tours-display-component>
            @else
            <tours-display-component v-bind:types="{{$types}}"></tours-display-component>
            @endif
        </div>
    </div>
</div>
@endsection
