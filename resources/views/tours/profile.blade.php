@extends('layouts.app')

@section('subtitle')
Tour Profile
@endsection

@section('content')
<div class="container">
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
