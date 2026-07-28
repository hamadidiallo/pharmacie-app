# GESTA PHARM

Application de gestion d'officine : stock de médicaments, ventes au comptoir,
tickets de caisse et tableau de bord.

## Stack

| | |
|---|---|
| Framework | Laravel 13, PHP 8.3 |
| Base de données | MySQL |
| Interface | Blade + Tailwind CSS 4 |
| Build | Vite 8 (polices auto-hébergées, aucun CDN) |
| PDF | dompdf via `barryvdh/laravel-dompdf` |
| Tests | Pest 4 (SQLite en mémoire) |

## Installation

```bash
composer setup
```

Cette commande installe les dépendances, crée le `.env`, génère la clé
applicative, joue les migrations et compile les assets. Renseignez ensuite les
accès MySQL dans `.env`, puis :

```bash
php artisan migrate --seed
```

Le seeder crée un compte `test@example.com` et cinq médicaments de démonstration.

## Développement

```bash
composer dev
```

Lance en parallèle le serveur PHP, le worker de queue et Vite.

## Tests

```bash
php artisan test
```

## Fonctionnalités

- **Authentification** — inscription, connexion, déconnexion.
- **Médicaments** — création, modification, suppression. Un produit identique
  (même nom, prix, description et date d'expiration) voit son stock cumulé au
  lot existant plutôt que dupliqué.
- **Ventes** — recherche de produits, panier, contrôle du stock. Le panier passe
  par la session : les prix sont toujours relus en base, jamais acceptés depuis
  le client. L'enregistrement est transactionnel — si un seul article manque,
  rien n'est écrit. Supprimer une vente restitue les quantités au stock.
- **Encaissement** — espèces, Mobile Money ou carte. En espèces, le montant reçu
  et la monnaie à rendre sont calculés puis conservés sur la vente et imprimés
  sur le ticket. Un montant reçu inférieur au total est refusé.
- **Tickets** — affichage au format 80 mm, impression directe et export PDF.
- **Tableau de bord** — recette du jour (avec écart vs la veille), semaine et
  mois, graphe du CA sur 7 jours, alertes de stock détaillées.
- **Statistiques** — top 5 des produits par période (semaine, mois, trimestre),
  panier moyen, nombre de tickets, surveillance du stock.

## Interface

Le design suit les tokens fournis dans
`Tableau de bord pharmacie/ecrans-tailwind/tokens` : palette santé (vert
officine), thème clair, ton clinique, barre latérale sombre de 236 px et topbar
de 64 px. IBM Plex Sans pour l'interface, IBM Plex Mono en chiffres tabulaires
pour tous les montants et quantités.

Les jetons (couleurs, polices, rayons, ombre) sont déclarés dans le bloc
`@theme` de [`resources/css/app.css`](resources/css/app.css), et les classes
composées (`panel`, `btn-primary`, `table-data`, `pill-*`, `notice-*`,
`nav-link`) juste en dessous via `@utility`.

Les boîtes de dialogue utilisent l'élément natif `<dialog>` piloté par
[`resources/js/app.js`](resources/js/app.js) — aucune dépendance JavaScript
externe, aucun CDN.

## Structure

```
app/Http/Controllers/   Auth, Dashboard, Medicament, Vente
app/Exceptions/         VenteException (erreurs métier de l'encaissement)
resources/views/        layout (app), layout-auth (invité), app/menu (sidebar),
                        auth, dashboard, medicaments, ventes
resources/js/           app.js (dialogues, sidebar), ventes/vente.js (panier)
tests/Feature/          Auth, Medicament, Vente, Pages
```

### Parcours d'une vente

```
ventes.create   panier construit côté client
      ↓         POST ventes.panier — contrôle du stock, panier stocké en session
ventes.paiement récapitulatif au prix de la base, choix du mode de paiement
      ↓         POST ventes.store — transaction, décrément du stock
ventes.show     ticket 80 mm, impression et PDF
```

## Support

+223 78 14 43 59 · +223 95 57 60 40 · ballaldialloubehd78@gmail.com

Développé par le cabinet de consulting Ballal Dialloubé.
