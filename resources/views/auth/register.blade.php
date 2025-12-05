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

<form action="{{route('register')}}" method="POST">
    @csrf
    <label for="login">Логин</label>
    <input type="login" name="login" id="login">
    <label for="password">Пароль</label>
    <input type="password" name="password" id="password">
    <button type="submit">Зарегестрироваться</button>

</form>

<a href="{{route('login')}}">Войти</a>
 </article>
@endsection
