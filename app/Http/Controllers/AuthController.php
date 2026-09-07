<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register', $this->chiffresOfficine());
    }

    /** Chiffres affichés sur le panneau de marque des écrans invités. */
    private function chiffresOfficine(): array
    {
        return [
            'references' => Medicament::count(),
            'caMois' => Vente::whereBetween('date_vente', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
        ];
    }
    public function register(RegisterRequest $request)
    {
        $user = $request->validated();
        User::create($user);
        return to_route('auth.login')->with('alert', 'inscription réussie');
    }
    public function showLoginForm()
    {
        return view('auth.login', $this->chiffresOfficine());
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $identifiant = $request->input('email');
        $champ = filter_var($identifiant, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $champ => $identifiant,
            'password' => $request->input('password'),
        ];

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
