@extends('layout-auth')

@section('titre', 'Créer un compte — GESTA PHARM')

@section('content')

    <h1 class="mb-1.5 text-[26px] font-bold tracking-tight">Créer un compte</h1>
    <p class="mb-8 text-sm text-muted">Un compte par personne travaillant au comptoir</p>

    <form action="{{ route('auth.register') }}" method="post">

        @csrf

        <div class="grid gap-x-4 sm:grid-cols-2">
            <x-component.input name="firstname" value="{{ old('firstname') }}" label="Prénom" />
            <x-component.input name="lastname" value="{{ old('lastname') }}" label="Nom" />
        </div>

        <x-component.input name="email" type="email" value="{{ old('email') }}" label="Adresse e-mail"
            placeholder="pharmacien@gestapharm.ml" />

        <x-component.input name="password" type="password" label="Mot de passe" placeholder="8 caractères minimum" />

        <button class="btn-primary btn-lg mt-2 w-full">Créer le compte</button>

    </form>

    <p class="mt-5 text-center text-[13px] text-muted">
        Vous avez déjà un compte ?
        <a href="{{ route('auth.login') }}" class="font-semibold text-brand-500 hover:text-brand-600">
            Se connecter
        </a>
    </p>

@endsection
