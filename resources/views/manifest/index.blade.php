@extends('layouts.app')

@section('subtitle')
Tours Listing
@endsection

@section('content')
<div class="container">
    <manifest-component v-bind:tour="{{$tour}}"></manifest-component>
</div>
@endsection
