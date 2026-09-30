@extends('layouts.app')
@section('guest')
<div class="login-page"><div class="login-box">
    <div class="eyebrow">Monitoring dan Evaluasi</div><h1>Monev KDH/WKDH</h1><p>Kelola program prioritas, strategis, dan progres pelaksanaannya.</p>
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@monev.test"></div>
        <div class="field"><label for="password">Kata sandi</label><input id="password" type="password" name="password" required placeholder="password"></div>
        <label style="font-weight:normal;margin-bottom:20px"><input style="width:auto;margin-right:7px" type="checkbox" name="remember"> Ingat saya</label>
        <button class="btn btn-primary" style="width:100%">Masuk ke aplikasi</button>
    </form>
    <p class="help" style="margin-top:22px;margin-bottom:0">Bappelitbangda @2026</p>
</div></div>
@endsection
