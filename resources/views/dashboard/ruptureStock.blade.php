@extends('layout')

@section('titre', 'Ruptures de stock — GESTA PHARM')

@section('content')

    <section class="card-officine overflow-hidden">

        <div class="card-head">
            <div>
                <h1 class="card-title">En rupture de stock</h1>
                <p class="mt-0.5 text-sm text-ink-soft">Produits à commander en priorité.</p>
            </div>
            <span class="badge-rupture">{{ $ruptureStock->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-officine">

                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Stock</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($ruptureStock as $medicament)
                        <tr>
                            <td class="font-medium">{{ $medicament->nom }}</td>
                            <td class="num"><span class="badge-rupture">0</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-10 text-center text-ink-soft">
                                Aucune rupture. Tous les produits sont en stock.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

@endsection
