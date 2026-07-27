let panier = [];
// AJOUTER PRODUIT FONCTION
function ajouterProduit(medicament) {

    let message = document.getElementById('messageStock');

    let existant = panier.find(p => p.id === medicament.id);

    let quantite = existant ? existant.quantite + 1 : 1;

    // ❌ stock insuffisant (IMPORTANT : on compare avec stock initial)
    if (quantite > medicament.stock) {

        message.innerHTML = `
            <div class="alert alert-warning">
                Stock insuffisant (${medicament.stock} disponible)
            </div>
        `;

        return;
    }

    //  ajout
    if (existant) {
        existant.quantite++;
    } else {
        panier.push({
            id: medicament.id,
            nom: medicament.nom,
            prix: medicament.prix,
            stock: medicament.stock,
            quantite: 1
        });
    }

    renderPanier();
    document.getElementById('search').dispatchEvent(new Event('keyup'));

    message.innerHTML = `
        <div class="alert alert-success">
            Produit ajouté
        </div>
    `;
}
// AFFICHER PANIER JS
function renderPanier() {

    let html = "";
    let total = 0;

    panier.forEach((p, index) => {

        let sousTotal = p.prix * p.quantite;
        total += sousTotal;

        html += `
            <tr>

                <td>${p.nom}</td>

                <td>${p.prix}</td>

                <td class="text-center">
                    <button class="btn btn-danger btn-sm rounded-circle" onclick="modifierQte(${index}, -1)">-</button>
                    ${p.quantite}
                    <button class="btn btn-success btn-sm rounded-circle" onclick="modifierQte(${index}, 1)">+</button>
                </td>

                <td>${sousTotal}</td>

                <td>
                    <button class="btn btn-danger btn-sm"
                        onclick="supprimer(${index})">
                        SUPPRIMER
                    </button>
                </td>

            </tr>
        `;
    });

    document.getElementById('panier').innerHTML = html;
    document.getElementById('total').innerText = total;
}
// MODIFIER QUANTITE JS
function modifierQte(index, valeur) {

    let produit = panier[index];

    let nouvelleQuantite = produit.quantite + valeur;

    let message = document.getElementById('messageStock');

    //  quantité > stock
    if (nouvelleQuantite > produit.stock) {

        message.innerHTML = `
            <div class="alert alert-warning">
                Stock insuffisant. Disponible : ${produit.stock}
            </div>
        `;

        return;
    }

    //  quantité <= 0
    if (nouvelleQuantite <= 0) {

        panier.splice(index, 1);

    } else {

        produit.quantite = nouvelleQuantite;
    }

    renderPanier();
    document.getElementById('search').dispatchEvent(new Event('keyup'));
}
// SUPPRIMER PRODUIT FUNCTION JS
function supprimer(index) {

    panier.splice(index, 1);

    renderPanier();
}
window.ajouterProduit = ajouterProduit;
window.modifierQte = modifierQte;
window.supprimer = supprimer;
// JS RECHERCHE PRODUIT DANS LA PAGE VENTE
document.getElementById('search')

    .addEventListener('keyup', function () {

        let valeur = this.value;

        fetch('/medicaments/search?q=' + encodeURIComponent(valeur))

            .then(res => res.json())

            .then(data => {

                let html = "";

                if (data.length === 0) {
                    html = `
                <div class="alert alert-danger">

                    Médicament introuvable

                </div>
            `;
                }

                data.forEach(medicament => {
                    let produitPanier = panier.find(p => p.id == medicament.id);

                    let quantitePanier = produitPanier ? produitPanier.quantite : 0;

                    let stockRestant = medicament.stock - quantitePanier;

                    html += `
                <div class="card p-2 mb-2">

                    <div class="d-flex
                                justify-content-between
                                align-items-center">

                        <div>

                            <strong>
                                ${medicament.nom}

                            </strong>

                            <br>

                            Prix :
                             ${medicament.prix} FCFA
                            Stock restant : ${stockRestant}
                            <br> <br>


                        </div>

                        <button
                            class="btn btn-success" onclick='ajouterProduit(${JSON.stringify(medicament)})'>

                            Ajouter

                        </button>

                    </div>

                </div>
            `;
                });

                document
                    .getElementById('resultats')
                    .innerHTML = html;

            });

    });
// FONCTION JS VALIDER VENTE
function validerVente() {

    fetch('/ventes/store', {
        method: 'POST', 
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            panier: panier
        })
    })
        .then(res => res.json())
        .then(data => {

            window.location.href = '/ventes/' + data.vente_id;

        })

        .catch(err => console.log(err));
    then(data => {

        alert("Vente enregistrée");

        panier = [];
        renderPanier();

        document.getElementById('search').dispatchEvent(new Event('keyup'));

    });

}
window.validerVente = validerVente;
// FONCTION POUR DIMUNIER LE STOCK SANS RAFRAICHIR

