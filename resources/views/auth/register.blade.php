@extends('layout')

@section('titre', 'Créer un compte — GESTA PHARM')

@section('content')
    <div class="mx-auto max-w-sm py-8">

        <div class="mb-8">
            <p class="mb-2 font-mono text-xs uppercase tracking-widest text-officine-600">Officine</p>
            <h1 class="text-2xl font-bold tracking-tight">CRÉER UN COMPTE</h1>
            <p class="mt-1 text-sm text-ink-soft">Un compte par personne travaillant au comptoir.</p>
        </div>

        <div class="card-officine p-6">

            <form action="{{ route('auth.register') }}" method="post">

                @csrf

                <div class="grid gap-x-4 sm:grid-cols-2">
                    <x-component.input name="firstname" value="{{ old('firstname') }}" label="Prénom" />
                    <x-component.input name="lastname" value="{{ old('lastname') }}" label="Nom" />
                </div>

                <x-component.input name="email" type="email" value="{{ old('email') }}" label="Email" />

                <x-component.input name="password" type="password" label="Mot de passe" />

                <p class="-mt-2 mb-4 text-xs text-ink-soft">8 caractères minimum.</p>

                <button class="btn-primary w-full">Créer le compte</button>

            </form>

        </div>

        <p class="mt-6 text-center text-sm text-ink-soft">
            Vous avez déjà un compte ?
            <a href="{{ route('auth.login') }}"
                class="font-semibold text-officine-600 underline underline-offset-4 hover:text-officine-700">
                Se connecter
            </a>
        </p>

    </div>
@endsection
