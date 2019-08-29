@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if($isAdmin)
            <tours-display-component is_admin></tours-display-component>
            @else
            <tours-display-component></tours-display-component>
            @endif
        </div>
    </div>
</div>
@endsection
