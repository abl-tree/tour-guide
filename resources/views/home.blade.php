@extends('layouts.app')

@section('subtitle')
Availabilities
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Availabilities</li>
        </ol>
    </nav>

    <calendar-component></calendar-component>
</div>
@endsection
