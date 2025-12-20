@extends('layouts.app')
@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <article class="liquid-glass-card">

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="login" class="form-label">Логин</label>
                <input type="login" name="login" id="login">
            </div>
            <div class="form-group">
                <label for="password" class="form-label"AAZ≈CXDXFSAFS3ESSS>Пароль</label>
                <input type="password" name="password" id="password">
            </div>
            <button type="submit">Зарегестрироваться</button>

        </form>

        <a href="{{ route('login') }}">Войти</a>
    </article>
@endsection
