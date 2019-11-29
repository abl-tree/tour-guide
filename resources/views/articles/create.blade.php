@extends('layouts.app')

@section('subtitle')
Articles & Roles
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Articles</div>
        <div class="card-body">
            <article-create-component></article-create-component>
        </div>
    </div>
</div>
@endsection
