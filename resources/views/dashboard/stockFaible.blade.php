@extends('layout')

@section('titre', 'Stock faible — GESTA PHARM')

@section('content')

    <section class="card-officine overflow-hidden">

        <div class="card-head">
            <div>
                <h1 class="card-title">Stock faible</h1>
                <p class="mt-0.5 text-sm text-ink-soft">Il reste 5 unités ou moins.</p>
            </div>
            <span class="badge-faible">{{ $stockFaible->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-officine">

                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Stock restant</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($stockFaible as $medicament)
                        <tr>
                            <td class="font-medium">{{ $medicament->nom }}</td>
                            <td class="num"><span class="badge-faible">{{ $medicament->stock }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-10 text-center text-ink-soft">
                                Aucun produit en stock faible.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

@endsection
