@extends('layouts.app')

@section('subtitle')
Articles & Roles
@endsection

@section('content')
<div class="container">
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
