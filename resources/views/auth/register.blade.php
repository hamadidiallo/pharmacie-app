@extends('layout')
<nav>
    @include('app.menu')
</nav>

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="card shadow-lg border-0 rounded-4 p-4" style="width: 100%; max-width: 500px;">

            <div class="text-center mb-4">

                <h1 class="fw-bold text-primary">
                    INSCRIPTION
                </h1>

                <p class="text-muted">
                    Créez votre compte utilisateur
                </p>

            </div>

            <form action="" method="post">

                @csrf

                <div class="mb-3">

                    <x-component.input name="firstname" type="text" value="{{ old('firstname') }}" label="Prénom :" />

                </div>

                <div class="mb-3">

                    <x-component.input name="lastname" type="text" value="{{ old('lastname') }}" label="Nom :" />

                </div>

                <div class="mb-3">

                    <x-component.input name="email" type="email" value="{{ old('email') }}" label="Email :" />

                </div>

                <div class="mb-4">

                    <x-component.input name="password" type="password" value="" label="Mot de passe :" />

                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">

                    CREER UN COMPTE

                </button>

            </form>

            <div class="text-center mt-4">

                <small class="text-muted">
                    Vous avez déjà un compte ?
                </small>

                <br>

                <a href="" class="text-decoration-none fw-bold text-primary">

                    Se connecter

                </a>

            </div>

        </div>

    </div>
@endsection
