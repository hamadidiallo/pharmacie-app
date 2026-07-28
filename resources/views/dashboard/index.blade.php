@extends('layout')

@section('titre', 'Tableau de bord — GESTA PHARM')

@section('content')

    {{-- Ce qui compte en ouvrant la caisse : la recette du jour, puis ce qui cloche. --}}
    <section class="card-officine mb-6 overflow-hidden">

        <div class="grid gap-px bg-rule sm:grid-cols-4">

            <div class="bg-surface p-6 sm:col-span-4">
                <p class="font-mono text-xs uppercase tracking-widest text-officine-600">Recette du jour</p>
                <p class="figure mt-3 text-4xl font-bold sm:text-5xl">
                    {{ number_format($venteJour, 0, ',', ' ') }}
                    <span class="text-base font-medium text-ink-soft">FCFA</span>
                </p>
                <p class="mt-2 text-sm text-ink-soft">{{ now()->translatedFormat('l j F Y') }}</p>
            </div>

            @foreach ([['Semaine', $venteSemaine], ['Mois', $venteMois], ['Trimestre', $venteTrimestre], ['Médicaments', $totalMedicaments]] as [$libelle, $montant])
                <div class="bg-surface p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-soft">{{ $libelle }}</p>
                    <p class="figure mt-2 text-xl font-semibold">
                        {{ number_format($montant, 0, ',', ' ') }}
                        @unless ($loop->last)
                            <span class="text-xs font-medium text-ink-soft">FCFA</span>
                        @endunless
                    </p>
                </div>
            @endforeach

        </div>

    </section>

    {{-- Alertes --}}
    @php
        $alertes = [
            ['route' => 'dashboard.ruptureStock', 'libelle' => 'En rupture', 'nombre' => $nbRuptures, 'ton' => 'rouge'],
            ['route' => 'dashboard.stockFaible', 'libelle' => 'Stock faible', 'nombre' => $nbStockFaible, 'ton' => 'ambre'],
            ['route' => 'dashboard.expire', 'libelle' => 'Expire sous 30 jours', 'nombre' => $nbExpirations, 'ton' => 'ambre'],
        ];
    @endphp

    <section class="mb-6">

        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-ink-soft">À traiter</h2>

        <div class="grid gap-3 sm:grid-cols-3">

            @foreach ($alertes as $alerte)
                <a href="{{ route($alerte['route']) }}"
                    @class([
                        'card-officine flex items-center justify-between p-5 transition-colors',
                        'hover:border-rouge-100 hover:bg-rouge-50' => $alerte['ton'] === 'rouge' && $alerte['nombre'] > 0,
                        'hover:border-ambre-100 hover:bg-ambre-50' => $alerte['ton'] === 'ambre' && $alerte['nombre'] > 0,
                        'hover:border-officine-100 hover:bg-officine-50' => $alerte['nombre'] === 0,
                    ])>

                    <span class="text-sm font-medium">{{ $alerte['libelle'] }}</span>

                    <span
                        @class([
                            'figure text-2xl font-bold',
                            'text-rouge-700' => $alerte['ton'] === 'rouge' && $alerte['nombre'] > 0,
                            'text-ambre-700' => $alerte['ton'] === 'ambre' && $alerte['nombre'] > 0,
                            'text-ink-soft' => $alerte['nombre'] === 0,
                        ])>
                        {{ $alerte['nombre'] }}
                    </span>

                </a>
            @endforeach

        </div>

    </section>

    {{-- Actions --}}
    <section class="card-officine p-5">

        <div class="flex flex-wrap items-center gap-3">

            <a href="{{ route('ventes.create') }}" class="btn-primary">Nouvelle vente</a>
            <a href="{{ route('medicament.create') }}" class="btn-ghost">Ajouter un produit</a>

            <span class="hidden h-6 w-px bg-rule sm:block"></span>

            <a href="{{ route('medicaments.index') }}" class="btn-ghost btn-sm">Médicaments</a>
            <a href="{{ route('ventes.index') }}" class="btn-ghost btn-sm">Historique des ventes</a>
            <a href="{{ route('dashboard.top_produit') }}" class="btn-ghost btn-sm">Top 5 des produits</a>

            <button type="button" data-dialog="aide" class="btn-ghost btn-sm ml-auto">Aide</button>

        </div>

    </section>

    {{-- Aide --}}
    <dialog id="aide" class="dialog-officine">

        <div class="card-head">
            <h2 class="card-title">Centre d'aide</h2>
            <button type="button" data-dialog-close class="btn-ghost btn-sm" aria-label="Fermer">Fermer</button>
        </div>

        <div class="max-h-[70vh] space-y-5 overflow-y-auto p-5 text-sm">

            <p class="text-ink-soft">
                GESTA PHARM gère le stock de l'officine, les ventes au comptoir et
                l'impression des tickets.
            </p>

            <div>
                <h3 class="mb-2 font-semibold">Médicaments</h3>
                <ul class="list-disc space-y-1 pl-5 text-ink-soft">
                    <li>Ajouter, modifier et supprimer un produit</li>
                    <li>Un produit identique déjà en base voit son stock cumulé</li>
                    <li>Suivre les dates d'expiration</li>
                </ul>
            </div>

            <div>
                <h3 class="mb-2 font-semibold">Ventes</h3>
                <ul class="list-disc space-y-1 pl-5 text-ink-soft">
                    <li>Rechercher un médicament et l'ajouter au panier</li>
                    <li>Le stock est contrôlé avant validation</li>
                    <li>Supprimer une vente remet les quantités en stock</li>
                </ul>
            </div>

            <div class="rule-dashed pt-5">
                <h3 class="mb-2 font-semibold">Assistance</h3>
                <ul class="space-y-1 text-ink-soft">
                    <li class="figure">+223 78 14 43 59</li>
                    <li class="figure">+223 95 57 60 40</li>
                    <li>ballaldialloubehd78@gmail.com</li>
                </ul>
                <p class="mt-4 text-xs text-ink-soft">
                    Développé par le cabinet de consulting Ballal Dialloubé.
                </p>
            </div>

        </div>

    </dialog>

@endsection
