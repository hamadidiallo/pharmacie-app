@extends('layout')

@section('titre', 'Ventes — GESTA PHARM')

@section('content')

    <section class="card-officine overflow-hidden">

        <div class="card-head">
            <div>
                <h1 class="card-title">Historique des ventes</h1>
                <p class="mt-0.5 text-sm text-ink-soft">{{ $ventes->total() }} tickets enregistrés.</p>
            </div>
            <div class="flex items-center gap-2">
                <label for="searchTicket" class="sr-only">Rechercher un ticket</label>
                <input type="search" id="searchTicket" class="field-input w-52" placeholder="N° de ticket">
                <a href="{{ route('ventes.create') }}" class="btn-primary btn-sm">Nouvelle vente</a>
            </div>
        </div>

        <p id="noResult" class="notice-warn m-5 hidden">Aucun ticket ne correspond à cette recherche.</p>

        <div class="overflow-x-auto">
            <table class="table-officine">

                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Date</th>
                        <th class="text-right">Heure</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($ventes as $vente)
                        <tr class="vente-row" data-ticket="TCK-{{ $vente->id }}">

                            <td class="figure font-medium">TCK-{{ $vente->id }}</td>

                            <td class="num whitespace-nowrap font-semibold">
                                {{ number_format($vente->total, 0, ',', ' ') }}
                                <span class="text-xs font-normal text-ink-soft">FCFA</span>
                            </td>

                            <td class="num whitespace-nowrap">{{ $vente->date_vente->format('d/m/Y') }}</td>

                            <td class="num whitespace-nowrap">{{ $vente->created_at->format('H:i:s') }}</td>

                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('ventes.show', $vente) }}" class="btn-ghost btn-sm">
                                        Voir le ticket
                                    </a>
                                    <button type="button" data-dialog="supprimer-vente-{{ $vente->id }}"
                                        class="btn-ghost btn-sm text-rouge-700 hover:border-rouge-100 hover:bg-rouge-50">
                                        Supprimer
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-ink-soft">
                                Aucune vente enregistrée.
                                <a href="{{ route('ventes.create') }}"
                                    class="font-semibold text-officine-600 underline underline-offset-4">
                                    Encaisser la première vente
                                </a>.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        @if ($ventes->hasPages())
            <div class="border-t border-rule px-5 py-4">
                {{ $ventes->links() }}
            </div>
        @endif

    </section>

    @foreach ($ventes as $vente)
        <dialog id="supprimer-vente-{{ $vente->id }}" class="dialog-officine">

            <div class="card-head">
                <h2 class="card-title">Supprimer le ticket TCK-{{ $vente->id }} ?</h2>
            </div>

            <div class="p-5 text-sm text-ink-soft">
                Les quantités vendues seront remises en stock. Cette action est définitive.
            </div>

            <div class="flex justify-end gap-2 border-t border-rule px-5 py-4">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <form action="{{ route('ventes.destroy', $vente) }}" method="POST">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn-danger">Supprimer et restituer le stock</button>
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
