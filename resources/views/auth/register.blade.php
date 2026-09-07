@extends('layout-auth')

@section('titre', 'Créer un compte — GESTA PHARM')

@section('content')

    <div class="panel p-8 sm:p-10 shadow-xl shadow-slate-900/5">

        <div class="mb-8 text-center sm:text-left">
            <div class="mb-4 inline-flex size-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" x2="19" y1="8" y2="14"/>
                    <line x1="22" x2="16" y1="11" y2="11"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Créer un compte</h1>
            <p class="mt-1 text-xs text-slate-500">Inscrivez un nouveau membre de l'équipe officinale</p>
        </div>

        <form action="{{ route('auth.register') }}" method="post" class="space-y-3.5">

            @csrf

            <div class="grid gap-x-3.5 sm:grid-cols-2">
                <x-component.input name="firstname" value="{{ old('firstname') }}" label="Prénom" placeholder="ex: Amadou" />
                <x-component.input name="lastname" value="{{ old('lastname') }}" label="Nom" placeholder="ex: Traoré" />
            </div>

            <x-component.input name="email" type="email" value="{{ old('email') }}" label="Adresse e-mail"
                placeholder="pharmacien@gestapharm.ml" />

            <x-component.input name="password" type="password" label="Mot de passe" placeholder="8 caractères minimum" />

            <button type="submit" class="btn-primary w-full h-12 text-sm font-bold shadow-md shadow-emerald-700/20 mt-2">
                <span>Créer mon compte</span>
                <svg class="size-4 ml-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>

        </form>

        <div class="mt-8 border-t border-slate-100 pt-6 text-center">
            <p class="text-xs text-slate-500">
                Vous avez déjà un compte ?
                <a href="{{ route('auth.login') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">
                    Se connecter
                </a>
            </p>
        </div>

    </div>

@endsection
