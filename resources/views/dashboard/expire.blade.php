@extends('layout')

@section('titre', 'Expirations proches — GESTA PHARM')

@section('content')

    <section class="card-officine overflow-hidden">

        <div class="card-head">
            <div>
                <h1 class="card-title">Expiration proche</h1>
                <p class="mt-0.5 text-sm text-ink-soft">Produits périmant dans les 30 prochains jours.</p>
            </div>
            <span class="badge-faible">{{ $expires->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-officine">

                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Date d'expiration</th>
                        <th class="text-right">Reste</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($expires as $medicament)
                        <tr>
                            <td class="font-medium">{{ $medicament->nom }}</td>
                            <td class="num">{{ $medicament->date_expiration->format('d/m/Y') }}</td>
                            <td class="num">
                                <span class="badge-faible">
                                    {{ (int) now()->startOfDay()->diffInDays($medicament->date_expiration, false) }} j
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-ink-soft">
                                Aucun produit n'expire dans les 30 prochains jours.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

@endsection
