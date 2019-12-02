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
            <li class="breadcrumb-item active" aria-current="page">Show</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">Articles</div>
        <div class="card-body">
            <h5 class="card-title text-center">{{$article ? $article->title : null}}</h5>
            <h6 class="card-subtitle mb-2 text-muted text-center">{{$article ? $article->subtitle : null}}</h6>
            <div class="row">
                <div class="col-md-10">
                    {!! $article && $article->content ? $article->content : null !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
