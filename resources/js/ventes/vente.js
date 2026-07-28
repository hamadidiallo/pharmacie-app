let panier = [];

const elements = {
    recherche: document.getElementById('search'),
    resultats: document.getElementById('resultats'),
    panier: document.getElementById('panier'),
    total: document.getElementById('total'),
    message: document.getElementById('messageStock'),
};

const formatFcfa = (montant) => new Intl.NumberFormat('fr-FR').format(montant);

// --- Messages -----------------------------------------------------------
function afficherMessage(texte, ton = 'info') {

    const classes = {
        info: 'notice-info',
        warn: 'notice-warn',
        error: 'notice-error',
    };

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

    afficherRecherche();
    rafraichirResultats();
    afficherMessage(`${medicament.nom} ajouté au panier.`);
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
    afficherRecherche();
    rafraichirResultats();
}

function supprimer(index) {
    panier.splice(index, 1);
    effacerMessage();
    afficherRecherche();
    rafraichirResultats();
}

function afficherRecherche() {

    if (panier.length === 0) {
        elements.panier.innerHTML = `
            <tr>
                <td colspan="5" class="py-10 text-center text-ink-soft">
                    Le panier est vide.
                </td>
            </tr>
        `;
        elements.total.textContent = '0';
        return;
    }

    let total = 0;

    elements.panier.innerHTML = panier.map((ligne, index) => {

        const sousTotal = ligne.prix * ligne.quantite;
        total += sousTotal;

        return `
            <tr>
                <td class="font-medium">${ligne.nom}</td>
                <td class="num whitespace-nowrap">${formatFcfa(ligne.prix)}</td>
                <td>
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" class="btn-ghost btn-sm" aria-label="Retirer une unité"
                            onclick="modifierQte(${index}, -1)">−</button>
                        <span class="figure w-8 text-center font-semibold">${ligne.quantite}</span>
                        <button type="button" class="btn-ghost btn-sm" aria-label="Ajouter une unité"
                            onclick="modifierQte(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="num whitespace-nowrap font-semibold">${formatFcfa(sousTotal)}</td>
                <td class="text-right">
                    <button type="button"
                        class="btn-ghost btn-sm text-rouge-700 hover:border-rouge-100 hover:bg-rouge-50"
                        onclick="supprimer(${index})">Retirer</button>
                </td>
            </tr>
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
        elements.resultats.innerHTML = `
            <p class="py-8 text-center text-sm text-ink-soft">Aucun médicament trouvé.</p>
        `;
        return;
    }

    elements.resultats.innerHTML = derniersResultats.map(medicament => {

        const dansPanier = panier.find(ligne => ligne.id === medicament.id);
        const restant = medicament.stock - (dansPanier ? dansPanier.quantite : 0);

        const pastille = restant === 0
            ? '<span class="badge-rupture">épuisé</span>'
            : restant <= 5
                ? `<span class="badge-faible">${restant}</span>`
                : `<span class="badge-ok">${restant}</span>`;

        return `
            <div class="flex items-center justify-between gap-3 rounded-md border border-rule p-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">${medicament.nom}</p>
                    <p class="figure mt-0.5 text-xs text-ink-soft">
                        ${formatFcfa(medicament.prix)} FCFA · reste ${pastille}
                    </p>
                </div>
                <button type="button" class="btn-primary btn-sm" ${restant === 0 ? 'disabled' : ''}
                    onclick='ajouterProduit(${JSON.stringify(medicament)})'>Ajouter</button>
            </div>
        `;
    }).join('');
}

function rafraichirResultats() {
    if (derniersResultats.length > 0) {
        rendreResultats();
    }
}

if (elements.recherche) {

    afficherRecherche();

    elements.recherche.addEventListener('input', function() {

        const valeur = this.value.trim();

        if (valeur === '') {
            derniersResultats = [];
            elements.resultats.innerHTML = `
                <p class="py-8 text-center text-sm text-ink-soft">
                    Tapez les premières lettres d'un médicament.
                </p>
            `;
            return;
        }

        fetch('/medicaments/search?q=' + encodeURIComponent(valeur))
            .then(reponse => reponse.json())
            .then(medicaments => {
                derniersResultats = medicaments;
                rendreResultats();
            })
            .catch(() => afficherMessage('La recherche a échoué. Vérifiez la connexion.', 'error'));
    });
}

// --- Validation ---------------------------------------------------------
function validerVente() {

    if (panier.length === 0) {
        afficherMessage('Le panier est vide.', 'warn');
        return;
    }

    fetch('/ventes/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ panier }),
    })
        .then(reponse => reponse.json().then(data => ({ ok: reponse.ok, data })))
        .then(({ ok, data }) => {

            // le serveur a refusé la vente : rien n'a été enregistré
            if (!ok) {
                afficherMessage(data.error || "La vente n'a pas pu être enregistrée.", 'error');
                return;
            }

            panier = [];
            afficherRecherche();

            window.location.href = '/ventes/' + data.vente_id;
        })
        .catch(() => afficherMessage("Erreur réseau : la vente n'a pas été enregistrée.", 'error'));
}

window.validerVente = validerVente;
