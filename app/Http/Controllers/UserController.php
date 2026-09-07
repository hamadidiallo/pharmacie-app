<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $recherche = trim((string) $request->query('q'));

        $users = User::query()
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($q) use ($recherche) {
                    $q->where('firstname', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('lastname', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('username', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('telephone', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('email', 'LIKE', '%' . $recherche . '%');
                });
            })
            ->orderBy('lastname')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::cases(),
            'recherche' => $recherche,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        return redirect()->route('users.index')->with('alert', 'Utilisateur créé avec succès.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $donnees = $request->validated();

        // Ne pas écraser le mot de passe s'il n'a pas été renseigné
        if (empty($donnees['password'])) {
            unset($donnees['password']);
        }

        $user->update($donnees);

        return redirect()->route('users.index')->with('alert', "Utilisateur {$user->firstname} {$user->lastname} mis à jour.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('alert', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $nom = "{$user->firstname} {$user->lastname}";
        $user->delete();

        return redirect()->route('users.index')->with('alert', "L'utilisateur {$nom} a été supprimé.");
    }
}
