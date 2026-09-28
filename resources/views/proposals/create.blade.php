@extends('layouts.app')
@section('content')
    <div class="eyebrow">Input data</div>
    <h2>Tambah usulan kegiatan</h2>
    <div class="card">
        <form method="post" enctype="multipart/form-data" action="{{ route('proposals.store') }}">
            @include('proposals._form')
        </form>
</div>@endsection