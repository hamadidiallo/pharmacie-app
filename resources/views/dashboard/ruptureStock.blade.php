@extends('layout')

@section('titre', 'Ruptures de stock — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Rupture de stock</div>
    <span class="ml-3.5 text-[13px] text-faint">Produits à commander en priorité</span>
    <a href="{{ route('medicaments.index', ['filtre' => 'rupture']) }}" class="btn-ghost ml-auto">
        Voir dans les médicaments
    </a>
@endsection

@section('content')

    <div class="panel overflow-hidden">

        <div class="overflow-x-auto">
            <table class="table-data">

                <thead>
                    <tr>
                        <th>MÉDICAMENT</th>
                        <th>EXPIRATION</th>
                        <th class="text-right">STATUT</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($ruptureStock as $medicament)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $medicament->nom }}</div>
                                <div class="truncate text-xs text-faint">{{ $medicament->description }}</div>
                            </td>
                            <td class="num text-slate-ink">{{ $medicament->date_expiration->format('m / Y') }}</td>
                            <td class="text-right"><span class="pill-danger">Rupture</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-12 text-center text-muted">
                                Aucune rupture. Tous les produits sont en stock.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

@endsection
