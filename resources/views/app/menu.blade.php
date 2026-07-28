@php
    $liens = [
        ['route' => 'dashboard', 'libelle' => 'Tableau de bord'],
        ['route' => 'medicaments.index', 'libelle' => 'Médicaments'],
        ['route' => 'ventes.index', 'libelle' => 'Ventes'],
        ['route' => 'dashboard.top_produit', 'libelle' => 'Statistiques'],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-rule bg-surface/95 backdrop-blur">
    <div class="mx-auto flex w-full max-w-6xl items-center gap-4 px-4 py-3 sm:px-6">

        {{-- La croix verte : le signe des officines --}}
        <a href="{{ auth()->check() ? route('dashboard') : route('auth.login') }}"
            class="flex shrink-0 items-center gap-2.5">

            <span class="grid size-8 place-items-center rounded bg-officine-500" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-5 fill-white">
                    <path d="M9.5 3h5v6.5H21v5h-6.5V21h-5v-6.5H3v-5h6.5V3Z" />
                </svg>
            </span>

            <span class="text-sm font-bold uppercase tracking-widest">GESTA PHARM</span>

        </a>

        @auth
            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="menu-principal"
                class="btn-ghost btn-sm ml-auto md:hidden">
                Menu
            </button>
        @endauth

        <nav id="menu-principal"
            class="@auth hidden @endauth absolute inset-x-0 top-full border-b border-rule bg-surface px-4 py-3
                   md:static md:mx-auto md:flex md:border-0 md:bg-transparent md:p-0">

            @auth
                <ul class="flex flex-col gap-1 md:flex-row md:items-center md:gap-1">
                    @foreach ($liens as $lien)
                        <li>
                            <a href="{{ route($lien['route']) }}"
                                @class([
                                    'block rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    'bg-officine-50 text-officine-700' => request()->routeIs($lien['route']),
                                    'text-ink-soft hover:bg-officine-50 hover:text-officine-700' => !request()->routeIs(
                                        $lien['route']),
                                ])>
                                {{ $lien['libelle'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endauth

        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-2">

            @guest
                <a href="{{ route('auth.login') }}" class="btn-ghost btn-sm">Se connecter</a>
                <a href="{{ route('auth.register') }}" class="btn-primary btn-sm">Créer un compte</a>
            @endguest

            @auth
                <span class="hidden text-sm text-ink-soft sm:inline">
                    {{ auth()->user()->firstname }}
                </span>

                <form action="{{ route('auth.logout') }}" method="post">
                    @csrf
                    @method('delete')
                    <button class="btn-ghost btn-sm">Déconnexion</button>
                </form>
            @endauth

        </div>

    </div>
</header>
