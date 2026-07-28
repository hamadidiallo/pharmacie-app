@extends('layout')

@section('titre', 'Nouvelle vente — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Nouvelle vente</div>
    <span class="ml-3.5 hidden text-[13px] text-faint sm:inline">Ticket en cours</span>
@endsection

@section('content')

    <div id="messageStock" class="mb-4 empty:hidden" role="status"></div>

    <div class="grid gap-[18px] lg:grid-cols-2">

        {{-- Recherche --}}
        <div class="flex min-h-0 flex-col">

            <label for="search" class="sr-only">Rechercher un médicament</label>
            <div class="relative mb-3.5">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 size-[18px] -translate-y-1/2"
                    viewBox="0 0 24 24" fill="none" stroke="#0F8A6B" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m21 21-4-4" />
                </svg>
                <input type="search" id="search" class="field-input border-line bg-white pl-11"
                    placeholder="Rechercher un médicament par nom…" autofocus>
            </div>

            <p id="compteurResultats" class="mb-2.5 text-xs text-muted"></p>

            <div id="resultats" class="flex flex-col gap-2.5">
                <p class="py-10 text-center text-[13px] text-muted">
                    Tapez les premières lettres d'un médicament.
                </p>
            </div>

        </div>

        {{-- Panier --}}
        <div class="flex flex-col overflow-hidden rounded-card border border-line bg-white">

            <div class="flex items-center gap-2.5 border-b border-hairline px-[17px] py-[15px]">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="#0F8A6B" stroke-width="1.9"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="20" r="1.4" />
                    <circle cx="18" cy="20" r="1.4" />
                    <path d="M2.5 3h2l2.2 12.2a1.5 1.5 0 0 0 1.5 1.3h8.6a1.5 1.5 0 0 0 1.5-1.2L21 7H6" />
                </svg>
                <span class="text-sm font-bold">Panier</span>
                <span id="compteurPanier"
                    class="num ml-auto rounded-pill bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600">
                    0 article
                </span>
            </div>

            <div id="panier" class="flex-1 overflow-auto p-2"></div>

            <div class="border-t border-hairline bg-[#F7FAF9] px-[17px] py-4">

                <div class="mb-3.5 flex items-baseline justify-between">
                    <span class="text-[15px] font-bold">Total</span>
                    <span class="num text-2xl font-bold text-brand-600">
                        <span id="total">0</span> FCFA
                    </span>
                </div>

                <button type="button" onclick="passerAuPaiement()" class="btn-primary btn-lg w-full">
                    <svg class="size-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2.5" />
                        <path d="M2 10h20" />
                    </svg>
                    Passer au paiement
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
