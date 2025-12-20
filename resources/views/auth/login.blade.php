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
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="login" class="form-label">Логин</label>
                <input type="login" name="login">
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" name="password">
            </div>
            <button type="submit">Войти</button>
        </form>
        <a href="{{ route('register') }}">Зарегестрироваться</a>
    </article>

@endsection
