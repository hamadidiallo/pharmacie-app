@extends('layout')

@section('titre', 'Ajouter un produit — GESTA PHARM')

@section('topbar')
    <a href="{{ route('medicaments.index') }}" class="btn-icon" aria-label="Retour aux médicaments">
        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Ajouter un médicament</div>
@endsection

@section('content')

    <div class="mx-auto max-w-xl">

        <div class="panel p-6">

            <p class="mb-6 text-[13px] text-muted">
                Si un produit identique existe déjà — même nom, prix, description et date
                d'expiration — les quantités sont cumulées au lot existant plutôt que dupliquées.
            </p>

            <form action="{{ route('medicament.store') }}" method="post">

                @csrf

                <x-component.input name="nom" value="{{ old('nom') }}" label="Nom du médicament"
                    placeholder="Paracétamol 500mg" />

                <div class="grid gap-x-4 sm:grid-cols-2">
                    <x-component.input name="prix" type="number" value="{{ old('prix') }}"
                        label="Prix unitaire (FCFA)" placeholder="250" />
                    <x-component.input name="stock" type="number" value="{{ old('stock') }}" label="Quantité"
                        placeholder="100" />
                </div>

                <x-component.input name="date_expiration" type="date" value="{{ old('date_expiration') }}"
                    label="Date d'expiration" />

                <x-component.input name="description" type="textarea" value="{{ old('description') }}"
                    label="Description" placeholder="Antalgique · boîte de 20" />

                <div class="mt-2 flex justify-end gap-2">
                    <a href="{{ route('medicaments.index') }}" class="btn-ghost">Annuler</a>
                    <button type="submit" class="btn-primary">Ajouter au stock</button>
                </div>

            </form>

        </div>

    </div>

@endsection
