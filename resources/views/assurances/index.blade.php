@extends('layout')

@section('titre', 'Organismes Payeurs & Assurances — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 12h10"/><path d="M12 7v10"/>
            </svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Tiers Payant & Organismes Payeurs</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Gestion des conventions d'assurances, mutuelles et taux de prise en charge</p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <button type="button" onclick="ouvrirModalAjout()" class="btn-primary inline-flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span>Nouvelle Assurance</span>
        </button>
    </div>
@endsection

@section('content')

    {{-- Cartes de synthèse --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="panel p-5 bg-gradient-to-br from-emerald-900 to-slate-900 text-white shadow-md">
            <div class="flex items-center justify-between text-xs text-emerald-300 font-semibold mb-2">
                <span>Créances Tiers Payant en attente</span>
                <span class="rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px]">À facturer</span>
            </div>
            <div class="num text-3xl font-black tracking-tight text-emerald-300">
                {{ number_format($totalCreancesGlobales, 0, ',', ' ') }} <span class="text-sm font-medium text-emerald-200">FCFA</span>
            </div>
            <p class="mt-2 text-xs text-slate-300">En attente d'émission en bordereaux officiels</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-teal-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Organismes Partenaires</div>
            <div class="num text-2xl font-black text-slate-900 dark:text-white">
                {{ $assurances->total() }} <span class="text-sm font-normal text-slate-500">conventions</span>
            </div>
            <p class="mt-2 text-xs text-slate-400">CANAM, INPS, Mutuelles & Privés</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-sky-500 flex flex-col justify-between">
            <div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Facturation groupée</div>
                <p class="text-xs text-slate-600 dark:text-slate-300">Regrouper les prises en charge par période et émettre le récapitulatif</p>
            </div>
            <div class="mt-3">
                <a href="{{ route('bordereaux.create') }}" class="btn-ghost text-sky-600 dark:text-sky-400 text-xs font-bold inline-flex items-center gap-1.5 p-0 hover:underline">
                    <span>Générer un bordereau officiel</span>
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Table des organismes d'assurance --}}
    <div class="panel overflow-hidden">
        <div class="table-container">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Organisme / Mutuelle</th>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4 text-center">Couverture Défaut</th>
                        <th class="py-3 px-4 text-center">Délai Règl.</th>
                        <th class="py-3 px-4 text-right">Dossiers en attente</th>
                        <th class="py-3 px-4 text-right">Créances à recouvrer</th>
                        <th class="py-3 px-4 text-center">Statut</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($assurances as $assurance)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-8 rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400 font-black text-xs grid place-items-center">
                                        {{ substr($assurance->code, 0, 3) }}
                                    </div>
                                    <div>
                                        <div>{{ $assurance->nom }}</div>
                                        @if($assurance->telephone || $assurance->email)
                                            <div class="text-xs text-slate-400 font-normal">
                                                {{ $assurance->telephone }} {{ $assurance->email ? '• ' . $assurance->email : '' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="rounded-md bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $assurance->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 px-2.5 py-0.5 text-xs font-bold">
                                    {{ (int)$assurance->taux_couverture_defaut }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs text-slate-600 dark:text-slate-400 font-medium">
                                {{ $assurance->delai_remboursement_jours }} jours
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="num text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $assurance->ventes_en_attente_count }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black num text-slate-900 dark:text-white">
                                {{ number_format($assurance->total_creances_en_attente ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($assurance->est_actif)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 px-2 py-0.5 text-[11px] font-semibold">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 px-2 py-0.5 text-[11px] font-semibold">
                                        Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" onclick='ouvrirModalEdition(@json($assurance))'
                                    class="btn-icon size-8 inline-flex items-center justify-center text-slate-500 hover:text-emerald-600" title="Modifier">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                    </svg>
                                </button>
                                @if($assurance->ventes_en_attente_count === 0 && $assurance->bordereaux_count === 0)
                                    <form action="{{ route('assurances.destroy', $assurance) }}" method="post" class="inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon size-8 inline-flex items-center justify-center text-slate-400 hover:text-rose-600" title="Supprimer">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                Aucun organisme d'assurance enregistré pour l'instant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assurances->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $assurances->links() }}
            </div>
        @endif
    </div>

    {{-- MODALE AJOUT / MODIFICATION --}}
    <div id="modalAssurance" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="panel max-w-lg w-full p-6 relative shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-5">
                    <h2 id="modalTitre" class="text-base font-bold text-slate-900 dark:text-white">Nouvelle Assurance / Mutuelle</h2>
                    <button type="button" onclick="fermerModal()" class="btn-icon text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form id="formAssurance" method="post" action="{{ route('assurances.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nom de l'organisme *</label>
                            <input type="text" name="nom" id="champNom" required placeholder="ex: CANAM (AMO), INPS, NSIA"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Code court *</label>
                            <input type="text" name="code" id="champCode" required placeholder="CANAM"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Taux couverture défaut (%) *</label>
                            <div class="relative flex items-center">
                                <input type="number" name="taux_couverture_defaut" id="champTaux" min="0" max="100" step="1" required value="70"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm pr-8">
                                <span class="absolute right-3 text-xs font-bold text-slate-400">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Délai remboursement (jours) *</label>
                            <input type="number" name="delai_remboursement_jours" id="champDelai" min="1" max="180" required value="30"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Téléphone convention</label>
                            <input type="text" name="telephone" id="champTelephone" placeholder="+223 20 22 .."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Email facturation</label>
                            <input type="email" name="email" id="champEmail" placeholder="tierspayant@assurance.ml"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Adresse ou Remarques</label>
                        <textarea name="adresse" id="champAdresse" rows="2" placeholder="Siège, interlocuteur conventionné..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="est_actif" id="champActif" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500 size-4">
                        <label for="champActif" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Organisme conventionné actif (proposé au comptoir)</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="fermerModal()" class="btn-ghost">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const modal = document.getElementById('modalAssurance');
        const form = document.getElementById('formAssurance');
        const formMethod = document.getElementById('formMethod');
        const titre = document.getElementById('modalTitre');

        function ouvrirModalAjout() {
            titre.textContent = 'Nouvelle Assurance / Mutuelle';
            form.action = "{{ route('assurances.store') }}";
            formMethod.value = 'POST';
            form.reset();
            document.getElementById('champActif').checked = true;
            document.getElementById('champTaux').value = '70';
            document.getElementById('champDelai').value = '30';
            modal.classList.remove('hidden');
        }

        function ouvrirModalEdition(assurance) {
            titre.textContent = 'Modifier ' + assurance.nom;
            form.action = `/assurances/${assurance.id}`;
            formMethod.value = 'PUT';
            document.getElementById('champNom').value = assurance.nom;
            document.getElementById('champCode').value = assurance.code;
            document.getElementById('champTaux').value = assurance.taux_couverture_defaut;
            document.getElementById('champDelai').value = assurance.delai_remboursement_jours;
            document.getElementById('champTelephone').value = assurance.telephone || '';
            document.getElementById('champEmail').value = assurance.email || '';
            document.getElementById('champAdresse').value = assurance.adresse || '';
            document.getElementById('champActif').checked = Boolean(assurance.est_actif);
            modal.classList.remove('hidden');
        }

        function fermerModal() {
            modal.classList.add('hidden');
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') fermerModal();
        });
    </script>
@endpush
