@extends('layout')

@section('titre', 'Nouveau Médicament / Arrivage — GESTA PHARM')

@section('topbar')
    <a href="{{ route('medicaments.index') }}" class="btn-icon" aria-label="Retour aux médicaments">
        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Fiche Médicament & Entrée en Stock</div>
@endsection

@section('content')

    <div class="mx-auto max-w-3xl space-y-6">

        <div class="panel p-6 sm:p-8">
            <div class="mb-6 border-b border-border pb-4">
                <h1 class="text-xl font-bold text-ink">Nouveau Médicament ou Réapprovisionnement</h1>
                <p class="mt-1 text-sm text-muted">
                    Renseignez les données cliniques, le cadre réglementaire de délivrance et les détails de traçabilité du lot initial.
                </p>
            </div>

            <form action="{{ route('medicaments.store') }}" method="post" class="space-y-6">
                @csrf

                <!-- Section 1 : Identification Clinique & Commerciale -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-faint mb-3 flex items-center gap-1.5">
                        <svg class="size-4 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M9 12h6" /><path d="M12 9v6" />
                        </svg>
                        1. Identification Clinique & Commerciale
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-component.input name="nom" value="{{ old('nom') }}" label="Nom commercial ou Spécialité *"
                                placeholder="Ex: DOLIPRANE, AMOXICILLINE BIOGARAN..." />
                        </div>

                        <div>
                            <x-component.input name="dci" value="{{ old('dci') }}" label="DCI (Principe actif)"
                                placeholder="Ex: Paracétamol, Amoxicilline..."
                                hint="Dénomination Commune Internationale — permet la substitution générique" />
                        </div>

                        <div>
                            <x-component.input name="code_barre" value="{{ old('code_barre') }}" label="Code-barres (EAN-13 / CIP)"
                                placeholder="Ex: 3400936192131"
                                hint="Peut être scanné directement avec la douchette" />
                        </div>

                        <div>
                            <x-component.input name="forme" value="{{ old('forme') }}" label="Forme galénique"
                                placeholder="Ex: Comprimé, Gélule, Sirop, Injectable..." />
                        </div>

                        <div>
                            <x-component.input name="dosage" value="{{ old('dosage') }}" label="Dosage"
                                placeholder="Ex: 500mg, 1g, 250mg/5ml..." />
                        </div>
                    </div>
                </div>

                <!-- Section 2 : Cadre Réglementaire & Sécurité -->
                <div class="pt-4 border-t border-border">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-faint mb-3 flex items-center gap-1.5">
                        <svg class="size-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                        </svg>
                        2. Cadre Réglementaire & Délivrance
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="tableau" class="field-label">Tableau réglementaire</label>
                            <select id="tableau" name="tableau" class="field-input">
                                @foreach ($tableaux as $tab)
                                    <option value="{{ $tab->value }}" @selected(old('tableau', 'non_liste') === $tab->value)>
                                        {{ $tab->libelle() }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-faint">Définit les règles de détention et d'inscription à l'ordonnancier.</p>
                            @error('tableau')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="field-label">Condition de délivrance</label>
                            <div class="mt-2 flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="ordonnance_requise" value="1" class="sr-only peer"
                                        @checked(old('ordonnance_requise', false))>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                                    <span class="ml-3 text-sm font-medium text-ink">Ordonnance médicale obligatoire</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-faint">Une alerte sera affichée au caissier lors de la vente au comptoir.</p>
                            @error('ordonnance_requise')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3 : Traçabilité du Lot & Stock Initial -->
                <div class="pt-4 border-t border-border">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-faint mb-3 flex items-center gap-1.5">
                        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                            <path d="m3.3 7 8.7 5 8.7-5" /><path d="M12 22V12" />
                        </svg>
                        3. Arrivage & Traçabilité du Lot Initial (FEFO)
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-component.input name="numero_lot" value="{{ old('numero_lot') }}" label="Numéro de lot"
                                placeholder="Ex: LOT-2026-X89"
                                hint="Optionnel (généré auto si laissé vide)" />
                        </div>

                        <div>
                            <x-component.input name="date_expiration" type="date" value="{{ old('date_expiration') }}"
                                label="Date d'expiration *"
                                hint="Détermine la priorité de sortie (FEFO)" />
                        </div>

                        <div>
                            <x-component.input name="date_fabrication" type="date" value="{{ old('date_fabrication') }}"
                                label="Date de fabrication"
                                hint="Optionnel (mention laboratoire)" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3 mt-2">
                        <div>
                            <x-component.input name="stock" type="number" value="{{ old('stock', 50) }}"
                                label="Quantité reçue (Boîtes) *" placeholder="50" />
                        </div>

                        <div>
                            <x-component.input name="prix" type="number" value="{{ old('prix') }}"
                                label="Prix de vente TTC (FCFA) *" placeholder="1500" />
                        </div>

                        <div>
                            <x-component.input name="stock_securite" type="number" value="{{ old('stock_securite', 10) }}"
                                label="Stock de sécurité (Alerte)" placeholder="10"
                                hint="Seuil pour déclencher le réassort" />
                        </div>
                    </div>
                </div>

                <!-- Section 4 : Informations complémentaires -->
                <div class="pt-4 border-t border-border">
                    <x-component.input name="description" type="textarea" value="{{ old('description') }}"
                        label="Description & Conseils de conservation"
                        placeholder="Ex: Conserver à l'abri de la lumière et de l'humidité. Température < 25°C." />
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('medicaments.index') }}" class="btn-ghost">Annuler</a>
                    <button type="submit" class="btn-primary">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14" /><path d="M12 5v14" />
                        </svg>
                        Enregistrer et Allouer le Stock
                    </button>
                </div>

            </form>
        </div>

    </div>

@endsection
