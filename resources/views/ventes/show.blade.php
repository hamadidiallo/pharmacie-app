@extends('layout')

@section('titre', 'Ticket TCK-' . $vente->id . ' — GESTA PHARM')

@section('topbar')
    <a href="{{ route('ventes.index') }}" class="btn-icon" aria-label="Retour aux ventes">
        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Ticket TCK-{{ $vente->id }}</div>
    <div class="ml-auto flex gap-2">
        <button type="button" onclick="window.print()" class="btn-primary">Imprimer</button>
        <a href="{{ route('ventes.pdf', $vente) }}" class="btn-ghost">PDF</a>
    </div>
@endsection

@section('content')

    <div class="flex flex-col items-center">

        {{-- Le reçu thermique 80 mm, bords dentelés compris. --}}
        <div class="ticket-edge no-print w-[80mm] max-w-full" aria-hidden="true"></div>

        <article id="ticket"
            class="ticket bg-white px-6 py-5 font-mono text-[12px] leading-relaxed text-ink shadow-card">

            <header class="text-center">
                <h1 class="text-sm font-bold tracking-widest">PHARMACIE GESTAPHARM</h1>
                <p>KATI, MALI</p>
                <p>Tel : +223 78 14 43 59</p>
            </header>

            <div class="my-3 border-t border-dashed border-line"></div>

            <dl class="space-y-0.5">
                <div class="flex justify-between">
                    <dt>Ticket</dt>
                    <dd class="font-bold">TCK-{{ $vente->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Date</dt>
                    <dd>{{ $vente->date_vente->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Heure</dt>
                    <dd>{{ $vente->created_at->format('H:i:s') }}</dd>
                </div>
            </dl>

            <div class="my-3 border-t border-dashed border-line"></div>

            <table class="w-full tabular-nums">

                <thead>
                    <tr class="border-b border-dashed border-line text-left">
                        <th class="pb-1 font-normal">Produit</th>
                        <th class="pb-1 text-right font-normal">Qté</th>
                        <th class="pb-1 text-right font-normal">P.U.</th>
                        <th class="pb-1 text-right font-normal">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($vente->medicaments as $medicament)
                        <tr>
                            <td class="py-1 pr-2">{{ $medicament->nom }}</td>
                            <td class="py-1 text-right">{{ $medicament->pivot->quantite }}</td>
                            <td class="py-1 text-right">
                                {{ number_format($medicament->pivot->prix, 0, ',', ' ') }}
                            </td>
                            <td class="py-1 text-right">
                                {{ number_format($medicament->pivot->sous_total, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <div class="my-3 border-t border-dashed border-line"></div>

            <p class="flex items-baseline justify-between text-sm font-bold">
                <span>TOTAL</span>
                <span>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</span>
            </p>

            <dl class="mt-2 space-y-0.5">
                <div class="flex justify-between">
                    <dt>Paiement</dt>
                    <dd>{{ $vente->libelleModePaiement() }}</dd>
                </div>
                @if ($vente->montant_recu !== null)
                    <div class="flex justify-between">
                        <dt>Reçu</dt>
                        <dd>{{ number_format($vente->montant_recu, 0, ',', ' ') }} FCFA</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Rendu</dt>
                        <dd>{{ number_format($vente->monnaie_rendue, 0, ',', ' ') }} FCFA</dd>
                    </div>
                @endif
            </dl>

            <div class="my-3 border-t border-dashed border-line"></div>

            <p class="text-center">Merci pour votre confiance</p>

        </article>

        <div class="ticket-edge ticket-edge-bottom no-print w-[80mm] max-w-full" aria-hidden="true"></div>

        <div class="no-print mt-6 flex flex-wrap justify-center gap-2">
            <a href="{{ route('ventes.create') }}" class="btn-primary">Nouvelle vente</a>
            <a href="{{ route('ventes.index') }}" class="btn-ghost">Retour à l'historique</a>
        </div>

    </div>

@endsection
