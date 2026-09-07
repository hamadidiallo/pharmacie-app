@extends('layout')

@section('titre', 'Utilisateurs & Rôles — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Gestion des utilisateurs</h1>
            <p class="text-xs text-slate-500 font-medium">Contrôle des accès et rôles de l'officine (RBAC)</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-3">
        <form method="get" class="flex items-center gap-2">
            <label for="q" class="sr-only">Rechercher un utilisateur</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search" id="q" name="q" value="{{ $recherche }}" placeholder="Rechercher par nom, @username, tél, email…"
                    class="field-input h-10 w-44 sm:w-[290px] pl-9">
            </div>
        </form>

        <button type="button" data-dialog="creer-utilisateur" class="btn-primary">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <line x1="19" y1="8" x2="19" y2="14" />
                <line x1="22" y1="11" x2="16" y2="11" />
            </svg>
            <span class="hidden sm:inline">Nouvel utilisateur</span>
        </button>
    </div>
@endsection

@section('content')

    @php
        $nbAdmins = $users->filter(fn($u) => $u->isAdmin())->count();
        $nbPharmaciens = $users->filter(fn($u) => $u->isPharmacien())->count();
        $nbCaissiers = $users->filter(fn($u) => $u->isCaissier())->count();
    @endphp

    {{-- Mini-Cartes Statistiques des Rôles --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="panel p-4 flex items-center gap-3.5">
            <div class="grid size-10 place-items-center rounded-xl bg-slate-100 text-slate-700 font-bold">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <div class="num text-xl font-bold text-slate-900">{{ $users->total() }}</div>
                <div class="text-xs text-slate-500 font-medium">Total employés</div>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3.5">
            <div class="grid size-10 place-items-center rounded-xl bg-purple-50 text-purple-700 font-bold">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div>
                <div class="num text-xl font-bold text-purple-700">{{ $nbAdmins }}</div>
                <div class="text-xs text-slate-500 font-medium">Administrateurs</div>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3.5">
            <div class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700 font-bold">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/>
                </svg>
            </div>
            <div>
                <div class="num text-xl font-bold text-emerald-700">{{ $nbPharmaciens }}</div>
                <div class="text-xs text-slate-500 font-medium">Pharmaciens</div>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3.5">
            <div class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-700 font-bold">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>
            </div>
            <div>
                <div class="num text-xl font-bold text-blue-700">{{ $nbCaissiers }}</div>
                <div class="text-xs text-slate-500 font-medium">Caissiers</div>
            </div>
        </div>
    </div>

    {{-- Tableau des Utilisateurs Moderne --}}
    <div class="panel overflow-hidden">

        <div class="overflow-x-auto">
            <table class="table-data">

                <thead>
                    <tr>
                        <th>COLLABORATEUR</th>
                        <th>COORDONNÉES & CONTACT</th>
                        <th>RÔLE ATTRIBUÉ</th>
                        <th>INSCRIPTION</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($users as $user)
                        @php
                            $initiales = mb_strtoupper(mb_substr($user->firstname, 0, 1) . mb_substr($user->lastname, 0, 1));
                            $estMoi = $user->id === auth()->id();
                        @endphp
                        <tr>

                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-slate-700 to-slate-900 text-xs font-bold text-white shadow-xs">
                                        {{ $initiales }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <span>{{ $user->firstname }} {{ $user->lastname }}</span>
                                            @if ($estMoi)
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Vous</span>
                                            @endif
                                        </div>
                                        @if ($user->username)
                                            <div class="text-xs font-medium text-slate-500">
                                                <span class="text-emerald-600 font-semibold">@</span>{{ $user->username }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="text-slate-800 font-medium text-xs">{{ $user->email }}</div>
                                @if ($user->telephone)
                                    <div class="flex items-center gap-1 text-[11px] text-slate-500 mt-0.5">
                                        <svg class="size-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        <span class="num font-semibold text-slate-600">{{ $user->telephone }}</span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span @class([
                                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1',
                                    'bg-purple-50 text-purple-800 ring-purple-600/20' => $user->isAdmin(),
                                    'bg-emerald-50 text-emerald-800 ring-emerald-600/20' => $user->isPharmacien(),
                                    'bg-blue-50 text-blue-800 ring-blue-600/20' => $user->isCaissier(),
                                ])>
                                    <span @class([
                                        'size-1.5 rounded-full',
                                        'bg-purple-600' => $user->isAdmin(),
                                        'bg-emerald-600' => $user->isPharmacien(),
                                        'bg-blue-600' => $user->isCaissier(),
                                    ])></span>
                                    {{ $user->role?->libelle() ?? 'Non défini' }}
                                </span>
                            </td>

                            <td class="num text-slate-600">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">

                                    <button type="button" data-dialog="modifier-user-{{ $user->id }}"
                                        class="btn-icon" aria-label="Modifier {{ $user->firstname }}" title="Modifier">
                                        <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </button>

                                    @if (!$estMoi)
                                        <button type="button" data-dialog="supprimer-user-{{ $user->id }}"
                                            class="btn-icon-danger" aria-label="Supprimer {{ $user->firstname }}" title="Supprimer">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
                                            </svg>
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-6 py-4">
            <span class="text-xs font-medium text-slate-500">
                Affichage de {{ $users->firstItem() ?? 0 }} à {{ $users->lastItem() ?? 0 }} sur {{ $users->total() }} utilisateurs
            </span>
            {{ $users->links() }}
        </div>

    </div>

    {{-- Dialog Création Utilisateur --}}
    <dialog id="creer-utilisateur" class="dialog-panel">

        <div class="panel-head">
            <h2 class="panel-title">Créer un nouveau compte utilisateur</h2>
            <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label" for="new_firstname">Prénom</label>
                        <input type="text" id="new_firstname" name="firstname" required class="field-input" placeholder="ex: Fatoumata">
                    </div>
                    <div>
                        <label class="field-label" for="new_lastname">Nom</label>
                        <input type="text" id="new_lastname" name="lastname" required class="field-input" placeholder="ex: Traoré">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label" for="new_username">Nom d'utilisateur</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">@</span>
                            <input type="text" id="new_username" name="username" class="field-input pl-7" placeholder="f_traore">
                        </div>
                    </div>
                    <div>
                        <label class="field-label" for="new_telephone">N° de téléphone</label>
                        <input type="tel" id="new_telephone" name="telephone" class="field-input" placeholder="+223 70 00 00 00">
                    </div>
                </div>

                <div>
                    <label class="field-label" for="new_email">Adresse email</label>
                    <input type="email" id="new_email" name="email" required class="field-input" placeholder="employe@gestapharm.ml">
                </div>

                <div>
                    <label class="field-label" for="new_role">Rôle et permissions</label>
                    <select id="new_role" name="role" required class="field-input bg-white font-medium">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}">{{ $role->libelle() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label" for="new_password">Mot de passe provisoire</label>
                    <input type="password" id="new_password" name="password" required minlength="8" class="field-input"
                        placeholder="Au moins 8 caractères">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-4">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <button type="submit" class="btn-primary">Créer l'utilisateur</button>
            </div>
        </form>

    </dialog>

    {{-- Dialogs Modification & Suppression par utilisateur --}}
    @foreach ($users as $user)

        <dialog id="modifier-user-{{ $user->id }}" class="dialog-panel">

            <div class="panel-head">
                <h2 class="panel-title">Modifier : {{ $user->firstname }} {{ $user->lastname }}</h2>
                <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Prénom</label>
                            <input type="text" name="firstname" value="{{ $user->firstname }}" required class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Nom</label>
                            <input type="text" name="lastname" value="{{ $user->lastname }}" required class="field-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Nom d'utilisateur</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">@</span>
                                <input type="text" name="username" value="{{ $user->username }}" class="field-input pl-7" placeholder="nom_utilisateur">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">N° de téléphone</label>
                            <input type="tel" name="telephone" value="{{ $user->telephone }}" class="field-input" placeholder="+223 70 00 00 00">
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Adresse email</label>
                        <input type="email" name="email" value="{{ $user->email }}" required class="field-input">
                    </div>

                    <div>
                        <label class="field-label">Rôle et permissions</label>
                        <select name="role" required class="field-input bg-white font-medium">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->libelle() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="field-label">
                            Nouveau mot de passe <span class="text-slate-400 font-normal">(laisser vide pour ne pas changer)</span>
                        </label>
                        <input type="password" name="password" minlength="8" class="field-input" placeholder="Optionnel">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-4">
                    <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                </div>
            </form>

        </dialog>

        @if ($user->id !== auth()->id())
            <dialog id="supprimer-user-{{ $user->id }}" class="dialog-panel">

                <div class="panel-head">
                    <h2 class="panel-title text-red-700">Supprimer cet utilisateur ?</h2>
                </div>

                <div class="p-6 text-sm text-slate-600">
                    Êtes-vous sûr de vouloir supprimer définitivement le compte de <strong class="font-bold text-slate-900">{{ $user->firstname }} {{ $user->lastname }}</strong> ({{ $user->email }}) ?
                </div>

                <div class="flex justify-end gap-2.5 border-t border-slate-100 px-6 py-4">
                    <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                    <form action="{{ route('users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Confirmer la suppression</button>
                    </form>
                </div>

            </dialog>
        @endif

    @endforeach

@endsection
