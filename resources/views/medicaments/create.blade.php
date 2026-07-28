@extends('layout')

@section('titre', 'Ajouter un produit — GESTA PHARM')

@section('content')

    <div class="mx-auto max-w-lg">

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight">Ajouter un produit</h1>
            <p class="mt-1 text-sm text-ink-soft">
                Si un produit identique existe déjà — même nom, prix, description et date
                d'expiration — les quantités sont cumulées au lot existant.
            </p>
        </div>

        <div class="card-officine p-6">

            <form action="{{ route('medicament.store') }}" method="post">

                @csrf

                <x-component.input name="nom" value="{{ old('nom') }}" label="Nom du médicament" />

                <div class="grid gap-x-4 sm:grid-cols-2">
                    <x-component.input name="prix" type="number" value="{{ old('prix') }}"
                        label="Prix unitaire (FCFA)" />
                    <x-component.input name="stock" type="number" value="{{ old('stock') }}" label="Quantité" />
                </div>

                <x-component.input name="date_expiration" type="date" value="{{ old('date_expiration') }}"
                    label="Date d'expiration" />

                <x-component.input name="description" type="textarea" value="{{ old('description') }}"
                    label="Description" />

                <div class="flex justify-end gap-2">
                    <a href="{{ route('medicaments.index') }}" class="btn-ghost">Annuler</a>
                    <button type="submit" class="btn-primary">Ajouter au stock</button>
                </div>

            </form>

        </div>

    </div>

@endsection
