@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(isset($myProfile))
                <guide-profile-component my-profile v-bind:guide="{{$guide}}"></guide-profile-component>
            @else
                @if($isAdmin)
                <guide-profile-component is-admin v-bind:guide="{{$guide}}"></guide-profile-component>
                @else
                <guide-profile-component v-bind:guide="{{$guide}}"></guide-profile-component>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
