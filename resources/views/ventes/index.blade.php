@extends('layout')

@section('titre', 'Historique des ventes — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Historique des ventes</div>

    <div class="ml-auto flex items-center gap-2">
        <label for="searchTicket" class="sr-only">Rechercher un ticket</label>
        <input type="search" id="searchTicket" class="field-input h-9 w-36 sm:w-52" placeholder="N° de ticket">
        <a href="{{ route('ventes.create') }}" class="btn-primary">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="hidden sm:inline">Nouvelle vente</span>
        </a>
    </div>
@endsection

@section('content')

    <p id="noResult" class="notice-warn mb-4 hidden">Aucun ticket ne correspond à cette recherche.</p>

    <div class="panel overflow-hidden">

        <div class="overflow-x-auto">
            <table class="table-data">

                <thead>
                    <tr>
                        <th>TICKET</th>
                        <th>TOTAL</th>
                        <th>PAIEMENT</th>
                        <th>DATE</th>
                        <th>HEURE</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($ventes as $vente)
                        <tr class="vente-row" data-ticket="TCK-{{ $vente->id }}">

                            <td class="num font-semibold">TCK-{{ $vente->id }}</td>

                            <td class="num whitespace-nowrap font-semibold">
                                {{ number_format($vente->total, 0, ',', ' ') }} FCFA
                            </td>

                            <td>
                                <span class="pill bg-surface text-slate-ink">{{ $vente->libelleModePaiement() }}</span>
                            </td>

                            <td class="num whitespace-nowrap text-slate-ink">
                                {{ $vente->date_vente->format('d/m/Y') }}
                            </td>

                            <td class="num whitespace-nowrap text-slate-ink">
                                {{ $vente->created_at->format('H:i:s') }}
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">

                                    <a href="{{ route('ventes.show', $vente) }}" class="btn-ghost btn h-8 px-3 text-xs">
                                        Voir le ticket
                                    </a>

                                    <button type="button" data-dialog="supprimer-vente-{{ $vente->id }}"
                                        class="btn-icon-danger" aria-label="Supprimer le ticket TCK-{{ $vente->id }}">
                                        <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="#C1352B"
                                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-muted">
                                Aucune vente enregistrée.
                                <a href="{{ route('ventes.create') }}" class="font-semibold text-brand-500">
                                    Encaisser la première vente
                                </a>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-hairline px-[18px] py-3.5">
            <span class="text-xs text-faint">
                {{ $ventes->firstItem() ?? 0 }} – {{ $ventes->lastItem() ?? 0 }}
                sur {{ number_format($ventes->total(), 0, ',', ' ') }}
            </span>
            {{ $ventes->onEachSide(1)->links() }}
        </div>

    </div>

    @foreach ($ventes as $vente)
        <dialog id="supprimer-vente-{{ $vente->id }}" class="dialog-panel">

            <div class="panel-head">
                <h2 class="panel-title">Supprimer le ticket TCK-{{ $vente->id }} ?</h2>
            </div>

            <div class="p-5 text-[13px] text-muted">
                Les quantités vendues seront remises en stock. Cette action est définitive.
            </div>

            <div class="flex justify-end gap-2 border-t border-hairline px-5 py-4">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <form action="{{ route('ventes.destroy', $vente) }}" method="POST">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn bg-danger-fg text-white hover:bg-danger-text">
                        Supprimer et restituer le stock
                    </button>
                </form>
            </div>

        </dialog>
    @endforeach

@endsection

@push('scripts')
    <script>
        document.getElementById('searchTicket').addEventListener('input', function() {

            const recherche = this.value.trim().toLowerCase();
            const lignes = document.querySelectorAll('.vente-row');

            let trouve = 0;

            lignes.forEach(ligne => {
                const correspond = ligne.dataset.ticket.toLowerCase().includes(recherche);
                ligne.hidden = !correspond;
                if (correspond) trouve++;
            });

            document.getElementById('noResult').classList.toggle('hidden', trouve > 0);
        });
    </script>
@endpush
