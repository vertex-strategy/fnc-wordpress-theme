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
│       ├── page-contact.php   # Page "Contact" (formulaire non fonctionnel, fidèle à la source)
│       ├── archive-fnc_edition.php      # Archive des éditions — DYNAMIQUE (vraies données du plugin)
│       ├── archive-fnc_publication.php  # Archive des publications — DYNAMIQUE (vraies données du plugin)
│       ├── index.php          # Gabarit de repli générique (obligatoire WordPress)
│       └── assets/
│           ├── js/main.js     # Comportements (nav au scroll, menu mobile, reveal)
│           └── images/        # Images utilisées par les pages
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

| Page | Gabarit | Source (`forum-numerique-congo`) | Type de rendu |
|---|---|---|---|
| Le Forum | `page-le-forum.php` | `docs/mockups/homepage-v2/interior/le-forum.html` | Statique (contenu exemple) |
| Contact | `page-contact.php` | `docs/mockups/homepage-v2/contact.html` (rendu par `site.js`) | Statique (formulaire non fonctionnel) |
| Éditions | `archive-fnc_edition.php` | `docs/mockups/homepage-v2/editions.html` (rendu par `site.js`) | **Dynamique** — vraies données du plugin |
| Publications | `archive-fnc_publication.php` | `docs/mockups/homepage-v2/publications.html` (rendu par `site.js`) | **Dynamique** — vraies données du plugin |

**Statut des sources à la date d'intégration :** ces 4 fichiers existent dans le dossier du projet principal mais n'étaient pas committés sur la branche de l'ADR-007 (travail en cours d'un autre agent, non fusionné) — intégrés ici à la demande explicite du Décideur, malgré ce statut. Si les fichiers source évoluent avant d'être committés en amont, les gabarits correspondants devront être resynchronisés.

`contact.html`/`editions.html`/`publications.html` ne sont pas des pages HTML statiques comme `le-forum.html` : leur contenu réel est généré côté client par `site.js` (à partir de `data-page` sur `<body>`), un système de gabarits JS partagé qui couvre l'ensemble des 27 pages de cette génération de la maquette (`docs/mockups/homepage-v2/site.css` + `site.js`). Les classes CSS ajoutées pour ces 3 pages (`.hero`, `.section-head`, `.card`/`.grid`, `.agenda`, `.toolbar`, `.empty`, `.form`, `.cta-band`) viennent de ce système et coexistent avec celles déjà utilisées par l'accueil et Le Forum (mêmes tokens de couleur, nommage différent).

**Éditions et Publications sont volontairement dynamiques**, pas de simples portages statiques : leurs URLs (`/editions/`, `/publications/`) correspondent exactement aux archives déjà enregistrées pour `fnc_edition`/`fnc_publication` (`has_archive` + `rewrite`), donc WordPress les sert nativement via ces gabarits `archive-{post_type}.php`, avec une vraie boucle `WP_Query` sur le contenu publié. La section "timeline" d'exemple de `editions.html` (libellés inventés type "Édition 1", "Édition 2") n'a pas été reproduite, pour ne pas afficher de contenu fictif à la place de vraies données.

Le gabarit `page-le-forum.php`/`page-contact.php` s'applique automatiquement à toute Page WordPress du slug correspondant (hiérarchie de templates `page-{slug}.php`). Les autres pages intérieures de la maquette (programme, intervenants, etc.) suivront le même principe au fur et à mesure de leur intégration.

## Multilinguisme

Non encore intégré dans ce scaffold. Décision actée (ADR-007, Décision 2 amendée) : Polylang (ou équivalent gratuit/GPL) sera ajouté comme dépendance ciblée, réservée exclusivement au multilinguisme — à confirmer précisément lors du branchement thème ↔ plugin (étape 4).

## Prochaines étapes

Voir le plan de mise en œuvre de l'ADR-007 :
1. ~~Créer le dépôt~~ (fait)
2. ~~Scaffolder la structure thème~~ (fait — page d'accueil uniquement, seule page committée/validée dans `homepage-v2` à ce jour)
3. ~~Scaffolder le plugin~~ (fait — CPTs, taxonomies, relations en meta ; Polylang non encore intégré)
4. Brancher le thème sur les données du plugin — **amorcé** : `archive-fnc_edition.php` et `archive-fnc_publication.php` interrogent déjà les vraies données ; reste à faire pour `front-page.php` (éditions/sessions/intervenants sur l'accueil) et à intégrer Polylang
