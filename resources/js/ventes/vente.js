let panier = window.panierInitial ?? [];

const elements = {
    recherche: document.getElementById('search'),
    resultats: document.getElementById('resultats'),
    compteurResultats: document.getElementById('compteurResultats'),
    panier: document.getElementById('panier'),
    compteurPanier: document.getElementById('compteurPanier'),
    total: document.getElementById('total'),
    message: document.getElementById('messageStock'),
};

const formatFcfa = (montant) => new Intl.NumberFormat('fr-FR').format(montant);

/** Échappement HTML sécurisé */
const echapper = (valeur) => String(valeur ?? '').replace(
    /[&<>"']/g,
    (caractere) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[caractere]
);

// --- Messages -----------------------------------------------------------
function afficherMessage(texte, ton = 'info') {
    const classes = { info: 'notice-info', warn: 'notice-warn', error: 'notice-error' };
    elements.message.innerHTML = `<div class="${classes[ton]}">${texte}</div>`;
}

function effacerMessage() {
    elements.message.innerHTML = '';
}

// --- Panier -------------------------------------------------------------
function ajouterProduit(medicament) {
    const existant = panier.find(ligne => ligne.id === medicament.id);
    const quantite = existant ? existant.quantite + 1 : 1;

    if (quantite > medicament.stock) {
        afficherMessage(`Stock insuffisant pour ${medicament.nom} : ${medicament.stock} unité(s) disponible(s).`, 'warn');
        return;
    }

    if (medicament.ordonnance_requise) {
        afficherMessage(`⚠️ ${medicament.nom} requiert la présentation d'une ordonnance médicale valide.`, 'info');
    }

    if (existant) {
        existant.quantite++;
    } else {
        panier.push({
            id: medicament.id,
            nom: medicament.nom,
            dci: medicament.dci ?? null,
            dosage: medicament.dosage ?? null,
            forme: medicament.forme ?? null,
            tableau: medicament.tableau ?? null,
            ordonnance_requise: !!medicament.ordonnance_requise,
            prix: Number(medicament.prix),
            stock: medicament.stock,
            quantite: 1,
        });
    }

    afficherPanier();
    rendreResultats();
}

function modifierQte(index, valeur) {
    const ligne = panier[index];
    const nouvelleQuantite = ligne.quantite + valeur;

    if (nouvelleQuantite > ligne.stock) {
        afficherMessage(`Stock insuffisant : ${ligne.stock} unité(s) disponible(s).`, 'warn');
        return;
    }

    if (nouvelleQuantite <= 0) {
        panier.splice(index, 1);
    } else {
        ligne.quantite = nouvelleQuantite;
    }

    effacerMessage();
    afficherPanier();
    rendreResultats();
}

function supprimer(index) {
    panier.splice(index, 1);
    effacerMessage();
    afficherPanier();
    rendreResultats();
}

function afficherPanier() {
    const articles = panier.reduce((somme, ligne) => somme + ligne.quantite, 0);

    elements.compteurPanier.textContent = articles + (articles > 1 ? ' articles' : ' article');

    if (panier.length === 0) {
        elements.panier.innerHTML = `
            <div class="py-12 text-center text-[13px] text-muted">
                <svg class="size-8 mx-auto mb-2 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="20" r="1.4" />
                    <circle cx="18" cy="20" r="1.4" />
                    <path d="M2.5 3h2l2.2 12.2a1.5 1.5 0 0 0 1.5 1.3h8.6a1.5 1.5 0 0 0 1.5-1.2L21 7H6" />
                </svg>
                Le panier est vide. Scannez un code-barres ou recherchez un produit.
            </div>
        `;
        elements.total.textContent = '0';
        return;
    }

    let total = 0;

    elements.panier.innerHTML = panier.map((ligne, index) => {
        const sousTotal = ligne.prix * ligne.quantite;
        total += sousTotal;

        const sousTitre = [ligne.dci ? `DCI: ${ligne.dci}` : null, ligne.dosage, ligne.forme].filter(Boolean).join(' · ');

        return `
            <div class="group flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-2.5 shadow-xs transition-colors hover:border-slate-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        <span class="truncate text-sm font-bold text-slate-800">${echapper(ligne.nom)}</span>
                        ${ligne.ordonnance_requise ? '<span class="text-[9px] font-bold text-rose-700 bg-rose-50 px-1 py-0.2 rounded border border-rose-200">Rx</span>' : ''}
                    </div>
                    ${sousTitre ? `<div class="text-[11px] text-slate-500 truncate">${echapper(sousTitre)}</div>` : ''}
                    <div class="num text-xs text-slate-500 mt-0.5">${formatFcfa(ligne.prix)} FCFA / u</div>
                </div>

                <div class="flex items-center gap-1.5 rounded-lg bg-slate-100 p-1">
                    <button type="button" onclick="modifierQte(${index}, -1)" aria-label="Retirer une unité"
                        class="grid size-6 place-items-center rounded-md border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 active:scale-95 transition-all">−</button>
                    <span class="num w-6 text-center text-xs font-bold text-slate-900">${ligne.quantite}</span>
                    <button type="button" onclick="modifierQte(${index}, 1)" aria-label="Ajouter une unité"
                        class="grid size-6 place-items-center rounded-md border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 active:scale-95 transition-all">+</button>
                </div>

                <div class="num w-24 shrink-0 text-right text-sm font-bold text-slate-900">${formatFcfa(sousTotal)} <span class="text-[10px] text-slate-400 font-normal">F</span></div>

                <button type="button" onclick="supprimer(${index})" aria-label="Retirer du panier"
                    class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        `;
    }).join('');

    elements.total.textContent = formatFcfa(total);
}

window.ajouterProduit = ajouterProduit;
window.modifierQte = modifierQte;
window.supprimer = supprimer;

// --- Recherche & Douchette Code-barres -----------------------------------
let derniersResultats = [];
let debounceRecherche = null;

function rendreResultats() {
    if (derniersResultats.length === 0) {
        return;
    }

    elements.resultats.innerHTML = derniersResultats.map(medicament => {
        const dansPanier = panier.find(ligne => ligne.id === medicament.id);
        const restant = medicament.stock - (dansPanier ? dansPanier.quantite : 0);
        const epuise = restant <= 0;

        const details = [
            medicament.forme,
            medicament.dosage
        ].filter(Boolean).join(' · ');

        return `
            <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs transition-all hover:border-emerald-300 hover:shadow-md ${epuise ? 'opacity-60 bg-slate-50' : ''}">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl ${epuise ? 'bg-rose-50 text-rose-600 ring-1 ring-rose-500/20' : 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20'}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        ${epuise
                            ? '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>'
                            : '<path d="M10.5 20.5 4 14a5 5 0 0 1 7-7l1 1 1-1a5 5 0 0 1 7 7l-6.5 6.5a2 2 0 0 1-3 0Z"/><path d="m8.5 8.5 7 7"/>'}
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-sm font-bold text-slate-900">${echapper(medicament.nom)}</span>
                        ${details ? `<span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">${echapper(details)}</span>` : ''}
                    </div>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5 text-xs">
                        ${medicament.dci ? `<span class="text-emerald-700 font-medium">DCI: ${echapper(medicament.dci)}</span>` : ''}
                        ${medicament.ordonnance_requise ? `<span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-1 rounded border border-rose-200">Ordonnance requise</span>` : ''}
                    </div>

                    <div class="flex items-center gap-2 mt-1 text-xs">
                        <span class="font-bold text-emerald-700 num">${formatFcfa(medicament.prix)} FCFA</span>
                        <span class="text-slate-300">•</span>
                        <span class="${epuise ? 'font-semibold text-rose-600' : 'text-slate-500'}">
                            ${epuise ? 'Rupture' : `Dispo : <strong class="text-slate-800">${restant}</strong>`}
                        </span>
                        ${medicament.code_barre ? `<span class="text-[10px] font-mono text-slate-400">#${echapper(medicament.code_barre)}</span>` : ''}
                    </div>
                </div>

                ${epuise ? '' : `
                    <button type="button" data-ajouter="${medicament.id}" aria-label="Ajouter au panier"
                        class="inline-flex items-center gap-1 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-500 active:scale-95 transition-all">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Ajouter</span>
                    </button>
                `}
            </div>
        `;
    }).join('');
}

function lancerRecherche(valeur, estScanDouchette = false) {
    if (valeur === '') {
        derniersResultats = [];
        elements.compteurResultats.textContent = '';
        elements.resultats.innerHTML = `
            <div class="panel flex flex-col items-center justify-center p-12 text-center text-slate-400">
                <div class="mb-3 grid size-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">Recherche rapide & Douchette</p>
                <p class="mt-1 text-xs text-slate-500">Scannez un code-barres ou tapez le nom / DCI d'un produit.</p>
            </div>
        `;
        return;
    }

    fetch('/medicaments/search?q=' + encodeURIComponent(valeur))
        .then(reponse => reponse.json())
        .then(medicaments => {
            derniersResultats = medicaments;

            elements.compteurResultats.textContent = medicaments.length === 0 ?
                'Aucun résultat' :
                medicaments.length + (medicaments.length > 1 ? ' résultats' : ' résultat');

            if (medicaments.length === 0) {
                elements.resultats.innerHTML = `
                    <p class="py-10 text-center text-[13px] text-muted">
                        Aucun médicament ne correspond à « ${echapper(valeur)} ».
                    </p>
                `;
                return;
            }

            // Si scan douchette exact ou touche Entrée avec un seul produit correspondant
            const matchExactCode = medicaments.find(m => m.code_barre === valeur);
            if (estScanDouchette && (matchExactCode || medicaments.length === 1)) {
                const produitChoisi = matchExactCode ?? medicaments[0];
                ajouterProduit(produitChoisi);
                elements.recherche.value = '';
                derniersResultats = [];
                elements.compteurResultats.textContent = `✓ ${produitChoisi.nom} scanné et ajouté !`;
                return;
            }

            rendreResultats();
        })
        .catch(() => afficherMessage('La recherche a échoué. Vérifiez la connexion.', 'error'));
}

if (elements.recherche) {
    afficherPanier();

    // Délégation clic ajouter
    elements.resultats.addEventListener('click', (event) => {
        const bouton = event.target.closest('[data-ajouter]');
        if (!bouton) return;

        const medicament = derniersResultats.find(
            (candidat) => String(candidat.id) === bouton.dataset.ajouter
        );

        if (medicament) {
            ajouterProduit(medicament);
        }
    });

    // Écoute de la saisie avec debouncing
    elements.recherche.addEventListener('input', function() {
        const valeur = this.value.trim();
        clearTimeout(debounceRecherche);
        debounceRecherche = setTimeout(() => {
            lancerRecherche(valeur, false);
        }, 250);
    });

    // Support touche Entrée (pour scanneur de code-barres rapide)
    elements.recherche.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const valeur = this.value.trim();
            if (valeur !== '') {
                lancerRecherche(valeur, true);
            }
        }
    });
}

// --- Passage au paiement ------------------------------------------------
function passerAuPaiement() {
    if (panier.length === 0) {
        afficherMessage('Le panier est vide.', 'warn');
        return;
    }

    fetch('/ventes/panier', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            panier: panier.map(ligne => ({ id: ligne.id, quantite: ligne.quantite })),
        }),
    })
        .then(reponse => reponse.json().then(data => ({ ok: reponse.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                afficherMessage(data.error || 'Le panier n\'a pas pu être validé.', 'error');
                return;
            }
            window.location.href = data.redirect;
        })
        .catch(() => afficherMessage('Erreur réseau : le panier n\'a pas été transmis.', 'error'));
}

window.passerAuPaiement = passerAuPaiement;
