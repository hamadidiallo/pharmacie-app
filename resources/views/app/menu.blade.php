@php
    $liens = [
        [
            'route' => 'dashboard',
            'libelle' => 'Tableau de bord',
            'icone' =>
                '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        ],
        [
            'route' => 'medicaments.index',
            'libelle' => 'Médicaments',
            'icone' =>
                '<path d="M10.5 20.5 4 14a5 5 0 0 1 7-7l1 1 1-1a5 5 0 0 1 7 7l-6.5 6.5a2 2 0 0 1-3 0Z"/><path d="m8.5 8.5 7 7"/>',
        ],
        [
            'route' => 'ventes.create',
            'libelle' => 'Nouvelle vente',
            'icone' =>
                '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2l2.2 12.2a1.5 1.5 0 0 0 1.5 1.3h8.6a1.5 1.5 0 0 0 1.5-1.2L21 7H6"/>',
        ],
        [
            'route' => 'ventes.index',
            'libelle' => 'Historique',
            'icone' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/>',
        ],
        [
            'route' => 'dashboard.statistiques',
            'libelle' => 'Statistiques',
            'icone' =>
                '<path d="M3 3v18h18"/><rect x="7" y="11" width="3" height="6" rx="0.6"/><rect x="12.5" y="7" width="3" height="10" rx="0.6"/><rect x="18" y="13" width="3" height="4" rx="0.6"/>',
        ],
    ];

    $utilisateur = auth()->user();
    $initiales = mb_strtoupper(mb_substr($utilisateur->firstname, 0, 1) . mb_substr($utilisateur->lastname, 0, 1));
@endphp

{{-- Voile de fond, mobile uniquement --}}
<div data-menu-backdrop class="fixed inset-0 z-30 hidden bg-brand-900/50 lg:hidden"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-sidebar shrink-0 -translate-x-full flex-col bg-brand-900 p-4
           text-sidebar-text transition-transform duration-200 lg:static lg:translate-x-0">

    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 pb-5 pt-1">
        <span class="inline-flex size-9 items-center justify-center rounded-lg bg-leaf">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"
                stroke-linecap="round">
                <path d="M12 6v12M6 12h12" />
            </svg>
        </span>
        <span>
            <span class="block whitespace-nowrap text-[15px] font-bold tracking-wide text-white">GESTA PHARM</span>
            <span class="block text-[11px] text-sidebar-muted">Kati · Mali</span>
        </span>
    </a>

    <p class="px-3 pb-1.5 pt-2 text-[11px] uppercase tracking-widest text-sidebar-label">Général</p>

    <nav class="flex flex-col gap-0.5">
        @foreach ($liens as $lien)
            @php $actif = request()->routeIs($lien['route']); @endphp
            <a href="{{ route($lien['route']) }}" @class(['nav-link-active' => $actif, 'nav-link' => !$actif])
                @if ($actif) aria-current="page" @endif>
                <svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    {!! $lien['icone'] !!}
                </svg>
                {{ $lien['libelle'] }}
            </a>
        @endforeach
    </nav>

    <div class="mt-auto flex items-center gap-3 rounded-xl bg-sidebar-card p-3">

        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-leaf text-sm font-bold text-white">
            {{ $initiales }}
        </span>

        <span class="min-w-0 flex-1 leading-tight">
            <span class="block truncate text-[13px] font-semibold text-white">
                {{ $utilisateur->firstname }} {{ $utilisateur->lastname }}
            </span>
            <span class="block text-[11px] text-sidebar-muted">Pharmacien</span>
        </span>

        <form action="{{ route('auth.logout') }}" method="post">
            @csrf
            @method('delete')
            <button class="grid size-8 place-items-center rounded-lg text-sidebar-muted transition-colors hover:bg-white/10 hover:text-white"
                aria-label="Se déconnecter" title="Se déconnecter">
                <svg class="size-[17px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="m16 17 5-5-5-5M21 12H9" />
                </svg>
            </button>
        </form>

    </div>

</aside>
