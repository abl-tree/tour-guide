@extends('layouts.app')

@section('subtitle')
Profile
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(isset($myProfile))
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>

                <guide-profile-component my-profile v-bind:guide="{{$guide}}"></guide-profile-component>
            @else
                @if($isAdmin)
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{route('tourguide.index')}}">Tour Guides Listing</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>

                <guide-profile-component is-admin v-bind:guide="{{$guide}}"></guide-profile-component>
                @else
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>

                <guide-profile-component v-bind:guide="{{$guide}}"></guide-profile-component>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
