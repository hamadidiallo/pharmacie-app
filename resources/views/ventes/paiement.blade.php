@extends('layout')

@section('titre', 'Paiement — GESTA PHARM')

@section('topbar')
    <a href="{{ route('ventes.create') }}" class="btn-icon" aria-label="Retour au panier">
        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Paiement</div>
    <span class="ml-3.5 hidden text-[13px] text-faint sm:inline">
        {{ count($lignes) }} {{ count($lignes) > 1 ? 'articles' : 'article' }}
    </span>
@endsection

@section('content')

    @php
        $icones = [
            'especes' =>
                '<rect x="2" y="6" width="20" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
            'mobile_money' => '<rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/>',
            'carte' => '<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>',
        ];
        // suggestions de montant reçu : le compte juste, puis les coupures au-dessus
        $suggestions = collect([$total, ceil($total / 500) * 500, ceil($total / 1000) * 1000, ceil($total / 5000) * 5000])
            ->unique()
            ->sort()
            ->take(4)
            ->values();
    @endphp

    <form method="post" action="{{ route('ventes.store') }}"
        class="grid gap-[18px] lg:grid-cols-[1fr_1.1fr]" data-total="{{ $total }}">

        @csrf

        {{-- Récapitulatif --}}
        <div class="flex flex-col rounded-card border border-line bg-white p-5">

            <div class="mb-4 text-sm font-bold">Récapitulatif</div>

            @foreach ($lignes as $ligne)
                <div class="flex items-center gap-3 border-b border-[#F1F5F3] py-2.5 last:border-0">
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold">{{ $ligne['medicament']->nom }}</div>
                        <div class="num text-xs text-faint">
                            {{ $ligne['quantite'] }} × {{ number_format($ligne['prix'], 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="num text-sm font-semibold">
                        {{ number_format($ligne['sous_total'], 0, ',', ' ') }}
                    </div>
                </div>
            @endforeach

            <div class="mt-6 rounded-xl bg-brand-900 p-5 text-white lg:mt-auto">
                <div class="mb-3 flex justify-between text-[13px] text-[#A9C7BD]">
                    <span>Sous-total</span>
                    <span class="num">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex items-baseline justify-between border-t border-[#23483D] pt-3">
                    <span class="text-sm font-semibold">Montant à payer</span>
                    <span class="num text-[26px] font-bold text-mint">
                        {{ number_format($total, 0, ',', ' ') }}
                    </span>
                </div>
            </div>

        </div>

        {{-- Encaissement --}}
        <div class="flex flex-col rounded-card border border-line bg-white p-5">

            <fieldset class="mb-[22px]">

                <legend class="mb-3.5 text-sm font-bold">Mode de paiement</legend>

                @error('mode_paiement')
                    <p class="field-error mb-3">{{ $message }}</p>
                @enderror

                <div class="grid grid-cols-3 gap-2.5">
                    @foreach ($modes as $cle => $libelle)
                        <label class="flex cursor-pointer flex-col items-center gap-2.5 rounded-xl border px-2.5 py-4
                                      transition-colors has-checked:border-[1.5px] has-checked:border-brand-500
                                      has-checked:bg-brand-50 border-line hover:bg-surface">
                            <input type="radio" name="mode_paiement" value="{{ $cle }}" class="sr-only"
                                data-mode @checked(old('mode_paiement', 'especes') === $cle)>
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                {!! $icones[$cle] !!}
                            </svg>
                            <span class="text-center text-[13px] font-semibold">{{ $libelle }}</span>
                        </label>
                    @endforeach
                </div>

            </fieldset>

            {{-- Espèces : montant reçu et monnaie --}}
            <div data-bloc-especes>

                <label for="montant_recu" class="field-label">Montant reçu</label>

                <div class="mb-3 flex h-14 items-center justify-between rounded-xl border-[1.5px] border-brand-500 bg-surface px-[18px]">
                    <input type="number" id="montant_recu" name="montant_recu" min="0" step="1"
                        value="{{ old('montant_recu', (int) $total) }}"
                        class="num w-full border-0 bg-transparent text-2xl font-semibold text-ink focus:outline-none">
                    <span class="ml-3 shrink-0 text-[13px] text-faint">FCFA</span>
                </div>

                @error('montant_recu')
                    <p class="field-error mb-3">{{ $message }}</p>
                @enderror

                <div class="mb-5 grid grid-cols-4 gap-2.5">
                    @foreach ($suggestions as $montant)
                        <button type="button" data-suggestion="{{ (int) $montant }}"
                            class="num rounded-lg border border-[#E7EEEB] bg-surface py-2.5 text-center text-sm font-semibold text-slate-ink transition-colors hover:bg-canvas">
                            {{ number_format($montant, 0, ',', ' ') }}
                        </button>
                    @endforeach
                </div>

                <div class="mb-5 flex items-center justify-between rounded-xl bg-brand-50 px-[18px] py-4">
                    <span class="text-sm font-semibold text-brand-600">Monnaie à rendre</span>
                    <span id="monnaie" class="num text-2xl font-bold text-brand-600">0 FCFA</span>
                </div>

            </div>

            <button type="submit" class="btn-primary mt-auto h-[54px] w-full rounded-xl text-base font-bold">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
                Valider le paiement
            </button>

        </div>

    </form>

@endsection

@push('scripts')
    <script>
        const formulaire = document.querySelector('form[data-total]');
        const total = Number(formulaire.dataset.total);
        const champMontant = document.getElementById('montant_recu');
        const affichageMonnaie = document.getElementById('monnaie');
        const blocEspeces = document.querySelector('[data-bloc-especes]');
        const boutonValider = formulaire.querySelector('button[type="submit"]');

        const formatFcfa = (montant) => new Intl.NumberFormat('fr-FR').format(montant);

        function rafraichirMonnaie() {

            const especes = formulaire.querySelector('[data-mode]:checked').value === 'especes';

            blocEspeces.hidden = !especes;

            if (!especes) {
                boutonValider.disabled = false;
                return;
            }

            const recu = Number(champMontant.value || 0);
            const monnaie = recu - total;

            affichageMonnaie.textContent = formatFcfa(Math.max(0, monnaie)) + ' FCFA';
            affichageMonnaie.classList.toggle('text-danger-fg', monnaie < 0);
            affichageMonnaie.classList.toggle('text-brand-600', monnaie >= 0);

            // on ne valide pas un encaissement incomplet
            boutonValider.disabled = monnaie < 0;
        }

        formulaire.querySelectorAll('[data-mode]').forEach(
            radio => radio.addEventListener('change', rafraichirMonnaie)
        );

        champMontant.addEventListener('input', rafraichirMonnaie);

        formulaire.querySelectorAll('[data-suggestion]').forEach(bouton => {
            bouton.addEventListener('click', () => {
                champMontant.value = bouton.dataset.suggestion;
                rafraichirMonnaie();
            });
        });

        rafraichirMonnaie();
    </script>
@endpush
