@extends('layout')

@section('titre', 'Ticket TCK-' . $vente->id . ' — GESTA PHARM')

@section('content')

    <div class="flex flex-col items-center">

        {{-- Le reçu thermique 80 mm, bords dentelés compris. --}}
        <div class="ticket-edge w-[80mm] max-w-full no-print" aria-hidden="true"></div>

        <article id="ticket" class="ticket bg-surface px-6 py-5 font-mono text-[12px] leading-relaxed text-ink">

            <header class="text-center">
                <h1 class="text-sm font-bold tracking-widest">PHARMACIE GESTAPHARM</h1>
                <p>KATI, MALI</p>
                <p>Tel : +223 78 14 43 59</p>
            </header>

            <div class="rule-dashed my-3"></div>

            <dl class="space-y-0.5">
                <div class="flex justify-between"><dt>Ticket</dt>
                    <dd class="font-bold">TCK-{{ $vente->id }}</dd>
                </div>
                <div class="flex justify-between"><dt>Date</dt>
                    <dd>{{ $vente->date_vente->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between"><dt>Heure</dt>
                    <dd>{{ $vente->created_at->format('H:i:s') }}</dd>
                </div>
            </dl>

            <div class="rule-dashed my-3"></div>

            <table class="w-full tabular-nums">

                <thead>
                    <tr class="border-b border-dashed border-rule text-left">
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
                            <td class="py-1 text-right">{{ number_format($medicament->pivot->prix, 0, ',', ' ') }}</td>
                            <td class="py-1 text-right">
                                {{ number_format($medicament->pivot->sous_total, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <div class="rule-dashed my-3"></div>

            <p class="flex items-baseline justify-between text-sm font-bold">
                <span>TOTAL</span>
                <span>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</span>
            </p>

            <div class="rule-dashed my-3"></div>

            <p class="text-center">Merci pour votre confiance</p>

        </article>

        <div class="ticket-edge ticket-edge-bottom w-[80mm] max-w-full no-print" aria-hidden="true"></div>

        <div class="no-print mt-6 flex flex-wrap justify-center gap-2">
            <button type="button" onclick="window.print()" class="btn-primary">Imprimer le ticket</button>
            <a href="{{ route('ventes.pdf', $vente) }}" class="btn-ghost">Télécharger le PDF</a>
            <a href="{{ route('ventes.index') }}" class="btn-ghost">Retour aux ventes</a>
        </div>

    </div>

@endsection
