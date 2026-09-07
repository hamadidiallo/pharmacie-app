@extends('layout-auth')

@section('titre', 'Connexion — GESTA PHARM')

@section('content')

    <div class="panel p-8 sm:p-10 shadow-xl shadow-slate-900/5">

        <div class="mb-8 text-center sm:text-left">
            <div class="mb-4 inline-flex size-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Espace de connexion</h1>
            <p class="mt-1 text-xs text-slate-500">Accédez à votre compte comptoir ou pharmacien</p>
        </div>

        <form method="post" action="{{ route('auth.login') }}" class="space-y-4">

            @csrf

            @error('error')
                <div class="notice-error mb-4">{{ $message }}</div>
            @enderror

            <div>
                <x-component.input name="email" type="text" value="{{ old('email') }}" label="Identifiant (e-mail ou nom d'utilisateur)"
                    placeholder="ex: pharmacien@gestapharm.ml ou amadou_d" />
            </div>

            <div>
                <x-component.input name="password" type="password" label="Mot de passe" placeholder="••••••••" />
            </div>

            <button type="submit" class="btn-primary w-full h-12 text-sm font-bold shadow-md shadow-emerald-700/20 mt-2">
                <span>Se connecter</span>
                <svg class="size-4 ml-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>

        </form>

        <div class="mt-8 border-t border-slate-100 pt-6 text-center">
            <p class="text-xs text-slate-500">
                Vous n'avez pas encore d'accès ?
                <a href="{{ route('auth.register') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">
                    Créer un compte
                </a>
            </p>
        </div>

    </div>

@endsection
