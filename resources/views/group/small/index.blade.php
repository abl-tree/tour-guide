@extends('layouts.app')

@section('subtitle')
Calendar
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Calendar</li>
        </ol>
    </nav>

    <small-group-component></small-group-component>
</div>
@endsection
