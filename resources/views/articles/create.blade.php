@extends('layouts.app')

@section('subtitle')
Articles & Roles
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('articles.index')}}">Articles & Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">Articles</div>
        <div class="card-body">
            <article-create-component></article-create-component>
        </div>
    </div>
</div>
@endsection
