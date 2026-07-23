# FNC WordPress Theme

Thème WordPress + plugin de contenu, dérivés de la maquette statique `docs/mockups/homepage-v2` du dépôt [`forum-numerique-congo`](https://github.com/vertex-strategy/forum-numerique-congo).

**Statut : étapes 2 et 3 du plan de mise en œuvre implémentées** (thème + plugin scaffoldés). L'étape 4 (branchement thème ↔ plugin) reste à faire.

## Positionnement

Produit vitrine parallèle, indépendant de l'application officielle Next.js/Payload CMS. Ce n'est pas un remplacement ni une migration du site officiel.

Voir la décision d'architecture complète : [ADR-007](https://github.com/vertex-strategy/forum-numerique-congo/blob/codex/m4-wcag-integration/docs/adr/ADR-007-template-wordpress-produit-parallele.md) dans le dépôt principal (statut : **Accepté**).

## Structure

```
wp-content/
├── themes/
│   └── fnc-wordpress-theme/
│       ├── style.css          # En-tête de thème + CSS complet (porté de docs/mockups/homepage-v2/index.html)
│       ├── functions.php      # Setup, enqueue, menu de repli, sélecteur de langue (Polylang ou repli statique)
│       ├── header.php         # <head>, navigation desktop + panneau mobile
│       ├── footer.php         # Pied de page
│       ├── front-page.php     # Page d'accueil — les 8 "moments" (M1 à M8)
│       ├── page-le-forum.php  # Page "Le Forum" (page-{slug}.php) — voir note ci-dessous
│       ├── index.php          # Gabarit de repli générique (obligatoire WordPress)
│       └── assets/
│           ├── js/main.js     # Comportements (nav au scroll, menu mobile, reveal)
│           └── images/        # Images utilisées par les pages (accueil + Le Forum)
└── plugins/
    └── fnc-content-model/
        ├── fnc-content-model.php
        └── includes/
            ├── post-types.php  # 6 custom post types
            ├── taxonomies.php  # fnc_categorie (hiérarchique), fnc_tag (plat)
            └── relations.php   # Relations post-to-post en meta (pas en taxonomie — voir note ci-dessous)
```

## Modèle de contenu (plugin)

Reflète les collections publiques Payload CMS du site officiel :

| Collection Payload | Custom Post Type WordPress |
|---|---|
| `Editions` | `fnc_edition` |
| `Sessions` | `fnc_session` |
| `Speakers` | `fnc_intervenant` |
| `Partners` | `fnc_partenaire` |
| `Publications` | `fnc_publication` |
| `News` | `fnc_actualite` |
| `Categories` / `Tags` | `fnc_categorie` (hiérarchique), `fnc_tag` (plat) — attachées à `fnc_session`, `fnc_publication`, `fnc_actualite` uniquement, par alignement sur [ADR-006](https://github.com/vertex-strategy/forum-numerique-congo/blob/codex/m4-wcag-integration/docs/adr/ADR-006-taxonomie-tags-categories-publications.md) du dépôt principal |
| `Pages` | Pages WP natives |

Explicitement hors périmètre (collections transactionnelles du site officiel, sans équivalent nécessaire ici) : `ContactSubmissions`, `Registrations`, `PartnershipRequests`, `Users`, `Media`.

### Relations entre post types

Les relations entité-à-entité (une session appartient à une édition, une session a plusieurs intervenants, une publication peut être liée à une édition) sont stockées en **post meta** (ID ou tableau d'ID), pas via une taxonomie WordPress. Une taxonomie dupliquerait ces entités en un vocabulaire parallèle à synchroniser manuellement, alors qu'`intervenant` et `edition` existent déjà comme post types — les référencer par ID évite cette duplication.

*(Ce choix précise, sans le contredire, le principe de la Décision 2 de l'ADR-007 : `intervenant` reste un post type indépendant, pas une taxonomie décorative — l'exemple de taxonomie mentionné dans l'ADR ne s'applique qu'à `fnc_categorie`/`fnc_tag`, de véritables vocabulaires partagés.)*

Ceci reste conforme au principe « zéro dépendance tierce » (hors multilinguisme) : aucun plugin de champs, uniquement des meta boxes natives (`add_meta_box`, `register_post_meta`).

## Pages intérieures (au-delà de l'accueil)

`page-le-forum.php` porte `docs/mockups/homepage-v2/interior/le-forum.html` du dépôt `forum-numerique-congo`. **Statut de la source à la date d'intégration :** ce fichier existe dans le dossier du projet principal mais n'était pas committé sur la branche de l'ADR-007 (travail en cours d'un autre agent, non fusionné) — intégré ici à la demande explicite du Décideur, malgré ce statut. Si le fichier source évolue avant d'être committé en amont, ce gabarit devra être resynchronisé.

Le gabarit s'applique automatiquement à toute Page WordPress dont le slug est `le-forum` (hiérarchie de templates `page-{slug}.php`). Les autres pages intérieures de la maquette (programme, intervenants, éditions, etc.) suivront le même principe au fur et à mesure de leur intégration.

## Multilinguisme

Non encore intégré dans ce scaffold. Décision actée (ADR-007, Décision 2 amendée) : Polylang (ou équivalent gratuit/GPL) sera ajouté comme dépendance ciblée, réservée exclusivement au multilinguisme — à confirmer précisément lors du branchement thème ↔ plugin (étape 4).

## Prochaines étapes

Voir le plan de mise en œuvre de l'ADR-007 :
1. ~~Créer le dépôt~~ (fait)
2. ~~Scaffolder la structure thème~~ (fait — page d'accueil uniquement, seule page committée/validée dans `homepage-v2` à ce jour)
3. ~~Scaffolder le plugin~~ (fait — CPTs, taxonomies, relations en meta ; Polylang non encore intégré)
4. Brancher le thème sur les données du plugin (remplacer le contenu statique de `front-page.php` par des requêtes sur les CPTs — éditions, sessions, intervenants), intégrer Polylang
