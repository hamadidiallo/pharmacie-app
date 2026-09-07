@extends('layout')

@section('titre', 'Fournisseurs & Grossistes — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Répertoire Fournisseurs & Grossistes</h1>
            <p class="text-xs text-slate-500 font-medium">Gestion des grossistes-répartiteurs et laboratoires partenaires</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2.5">
        <button type="button" data-dialog="ajouter-fournisseur" class="btn-primary">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            <span>Nouveau fournisseur</span>
        </button>
    </div>
@endsection

@section('content')

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-data">
                <thead>
                    <tr>
                        <th>FOURNISSEUR / GROSSISTE</th>
                        <th>CONTACT & TÉLÉPHONE</th>
                        <th>DÉLAI DE LIVRAISON</th>
                        <th>CONDITIONS RÈGLEMENT</th>
                        <th>COMMANDES</th>
                        <th>STATUT</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fournisseurs as $fournisseur)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5">
                                <div class="font-bold text-slate-900 text-sm">{{ $fournisseur->nom }}</div>
                                <div class="text-xs text-slate-400 font-mono">
                                    {{ $fournisseur->code_fournisseur ?? 'GROSSISTE' }} · {{ $fournisseur->ville }}
                                </div>
                            </td>

                            <td>
                                <div class="text-xs font-semibold text-slate-800">{{ $fournisseur->telephone ?? 'Non renseigné' }}</div>
                                @if ($fournisseur->email)
                                    <div class="text-[11px] text-slate-400">{{ $fournisseur->email }}</div>
                                @endif
                            </td>

                            <td>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                    <svg class="size-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    ~{{ $fournisseur->delai_livraison_jours }} jour(s)
                                </span>
                            </td>

                            <td class="text-xs text-slate-600">
                                {{ $fournisseur->conditions_paiement }}
                            </td>

                            <td>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-700">
                                    {{ $fournisseur->commandes_count }} commande(s)
                                </span>
                            </td>

                            <td>
                                @if ($fournisseur->actif)
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                        Inactif
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" data-dialog="modifier-{{ $fournisseur->id }}" class="btn-icon" title="Modifier le fournisseur">
                                        <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </button>

                                    <form action="{{ route('fournisseurs.destroy', $fournisseur) }}" method="POST"
                                        onsubmit="return confirm('Confirmer la suppression ou désactivation de ce fournisseur ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-danger" title="Supprimer">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Aucun fournisseur enregistré dans le répertoire.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
            <span class="text-xs text-slate-500 font-medium">
                {{ $fournisseurs->total() }} fournisseur(s) au total
            </span>
            {{ $fournisseurs->links() }}
        </div>
    </div>

    {{-- MODAL AJOUT FOURNISSEUR --}}
    <dialog id="ajouter-fournisseur" class="dialog-panel max-w-lg">
        <div class="panel-head">
            <h2 class="panel-title flex items-center gap-2">
                <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                Nouveau Fournisseur / Grossiste
            </h2>
            <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('fournisseurs.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4 text-xs">
                <div>
                    <label class="field-label">Nom du grossiste / laboratoire *</label>
                    <input type="text" name="nom" required placeholder="Ex: LABOREX MALI, COPHARM, UBIPHARM..." class="field-input">
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Code Fournisseur (identifiant)</label>
                        <input type="text" name="code_fournisseur" placeholder="Ex: LAB-01" class="field-input uppercase font-mono">
                    </div>
                    <div>
                        <label class="field-label">Délai de livraison moyen (Jours) *</label>
                        <input type="number" name="delai_livraison_jours" required min="1" max="30" value="2" class="field-input">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Téléphone de contact</label>
                        <input type="text" name="telephone" placeholder="Ex: +223 20 22 10 10" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Adresse Email</label>
                        <input type="email" name="email" placeholder="commandes@grossiste.com" class="field-input">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Ville</label>
                        <input type="text" name="ville" value="Kati" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Conditions de règlement</label>
                        <input type="text" name="conditions_paiement" value="Comptant à livraison" class="field-input">
                    </div>
                </div>

                <div>
                    <label class="field-label">Adresse physique</label>
                    <input type="text" name="adresse" placeholder="Zone industrielle, Rue..." class="field-input">
                </div>
            </div>

            <div class="panel-foot flex justify-end gap-2">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer le fournisseur</button>
            </div>
        </form>
    </dialog>

    {{-- MODALS MODIFICATION FOURNISSEUR --}}
    @foreach ($fournisseurs as $fournisseur)
        <dialog id="modifier-{{ $fournisseur->id }}" class="dialog-panel max-w-lg">
            <div class="panel-head">
                <h2 class="panel-title">Modifier : {{ $fournisseur->nom }}</h2>
                <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('fournisseurs.update', $fournisseur) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="field-label">Nom du grossiste *</label>
                        <input type="text" name="nom" required value="{{ $fournisseur->nom }}" class="field-input">
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="field-label">Code Fournisseur</label>
                            <input type="text" name="code_fournisseur" value="{{ $fournisseur->code_fournisseur }}" class="field-input uppercase font-mono">
                        </div>
                        <div>
                            <label class="field-label">Délai moyen (Jours) *</label>
                            <input type="number" name="delai_livraison_jours" required min="1" max="30" value="{{ $fournisseur->delai_livraison_jours }}" class="field-input">
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="field-label">Téléphone</label>
                            <input type="text" name="telephone" value="{{ $fournisseur->telephone }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email" name="email" value="{{ $fournisseur->email }}" class="field-input">
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="field-label">Ville</label>
                            <input type="text" name="ville" value="{{ $fournisseur->ville }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Conditions de règlement</label>
                            <input type="text" name="conditions_paiement" value="{{ $fournisseur->conditions_paiement }}" class="field-input">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="actif" value="1" @checked($fournisseur->actif) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-bold text-slate-800">Fournisseur actif dans le catalogue</span>
                        </label>
                    </div>
                </div>

                <div class="panel-foot flex justify-end gap-2">
                    <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </dialog>
    @endforeach

@endsection
