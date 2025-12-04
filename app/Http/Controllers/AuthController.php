<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = User::where('login', $validated['login'])->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            Auth::login($user);
            return redirect(route('home'));
        }
        return back()->withErrors('Неправильный логин или пароль');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|unique:users',
            'password' => 'required|string'
        ]);
        $user = new User;
        $user->login = $validated['login'];
        $user->password = bcrypt($validated['password']);
        $user->save();
        Auth::login($user);
        return redirect(route('home'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('login'));
    }
}
