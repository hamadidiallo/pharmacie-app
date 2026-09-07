{{-- Partiel inclus dans la modale de modification de medicaments/index --}}
<form action="{{ route('medicaments.update', $medicament) }}" method="post" class="space-y-4">

    @method('put')
    @csrf

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-component.input name="nom" value="{{ old('nom', $medicament->nom) }}" label="Nom commercial ou Spécialité *" />
        </div>

        <div>
            <x-component.input name="dci" value="{{ old('dci', $medicament->dci) }}" label="DCI (Principe actif)"
                placeholder="Ex: Paracétamol" />
        </div>

        <div>
            <x-component.input name="code_barre" value="{{ old('code_barre', $medicament->code_barre) }}" label="Code-barres (EAN-13)"
                placeholder="Ex: 3400936192131" />
        </div>

        <div>
            <x-component.input name="forme" value="{{ old('forme', $medicament->forme) }}" label="Forme galénique"
                placeholder="Ex: Comprimé, Sirop..." />
        </div>

        <div>
            <x-component.input name="dosage" value="{{ old('dosage', $medicament->dosage) }}" label="Dosage"
                placeholder="Ex: 500mg, 1g..." />
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 pt-2 border-t border-slate-100">
        <div>
            <label for="tableau-{{ $medicament->id }}" class="field-label">Tableau réglementaire</label>
            <select id="tableau-{{ $medicament->id }}" name="tableau" class="field-input">
                @foreach (\App\Enums\TableauReglementaire::cases() as $tab)
                    <option value="{{ $tab->value }}" @selected(old('tableau', $medicament->tableau?->value ?? 'non_liste') === $tab->value)>
                        {{ $tab->libelle() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col justify-end">
            <label class="flex items-center gap-2 cursor-pointer mt-2">
                <input type="checkbox" name="ordonnance_requise" value="1" class="rounded border-slate-300 text-brand focus:ring-brand"
                    @checked(old('ordonnance_requise', $medicament->ordonnance_requise))>
                <span class="text-xs font-semibold text-slate-800">Ordonnance médicale obligatoire (Rx)</span>
            </label>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3 pt-2 border-t border-slate-100">
        <div>
            <x-component.input name="prix" type="number" value="{{ old('prix', $medicament->prix) }}" label="Prix unitaire (FCFA) *" />
        </div>
        <div>
            <x-component.input name="stock" type="number" value="{{ old('stock', $medicament->stock) }}" label="Stock global *" />
        </div>
        <div>
            <x-component.input name="stock_securite" type="number" value="{{ old('stock_securite', $medicament->stock_securite ?? 10) }}" label="Stock de sécurité" />
        </div>
    </div>

    <x-component.input name="date_expiration" type="date"
        value="{{ old('date_expiration', $medicament->date_expiration->format('Y-m-d')) }}" label="Date d'expiration globale *" />

    <x-component.input name="description" type="textarea" value="{{ old('description', $medicament->description) }}"
        label="Description & Posologie" />

    <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
        <button type="submit" class="btn-primary">Enregistrer les modifications</button>
    </div>

</form>
