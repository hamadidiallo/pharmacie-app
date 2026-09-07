@extends('layout')

@section('titre', 'Gestion de Caisse & Sessions — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Sessions de Caisse & Billetage</h1>
            <p class="text-xs text-slate-500 font-medium">Contrôle des flux d'espèces, suivi des écarts et rapports journaliers Z</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2.5">
        @if (!$sessionActive)
            <button type="button" data-dialog="ouvrir-caisse" class="btn-primary">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                <span>Ouvrir la caisse du jour</span>
            </button>
        @else
            <button type="button" data-dialog="mouvement-caisse" class="btn-ghost">
                <svg class="size-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
                <span>Sortie d'espèces / Dépense</span>
            </button>
            <a href="{{ route('caisse.sessions.cloture', $sessionActive) }}" class="btn-primary">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <span>Clôturer la caisse (Z)</span>
            </a>
        @endif
    </div>
@endsection

@section('content')

    {{-- 1. CARTE DE LA SESSION ACTIVE EN COURS --}}
    @if ($sessionActive)
        <div class="panel p-6 border-l-4 border-l-emerald-500 bg-gradient-to-r from-emerald-50/40 via-white to-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-600/20">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h8M6 16h12" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">Session de Caisse en cours #{{ $sessionActive->id }}</h2>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                                <span class="size-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                Ouverte
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Ouverte par <strong>{{ $sessionActive->user->firstname }} {{ $sessionActive->user->lastname }}</strong> le {{ $sessionActive->date_ouverture->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('ventes.create') }}" class="btn-primary text-xs h-9">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                        </svg>
                        Aller au comptoir
                    </a>
                </div>
            </div>

            {{-- KPIs En direct de la session --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 mt-5">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Fond d'ouverture</span>
                    <span class="num text-xl font-bold text-slate-900 mt-1 block">
                        {{ number_format($sessionActive->fond_caisse_ouverture, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                    </span>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 block">Ventes Espèces</span>
                    <span class="num text-xl font-bold text-emerald-700 mt-1 block">
                        {{ number_format($sessionActive->total_especes_theorique, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                    </span>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-sky-600 block">Mobile Money / Wave</span>
                    <span class="num text-xl font-bold text-sky-700 mt-1 block">
                        {{ number_format($sessionActive->total_mobile_money, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                    </span>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600 block">Dépenses / Sorties</span>
                    <span class="num text-xl font-bold text-amber-700 mt-1 block">
                        -{{ number_format($sessionActive->total_sorties_especes, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                    </span>
                </div>

                <div class="rounded-2xl bg-slate-900 p-4 shadow-sm text-white">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400 block">Tiroir Attendu (Espèces)</span>
                    <span class="num text-xl font-black text-white mt-1 block">
                        {{ number_format($sessionActive->soldeTheoriqueAttendu(), 0, ',', ' ') }} <span class="text-xs font-normal text-emerald-300">FCFA</span>
                    </span>
                </div>
            </div>
        </div>
    @else
        <div class="panel p-8 text-center bg-gradient-to-b from-slate-50 to-white border-dashed border-2 border-slate-200">
            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-500/20 mb-3">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h8M6 16h12" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900">Aucune caisse ouverte actuellement</h2>
            <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                Pour commencer les encaissements au comptoir et garantir le traçage comptable, veuillez ouvrir la session de caisse avec le fond initial.
            </p>
            <div class="mt-5">
                <button type="button" data-dialog="ouvrir-caisse" class="btn-primary text-sm px-6 h-11">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Ouvrir la session de caisse
                </button>
            </div>
        </div>
    @endif

    {{-- 2. HISTORIQUE DES SESSIONS DE CAISSE --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Historique des Sessions & Clôtures Z</h2>
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('caisse.sessions.index', ['filtre' => 'toutes']) }}"
                    @class(['filter-tab-active' => $filtre === 'toutes', 'filter-tab' => $filtre !== 'toutes'])>
                    Toutes
                </a>
                <a href="{{ route('caisse.sessions.index', ['filtre' => 'ouvertes']) }}"
                    @class(['filter-tab-active' => $filtre === 'ouvertes', 'filter-tab' => $filtre !== 'ouvertes'])>
                    Ouvertes
                </a>
                <a href="{{ route('caisse.sessions.index', ['filtre' => 'cloturees']) }}"
                    @class(['filter-tab-active' => $filtre === 'cloturees', 'filter-tab' => $filtre !== 'cloturees'])>
                    Clôturées (Rapport Z)
                </a>
            </div>
        </div>

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-data">
                    <thead>
                        <tr>
                            <th>RÉFÉRENCE & DATE</th>
                            <th>CAISSIER</th>
                            <th>FOND INITIAL</th>
                            <th>CHIFFRE D'AFFAIRES</th>
                            <th>ESPÈCES COMPTÉES</th>
                            <th>ÉCART DE CAISSE</th>
                            <th>STATUT</th>
                            <th class="text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5">
                                    <div class="font-bold text-slate-900">Session #{{ $session->id }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $session->date_ouverture->format('d/m/Y H:i') }}
                                        @if ($session->date_fermeture)
                                            &rarr; {{ $session->date_fermeture->format('H:i') }}
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="text-sm font-medium text-slate-800">{{ $session->user->firstname }} {{ $session->user->lastname }}</div>
                                    <div class="text-xs text-slate-400">{{ $session->user->role?->libelle() }}</div>
                                </td>

                                <td class="num font-semibold text-slate-700">
                                    {{ number_format($session->fond_caisse_ouverture, 0, ',', ' ') }} <span class="text-xs text-slate-400">F</span>
                                </td>

                                <td class="num font-bold text-emerald-700">
                                    {{ number_format($session->totalChiffreAffaires(), 0, ',', ' ') }} <span class="text-xs text-slate-400">FCFA</span>
                                </td>

                                <td class="num">
                                    @if ($session->montant_reel_compte !== null)
                                        <span class="font-bold text-slate-900">{{ number_format($session->montant_reel_compte, 0, ',', ' ') }}</span> <span class="text-xs text-slate-400">F</span>
                                    @else
                                        <span class="text-xs text-slate-400">En cours...</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($session->ecart_caisse !== null)
                                        @php
                                            $ecart = (float) $session->ecart_caisse;
                                        @endphp
                                        <span @class([
                                            'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold ring-1',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $ecart == 0,
                                            'bg-sky-50 text-sky-700 ring-sky-600/20' => $ecart > 0,
                                            'bg-rose-50 text-rose-700 ring-rose-600/20' => $ecart < 0,
                                        ])>
                                            {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }} FCFA
                                            @if ($ecart == 0) (Exact) @elseif($ecart > 0) (Excédent) @else (Déficit) @endif
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full {{ $session->statut->badgeClasses() }}">
                                        {{ $session->statut->libelle() }}
                                    </span>
                                </td>

                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('caisse.sessions.show', $session) }}" class="btn-icon" title="Détail de la session">
                                            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>

                                        @if ($session->estOuverte())
                                            <a href="{{ route('caisse.sessions.cloture', $session) }}" class="btn-icon" title="Clôturer la caisse">
                                                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                                    <path d="m9 12 2 2 4-4" />
                                                </svg>
                                            </a>
                                        @else
                                            <a href="{{ route('caisse.sessions.rapport-z', $session) }}" class="btn-icon" title="Imprimer le Rapport Z">
                                                <svg class="size-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="6 9 6 2 18 2 18 9"/>
                                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                                    <rect width="12" height="8" x="6" y="14"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Aucune session de caisse enregistrée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
                <span class="text-xs text-slate-500 font-medium">
                    {{ $sessions->total() }} session(s) au total
                </span>
                {{ $sessions->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL : OUVERTURE DE CAISSE --}}
    <dialog id="ouvrir-caisse" class="dialog-panel max-w-md">
        <div class="panel-head">
            <h2 class="panel-title flex items-center gap-2 text-emerald-800">
                <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h8M6 16h12" />
                </svg>
                Ouverture de Caisse Journalière
            </h2>
            <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('caisse.sessions.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-600">
                    Renseignez le montant liquide présent dans le tiroir au démarrage de la garde ou de la vacation.
                </p>

                <div>
                    <label class="field-label">Fond de caisse initial (FCFA) *</label>
                    <input type="number" name="fond_caisse_ouverture" required min="0" step="100" value="25000"
                        class="field-input font-bold text-base text-slate-900">
                    <p class="mt-1 text-xs text-slate-400">Montant destiné à rendre la monnaie aux premiers clients.</p>
                </div>

                <div>
                    <label class="field-label">Remarques ou observations (optionnel)</label>
                    <textarea name="observations" rows="2" placeholder="Ex: Vacation du matin, caissier principal..." class="field-input h-auto py-2 text-xs"></textarea>
                </div>
            </div>

            <div class="panel-foot flex justify-end gap-2">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <button type="submit" class="btn-primary">
                    Valider et Ouvrir la Caisse
                </button>
            </div>
        </form>
    </dialog>

    {{-- MODAL : DÉPENSE / SORTIE D'ESPÈCES --}}
    @if ($sessionActive)
        <dialog id="mouvement-caisse" class="dialog-panel max-w-md">
            <div class="panel-head">
                <h2 class="panel-title flex items-center gap-2 text-amber-800">
                    <svg class="size-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                    Sortie d'espèces / Dépense Officine
                </h2>
                <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('caisse.sessions.mouvements.store', $sessionActive) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="sortie">
                <div class="p-6 space-y-4">
                    <p class="text-xs text-slate-600">
                        Cette dépense déduira immédiatement le montant du solde théorique du tiroir-caisse et apparaîtra sur le rapport Z.
                    </p>

                    <div>
                        <label class="field-label">Montant de la dépense (FCFA) *</label>
                        <input type="number" name="montant" required min="100" placeholder="5000"
                            class="field-input font-bold text-base text-slate-900">
                    </div>

                    <div>
                        <label class="field-label">Motif de la dépense *</label>
                        <input type="text" name="motif" required placeholder="Ex: Rouleaux thermiques tickets, Nettoyage..." class="field-input text-xs">
                    </div>

                    <div>
                        <label class="field-label">Bénéficiaire / Destinataire (optionnel)</label>
                        <input type="text" name="beneficiaire" placeholder="Ex: Librairie centrale, Agent coursier..." class="field-input text-xs">
                    </div>
                </div>

                <div class="panel-foot flex justify-end gap-2">
                    <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                    <button type="submit" class="btn-danger">
                        Enregistrer la sortie
                    </button>
                </div>
            </form>
        </dialog>
    @endif

@endsection
