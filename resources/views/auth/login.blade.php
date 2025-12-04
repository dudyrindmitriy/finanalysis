@if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
@endif
<form action="{{route('login')}}" method="POST">
    @csrf
    <input type="login" name="login">
    <input type="password" name="password">
    <button type="submit">Войти</button>
</form>
<a href="{{route('register')}}">Зарегестрироваться</a>
