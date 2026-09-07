@extends('layout')

@section('titre', 'Historique des ventes — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="M7 15h0M2 9.5h20"/>
            </svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Historique des ventes</h1>
            <p class="text-xs text-slate-500">Journal des encaissements et tickets émis</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-3">
        <div class="relative">
            <label for="searchTicket" class="sr-only">Rechercher un ticket</label>
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="search" id="searchTicket" class="field-input h-9 w-40 pl-9 sm:w-60" placeholder="N° de ticket (ex: TCK-12)">
        </div>

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
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-5 py-3.5">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Registre des transactions
                </span>
            </div>
            <span class="text-xs text-slate-500">
                Page {{ $ventes->currentPage() }} sur {{ $ventes->lastPage() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-data">
                <thead>
                    <tr>
                        <th>TICKET</th>
                        <th>MONTANT TOTAL</th>
                        <th>MODE DE PAIEMENT</th>
                        <th>DATE</th>
                        <th>HEURE</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($ventes as $vente)
                        <tr class="vente-row group" data-ticket="TCK-{{ $vente->id }}">
                            <td class="whitespace-nowrap font-medium">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-800 ring-1 ring-slate-200/80 group-hover:bg-emerald-50 group-hover:text-emerald-800 group-hover:ring-emerald-200/60 transition-colors">
                                    <svg class="size-3 text-slate-400 group-hover:text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4l-4 4-4-4-4 4z"/>
                                    </svg>
                                    TCK-{{ $vente->id }}
                                </span>
                            </td>

                            <td class="num whitespace-nowrap text-sm font-bold text-slate-900">
                                {{ number_format($vente->total, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span>
                            </td>

                            <td>
                                @php
                                    $mode = $vente->mode_paiement;
                                    $modeClass = match($mode) {
                                        'especes' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                        'mobile_money' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        'carte' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $modeClass }}">
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                    {{ $vente->libelleModePaiement() }}
                                </span>
                            </td>

                            <td class="num whitespace-nowrap text-sm text-slate-600">
                                {{ $vente->date_vente->format('d/m/Y') }}
                            </td>

                            <td class="num whitespace-nowrap text-xs text-slate-500">
                                {{ $vente->created_at->format('H:i:s') }}
                            </td>

                            <td>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('ventes.show', $vente) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-700 transition-colors">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        Ticket
                                    </a>

                                    @can('supprimer-vente')
                                        <button type="button" data-dialog="supprimer-vente-{{ $vente->id }}"
                                            class="grid size-8 place-items-center rounded-lg border border-transparent text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                                            aria-label="Supprimer le ticket TCK-{{ $vente->id }}"
                                            title="Annuler cette vente">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="mx-auto mb-3 grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                                        <path d="M7 15h0M2 9.5h20"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-slate-800">Aucune vente enregistrée</div>
                                <p class="text-xs text-slate-500 mb-3">Enregistrez votre première transaction au comptoir.</p>
                                <a href="{{ route('ventes.create') }}" class="btn-primary inline-flex">
                                    Encaisser une vente
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-5 py-3.5">
            <span class="text-xs text-slate-500">
                Affichage de <span class="font-semibold text-slate-700">{{ $ventes->firstItem() ?? 0 }}</span> à <span class="font-semibold text-slate-700">{{ $ventes->lastItem() ?? 0 }}</span>
                sur <span class="font-semibold text-slate-700">{{ number_format($ventes->total(), 0, ',', ' ') }}</span> tickets
            </span>
            {{ $ventes->onEachSide(1)->links() }}
        </div>
    </div>

    @can('supprimer-vente')
        @foreach ($ventes as $vente)
            <dialog id="supprimer-vente-{{ $vente->id }}" class="dialog-panel">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-600 ring-1 ring-rose-500/20">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Supprimer le ticket TCK-{{ $vente->id }} ?</h2>
                            <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                                Cette opération annulera la vente d'un montant de <span class="font-semibold text-slate-800">{{ number_format($vente->total, 0, ',', ' ') }} FCFA</span>.
                                Les quantités vendues seront automatiquement réintégrées dans les stocks.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                    <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                    <form action="{{ route('ventes.destroy', $vente) }}" method="POST">
                        @csrf
                        @method('delete')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-500 transition-colors">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Confirmer la suppression
                        </button>
                    </form>
                </div>
            </dialog>
        @endforeach
    @endcan

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
