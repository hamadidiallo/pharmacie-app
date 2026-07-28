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

/** Les noms de médicaments viennent de la base : on ne les injecte jamais bruts. */
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
        afficherMessage(`Stock insuffisant : ${medicament.stock} unité(s) disponible(s).`, 'warn');
        return;
    }

    if (existant) {
        existant.quantite++;
    } else {
        panier.push({
            id: medicament.id,
            nom: medicament.nom,
            prix: Number(medicament.prix),
            stock: medicament.stock,
            quantite: 1,
        });
    }

    effacerMessage();
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
            <p class="py-12 text-center text-[13px] text-muted">
                Le panier est vide.
            </p>
        `;
        elements.total.textContent = '0';
        return;
    }

    let total = 0;

    elements.panier.innerHTML = panier.map((ligne, index) => {

        const sousTotal = ligne.prix * ligne.quantite;
        total += sousTotal;

        return `
            <div class="group flex items-center gap-3 rounded-lg px-2.5 py-2.5 ${index % 2 ? 'bg-[#F7FAF9]' : ''}">

                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold">${echapper(ligne.nom)}</div>
                    <div class="num text-xs text-faint">${formatFcfa(ligne.prix)} FCFA</div>
                </div>

                <div class="flex items-center gap-2 rounded-lg bg-surface p-[3px]">
                    <button type="button" onclick="modifierQte(${index}, -1)" aria-label="Retirer une unité"
                        class="grid size-[26px] place-items-center rounded-md border border-line bg-white text-[15px] text-slate-ink hover:bg-surface">−</button>
                    <span class="num w-[22px] text-center text-sm font-semibold">${ligne.quantite}</span>
                    <button type="button" onclick="modifierQte(${index}, 1)" aria-label="Ajouter une unité"
                        class="grid size-[26px] place-items-center rounded-md border border-line bg-white text-[15px] text-slate-ink hover:bg-surface">+</button>
                </div>

                <div class="num w-[82px] shrink-0 text-right text-sm font-semibold">${formatFcfa(sousTotal)}</div>

                <button type="button" onclick="supprimer(${index})" aria-label="Retirer du panier"
                    class="grid size-7 shrink-0 place-items-center rounded-md text-faint hover:bg-danger-bg hover:text-danger-fg">
                    <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
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

// --- Recherche ----------------------------------------------------------
let derniersResultats = [];

function rendreResultats() {

    if (derniersResultats.length === 0) {
        return;
    }

    elements.resultats.innerHTML = derniersResultats.map(medicament => {

        const dansPanier = panier.find(ligne => ligne.id === medicament.id);
        const restant = medicament.stock - (dansPanier ? dansPanier.quantite : 0);
        const epuise = restant <= 0;

        return `
            <div class="flex items-center gap-3 rounded-xl border border-line bg-white p-3.5 ${epuise ? 'opacity-60' : ''}">

                <span class="grid size-[42px] shrink-0 place-items-center rounded-lg ${epuise ? 'bg-danger-bg' : 'bg-brand-50'}">
                    <svg class="size-[21px]" viewBox="0 0 24 24" fill="none" stroke="${epuise ? '#C1352B' : '#0F8A6B'}"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        ${epuise
                            ? '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>'
                            : '<path d="M10.5 20.5 4 14a5 5 0 0 1 7-7l1 1 1-1a5 5 0 0 1 7 7l-6.5 6.5a2 2 0 0 1-3 0Z"/><path d="m8.5 8.5 7 7"/>'}
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold">${echapper(medicament.nom)}</div>
                    <div class="text-xs ${epuise ? 'text-danger-fg' : 'text-faint'}">
                        ${epuise
                            ? 'Rupture de stock'
                            : `Stock : ${restant} · <span class="num">${formatFcfa(medicament.prix)} FCFA</span>`}
                    </div>
                </div>

                ${epuise ? '' : `
                    <button type="button" data-ajouter="${medicament.id}" aria-label="Ajouter au panier"
                        class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500 transition-colors hover:bg-brand-600">
                        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4"
                            stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                `}

            </div>
        `;
    }).join('');
}

if (elements.recherche) {

    afficherPanier();

    // délégation : le bouton porte l'identifiant, jamais le produit sérialisé
    elements.resultats.addEventListener('click', (event) => {

        const bouton = event.target.closest('[data-ajouter]');

        if (!bouton) {
            return;
        }

        const medicament = derniersResultats.find(
            (candidat) => String(candidat.id) === bouton.dataset.ajouter
        );

        if (medicament) {
            ajouterProduit(medicament);
        }
    });

    elements.recherche.addEventListener('input', function() {

        const valeur = this.value.trim();

        if (valeur === '') {
            derniersResultats = [];
            elements.compteurResultats.textContent = '';
            elements.resultats.innerHTML = `
                <p class="py-10 text-center text-[13px] text-muted">
                    Tapez les premières lettres d'un médicament.
                </p>
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
                            Aucun médicament ne correspond à « ${valeur} ».
                        </p>
                    `;
                    return;
                }

                rendreResultats();
            })
            .catch(() => afficherMessage('La recherche a échoué. Vérifiez la connexion.', 'error'));
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
