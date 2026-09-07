@extends('layout')

@section('titre', 'Nouvelle vente — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <a href="{{ route('ventes.index') }}" class="btn-icon" aria-label="Retour aux ventes" title="Retour à l'historique">
            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Comptoir de vente</h1>
            <p class="text-xs text-slate-500">Ajoutez des produits au panier pour encaisser</p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Caisse active
        </span>
    </div>
@endsection

@section('content')

    <div id="messageStock" class="mb-4 empty:hidden" role="status"></div>

    <div class="grid gap-5 lg:grid-cols-[1.2fr_1fr]">

        {{-- Recherche médicaments --}}
        <div class="flex min-h-0 flex-col">
            <div class="panel p-4 mb-4">
                <label for="search" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Rechercher un produit en stock</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-emerald-600"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m21 21-4-4" />
                    </svg>
                    <input type="search" id="search" class="field-input h-11 border-slate-200 bg-white pl-11 text-sm shadow-xs focus:border-emerald-500 focus:ring-emerald-500/20"
                        placeholder="Tapez le nom d'un médicament (ex: Paracétamol, Amoxicilline)…" autofocus>
                </div>

                <div class="mt-2.5 flex items-center justify-between text-xs">
                    <p id="compteurResultats" class="text-slate-500 font-medium"></p>
                    <span class="text-slate-400">Tapez au moins 1 lettre</span>
                </div>
            </div>

            <div id="resultats" class="flex flex-col gap-2.5 overflow-y-auto pr-1">
                <div class="panel flex flex-col items-center justify-center p-12 text-center text-slate-400">
                    <div class="mb-3 grid size-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-700">Recherche rapide</p>
                    <p class="mt-1 text-xs text-slate-500">Commencez à saisir pour afficher les médicaments disponibles et leur stock.</p>
                </div>
            </div>
        </div>

        {{-- Panier / Ticket en cours --}}
        <div class="panel flex flex-col overflow-hidden">

            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="20" r="1.4" />
                            <circle cx="18" cy="20" r="1.4" />
                            <path d="M2.5 3h2l2.2 12.2a1.5 1.5 0 0 0 1.5 1.3h8.6a1.5 1.5 0 0 0 1.5-1.2L21 7H6" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Panier en cours</h2>
                        <p class="text-[11px] text-slate-500">Articles prêts à être encaissés</p>
                    </div>
                </div>
                <span id="compteurPanier"
                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                    0 article
                </span>
            </div>

            <div id="panier" class="flex-1 overflow-y-auto p-4 min-h-[220px]"></div>

            <div class="border-t border-slate-100 bg-slate-50/80 p-5">
                <div class="mb-4 flex items-baseline justify-between rounded-xl bg-white p-4 ring-1 ring-slate-200/70 shadow-xs">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block">Total à payer</span>
                        <span class="text-[11px] text-slate-400">TTC inclus</span>
                    </div>
                    <div class="text-right">
                        <span class="num text-2xl font-black text-emerald-700 tracking-tight">
                            <span id="total">0</span>
                        </span>
                        <span class="text-xs font-bold text-emerald-600 ml-1">FCFA</span>
                    </div>
                </div>

                <button type="button" onclick="passerAuPaiement()" class="btn-primary w-full h-12 text-sm font-bold shadow-md shadow-emerald-700/15">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2.5" />
                        <path d="M2 10h20" />
                    </svg>
                    Passer au paiement &rarr;
                </button>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
    <script>
        window.panierInitial = @json($panierInitial);
    </script>
    @vite('resources/js/ventes/vente.js')
@endpush
