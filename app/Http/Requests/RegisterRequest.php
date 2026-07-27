<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
        ];
    }
    public function messages()
    {
        return [
            'firstname.required' => 'le prénom est obligatoire',
            'lastname.required' => 'le nom est obligatoire',
            'email.required' => 'Email est obligatoire',
            'password.required' => 'le mot de passe est obligatoire.',
            'password.min' => 'le mot de passe doit contenir aux moins 4 caractères',
            'email.unique' => 'le champ email doit être unique pour un utilisateur',
        ];
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('auth.login')->with('alert', 'déconnexion réussie');
    }
}
