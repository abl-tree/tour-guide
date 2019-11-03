@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Articles</div>
        <div class="card-body">
            <article-create-component v-bind:article="{{$article}}"></article-create-component>
        </div>
    </div>
</div>
@endsection
