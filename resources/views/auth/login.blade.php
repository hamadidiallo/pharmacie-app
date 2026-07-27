@extends('layout')
<nav>
    @include('app.menu')
</nav>
@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="card shadow-lg border-0 rounded-4 p-4"
            style="width: 100%; max-width: 450px;
        background: linear-gradient(135deg, #0d6efd, #6610f2);">

            <div class="text-center mb-4">

                <h1 class="fw-bold text-white">
                    CONNEXION
                </h1>

                <p class="text-light">
                    Connectez-vous à votre espace pharmacie
                </p>

            </div>

            <form class="login-form" method="post">

                @csrf

                @error('error')
                    <div class="alert alert-danger text-center">

                        {{ $message }}

                    </div>
                @enderror

                <div class="mb-3">

                    <label class="text-white fw-bold mb-1">
                        Email :
                    </label>

                    <x-component.input name="email" type="email" value="{{ old('email') }}" label="" />

                </div>

                <div class="mb-4">

                    <label class="text-white fw-bold mb-1">
                        Mot de passe :
                    </label>

                    <x-component.input name="password" type="password" value="" label="" />

                </div>

                <button class="btn btn-warning w-100 py-2 fw-bold rounded-3 shadow connecter">

                    SE CONNECTER

                </button>

            </form>

            <div class="text-center mt-4">

                <small class="text-light">
                    Vous n'avez pas de compte ?
                </small>

                <br>

                <a href="{{route('auth.register')}}" class="text-warning fw-bold text-decoration-none">

                    Créer un compte

                </a>

            </div>

        </div>

    </div>

    <style>
        .card {
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .connecter {
            transition: 0.3s;
        }

        .connecter:hover {
            background-color: white;
            color: #0d6efd;
            transform: scale(1.02);
        }

        input {
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endsection
