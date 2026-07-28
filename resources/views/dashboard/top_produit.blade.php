@extends('layout')

@section('titre', 'Top 5 des produits — GESTA PHARM')

@section('content')

    <section class="card-officine overflow-hidden">

        <div class="card-head">
            <div>
                <h1 class="card-title">Top 5 des produits</h1>
                <p class="mt-0.5 text-sm text-ink-soft">Classés par quantité vendue.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-officine">

                <thead>
                    <tr>
                        <th class="w-10">Rang</th>
                        <th>Produit</th>
                        <th class="text-right">Quantité vendue</th>
                        <th class="text-right">Montant généré</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($topMedicaments as $medicament)
                        <tr>
                            <td class="num text-ink-soft">{{ $loop->iteration }}</td>
                            <td class="font-medium">{{ $medicament->nom }}</td>
                            <td class="num">{{ number_format($medicament->total_quantite, 0, ',', ' ') }}</td>
                            <td class="num font-semibold">
                                {{ number_format($medicament->total_montant, 0, ',', ' ') }}
                                <span class="text-xs font-normal text-ink-soft">FCFA</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-ink-soft">
                                Aucune vente enregistrée pour l'instant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

@endsection
