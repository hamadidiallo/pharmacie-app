<nav class="navbar navbar-expand-lg shadow-sm py-3" style="background: linear-gradient(135deg, #0d6efd, #6610f2);">

    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand fw-bold text-white fs-3" href="#">

            💊 GESTA PHARM

        </a>

        <!-- MENU MOBILE -->
        <button class="navbar-toggler bg-white" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent">

            <span class="navbar-toggler-icon"></span>

        </button>

        <!-- MENU -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <!-- LIENS CENTRE -->
            @auth

                <ul class="navbar-nav mx-auto gap-2">

                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold nav-hover" href="{{route('dashboard')}}">

                            🏠 Dashboard

                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold nav-hover" href="{{route('medicaments.index')}}">

                            💊 Médicaments

                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold nav-hover" href="{{route('ventes.index')}}">

                            🛒 Ventes

                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold nav-hover" href="{{route('dashboard')}}">

                            📊 Statistiques

                        </a>
                    </li>

                </ul>

            @endauth

            <!-- BOUTONS A DROITE -->
            <div class="ms-auto d-flex gap-2 align-items-center">

                @guest

                    <a href="{{ route('auth.login') }}" class="btn btn-light fw-bold rounded-pill px-4">

                        SE CONNECTER

                    </a>

                    <a href="{{ route('auth.register') }}" class="btn btn-warning fw-bold rounded-pill px-4">

                        INSCRIPTION

                    </a>

                @endguest

                @auth

                    <span class="text-white fw-bold">

                        👋 {{ auth()->user()->firstname }}

                    </span>

                    <form action="{{ route('auth.logout') }}" method="post">

                        @csrf
                        @method('delete')

                        <button class="btn btn-danger rounded-pill fw-bold px-4">

                            DÉCONNEXION

                        </button>

                    </form>

                @endauth

            </div>

        </div>

    </div>

</nav>

<style>
    .nav-hover {

        transition: 0.3s;
        border-radius: 10px;
        padding: 8px 15px !important;

    }

    .nav-hover:hover {

        background-color: rgba(255, 255, 255, 0.2);

        transform: translateY(-2px);

    }

    .btn {

        transition: 0.3s;

    }

    .btn:hover {

        transform: scale(1.05);

    }
</style>
