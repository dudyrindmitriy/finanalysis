@if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
@endif
<form action="{{route('register')}}" method="POST">
    @csrf
    <input type="login" name="login">
    <input type="password" name="password">
    <button type="submit">Зарегестрироваться</button>

</form>

<a href="{{route('login')}}">Войти</a>
