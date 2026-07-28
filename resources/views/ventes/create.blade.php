@extends('layout')

@section('titre', 'Nouvelle vente — GESTA PHARM')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Nouvelle vente</h1>
        <p class="mt-1 text-sm text-ink-soft">Cherchez un produit, ajustez les quantités, encaissez.</p>
    </div>

    <div id="messageStock" class="mb-4 empty:mb-0" role="status"></div>

    <div class="grid gap-5 lg:grid-cols-5">

        {{-- Recherche --}}
        <section class="card-officine lg:col-span-2">

            <div class="card-head">
                <h2 class="card-title">Rechercher un produit</h2>
            </div>

            <div class="p-5">

                <label for="search" class="sr-only">Rechercher un médicament</label>
                <input type="search" id="search" class="field-input" placeholder="Nom du médicament" autofocus>

                <div id="resultats" class="mt-4 space-y-2">
                    <p class="py-8 text-center text-sm text-ink-soft">
                        Tapez les premières lettres d'un médicament.
                    </p>
                </div>

            </div>

        </section>

        {{-- Panier --}}
        <section class="card-officine lg:col-span-3">

            <div class="card-head">
                <h2 class="card-title">Panier</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="table-officine">

                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-right">Prix</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-right">Sous-total</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="panier">
                        {{-- rempli par resources/js/ventes/vente.js --}}
                    </tbody>

                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-rule px-5 py-4">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-soft">Total</p>
                    <p class="figure text-3xl font-bold">
                        <span id="total">0</span>
                        <span class="text-sm font-medium text-ink-soft">FCFA</span>
                    </p>
                </div>

                <button type="button" onclick="validerVente()" class="btn-primary">
                    Valider la vente
                </button>

            </div>

        </section>

    </div>

@endsection

@push('scripts')
    @vite('resources/js/ventes/vente.js')
@endpush
