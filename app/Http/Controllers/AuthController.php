<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    public function register(RegisterRequest $request)
    {
        $user = $request->validated();
        User::create($user);
        return to_route('auth.login')->with('alert', 'inscription réussie');
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('alert', 'connexion reussie');
        }

        return back()->withErrors([
            'error' => 'les informations saisies sont incorrectes ou ne se correspondent pas',
        ])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('auth.login')->with('alert', 'déconnexion réussie');
    }
}
