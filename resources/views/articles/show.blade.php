@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Articles</div>
        <div class="card-body">
            <h5 class="card-title text-center">{{$article->title}}</h5>
            <h6 class="card-subtitle mb-2 text-muted text-center">{{$article->subtitle}}</h6>
            <div class="row">
                <div class="col-md-10">
                    {!! $article->content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
