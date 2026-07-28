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
- **Ventes** — recherche de produits, panier, contrôle du stock, encaissement.
  L'enregistrement est transactionnel : si un seul article manque, rien n'est
  écrit. Les prix sont toujours relus en base, jamais acceptés depuis le client.
  Supprimer une vente restitue les quantités au stock.
- **Tickets** — affichage au format 80 mm, impression directe et export PDF.
- **Tableau de bord** — recette du jour, semaine, mois et trimestre, ruptures,
  stocks faibles, expirations sous 30 jours, top 5 des produits.

## Interface

Le thème s'appuie sur deux matériaux du métier : le papier d'ordonnance et le
ticket de caisse. Le vert `officine` est celui de la croix des pharmacies. Les
chiffres sont composés en monospace tabulaire pour s'aligner en colonnes, et les
filets pointillés reprennent le trait des reçus thermiques.

Les jetons de design (couleurs, polices) sont déclarés dans le bloc `@theme` de
[`resources/css/app.css`](resources/css/app.css), et les classes composées
(`btn-primary`, `card-officine`, `table-officine`, `badge-*`, `notice-*`) juste
en dessous via `@utility`.

Les boîtes de dialogue utilisent l'élément natif `<dialog>` piloté par
[`resources/js/app.js`](resources/js/app.js) — aucune dépendance JavaScript
externe.

## Structure

```
app/Http/Controllers/   Auth, Dashboard, Medicament, Vente
app/Exceptions/         VenteException (erreurs métier de l'encaissement)
resources/views/        layout, app/menu, auth, dashboard, medicaments, ventes
resources/js/           app.js (dialogues, menu), ventes/vente.js (panier)
tests/Feature/          Auth, Medicament, Vente, Pages
```

## Support

+223 78 14 43 59 · +223 95 57 60 40 · ballaldialloubehd78@gmail.com

Développé par le cabinet de consulting Ballal Dialloubé.
