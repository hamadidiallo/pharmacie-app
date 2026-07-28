@extends('layout')

@section('titre', 'Expirations proches — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Expiration proche</div>
    <span class="ml-3.5 text-[13px] text-faint">Produits périmant dans les 30 prochains jours</span>
    <a href="{{ route('medicaments.index', ['filtre' => 'expire']) }}" class="btn-ghost ml-auto">
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
                        <th>STOCK</th>
                        <th>EXPIRATION</th>
                        <th class="text-right">RESTE</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($expires as $medicament)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $medicament->nom }}</div>
                                <div class="truncate text-xs text-faint">{{ $medicament->description }}</div>
                            </td>
                            <td class="num">{{ $medicament->stock }}</td>
                            <td class="num text-danger-fg">{{ $medicament->date_expiration->format('d / m / Y') }}</td>
                            <td class="text-right">
                                <span class="pill-warn num">
                                    {{ (int) now()->startOfDay()->diffInDays($medicament->date_expiration, false) }} j
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-muted">
                                Aucun produit n'expire dans les 30 prochains jours.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

@endsection
