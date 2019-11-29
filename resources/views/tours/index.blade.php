@extends('layouts.app')

@section('subtitle')
Tours Listing
@endsection

@section('content')
<div class="container">
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
