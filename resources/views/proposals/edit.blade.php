@extends('layouts.app')
@section('content')
    <div class="eyebrow">Perbarui data</div>
    <div class="card">
        <form method="post" enctype="multipart/form-data" action="{{ route('proposals.update', $proposal) }}">@method('put')
            @include('proposals._form')
        </form>
</div>@endsection