@extends('layout-auth')

@section('titre', 'Connexion — GESTA PHARM')

@section('content')

    <h1 class="mb-1.5 text-[26px] font-bold tracking-tight">Connexion</h1>
    <p class="mb-8 text-sm text-muted">Connectez-vous à votre espace pharmacie</p>

    <form method="post">

        @csrf

        @error('error')
            <div class="notice-error mb-5">{{ $message }}</div>
        @enderror

        <x-component.input name="email" type="email" value="{{ old('email') }}" label="Adresse e-mail"
            placeholder="pharmacien@gestapharm.ml" />

        <x-component.input name="password" type="password" label="Mot de passe" placeholder="••••••••" />

        <button class="btn-primary btn-lg mt-2 w-full">Se connecter</button>

    </form>

    <p class="mt-5 text-center text-[13px] text-muted">
        Vous n'avez pas de compte ?
        <a href="{{ route('auth.register') }}" class="font-semibold text-brand-500 hover:text-brand-600">
            Créer un compte
        </a>
    </p>

@endsection
