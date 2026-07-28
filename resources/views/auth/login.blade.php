@extends('layout')

@section('titre', 'Connexion — GESTA PHARM')

@section('content')
    <div class="mx-auto max-w-sm py-8">

        <div class="mb-8">
            <p class="mb-2 font-mono text-xs uppercase tracking-widest text-officine-600">Officine</p>
            <h1 class="text-2xl font-bold tracking-tight">CONNEXION</h1>
            <p class="mt-1 text-sm text-ink-soft">Ouvrez la caisse et reprenez le comptoir.</p>
        </div>

        <div class="card-officine p-6">

            <form method="post">

                @csrf

                @error('error')
                    <div class="notice-error mb-5">{{ $message }}</div>
                @enderror

                <x-component.input name="email" type="email" value="{{ old('email') }}" label="Email" />

                <x-component.input name="password" type="password" label="Mot de passe" />

                <button class="btn-primary w-full">Se connecter</button>

            </form>

        </div>

        <p class="mt-6 text-center text-sm text-ink-soft">
            Pas encore de compte ?
            <a href="{{ route('auth.register') }}"
                class="font-semibold text-officine-600 underline underline-offset-4 hover:text-officine-700">
                Créer un compte
            </a>
        </p>

    </div>
@endsection
