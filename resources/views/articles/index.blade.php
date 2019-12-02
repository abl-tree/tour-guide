@extends('layouts.app')

@section('subtitle')
Articles & Roles
@endsection

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Articles & Roles</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">Articles</div>
        <div class="card-body">
            @if($isAdmin)
            <article-component is-admin></article-component>
            @else
            <article-component></article-component>
            @endif
        </div>
    </div>
</div>
@endsection
