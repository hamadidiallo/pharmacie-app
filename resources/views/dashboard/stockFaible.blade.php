@extends('layout')

@section('titre', 'Stock faible — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Stock faible</div>
    <span class="ml-3.5 text-[13px] text-faint">Il reste 5 unités ou moins</span>
    <a href="{{ route('medicaments.index', ['filtre' => 'faible']) }}" class="btn-ghost ml-auto">
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
                        <th class="text-right">STOCK RESTANT</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($stockFaible as $medicament)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $medicament->nom }}</div>
                                <div class="truncate text-xs text-faint">{{ $medicament->description }}</div>
                            </td>
                            <td class="num text-slate-ink">{{ $medicament->date_expiration->format('m / Y') }}</td>
                            <td class="text-right">
                                <span class="pill-warn num">{{ $medicament->stock }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-12 text-center text-muted">
                                Aucun produit en stock faible.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

@endsection
