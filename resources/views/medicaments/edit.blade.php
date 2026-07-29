{{-- partiel inclus dans la modale de modification de medicaments/index --}}
<form action="{{ route('medicaments.update', $medicament) }}" method="post">

    @method('put')
    @csrf

    <x-component.input name="nom" value="{{ $medicament->nom }}" label="Nom du médicament" />

    <div class="grid gap-x-4 sm:grid-cols-2">
        <x-component.input name="prix" type="number" value="{{ $medicament->prix }}" label="Prix unitaire (FCFA)" />
        <x-component.input name="stock" type="number" value="{{ $medicament->stock }}" label="Quantité" />
    </div>

    <x-component.input name="date_expiration" type="date"
        value="{{ $medicament->date_expiration->format('Y-m-d') }}" label="Date d'expiration" />

    <x-component.input name="description" type="textarea" value="{{ $medicament->description }}"
        label="Description" />

    <div class="mt-2 flex justify-end gap-2">
        <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
        <button type="submit" class="btn-primary">Enregistrer</button>
    </div>

</form>
