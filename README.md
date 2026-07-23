# FNC WordPress Theme

Thème WordPress + plugin de contenu, dérivés de la maquette statique `docs/mockups/homepage-v2` du dépôt [`forum-numerique-congo`](https://github.com/vertex-strategy/forum-numerique-congo).

**Statut : amorçage.** Ce dépôt vient d'être scaffoldé — aucun code de thème ni de plugin n'est encore implémenté.

## Positionnement

Produit vitrine parallèle, indépendant de l'application officielle Next.js/Payload CMS. Ce n'est pas un remplacement ni une migration du site officiel.

Voir la décision d'architecture complète : [ADR-007](https://github.com/vertex-strategy/forum-numerique-congo/blob/codex/m4-wcag-integration/docs/adr/ADR-007-template-wordpress-produit-parallele.md) dans le dépôt principal (statut : Proposé).

## Structure

```
wp-content/
├── themes/
│   └── fnc-wordpress-theme/   # Thème — dérivé de docs/mockups/homepage-v2
└── plugins/
    └── fnc-content-model/     # Plugin custom, zéro dépendance tierce
                                # (register_post_type / register_taxonomy / register_post_meta)
```

## Modèle de contenu (plugin)

Reflète les collections publiques Payload CMS du site officiel :

| Collection Payload | Custom Post Type WordPress |
|---|---|
| `Editions` | `edition` |
| `Sessions` | `session` |
| `Speakers` | `intervenant` |
| `Partners` | `partenaire` |
| `Publications` | `publication` |
| `News` | `actualite` |
| `Categories` / `Tags` | Taxonomies WP dédiées |
| `Pages` | Pages WP natives |

Explicitement hors périmètre (collections transactionnelles du site officiel, sans équivalent nécessaire ici) : `ContactSubmissions`, `Registrations`, `PartnershipRequests`, `Users`, `Media`.

## Prochaines étapes

Voir le plan de mise en œuvre de l'ADR-007 :
1. ~~Créer le dépôt~~ (fait)
2. Scaffolder la structure thème (hiérarchie de templates WP à partir des pages de `homepage-v2`)
3. Scaffolder le plugin (CPTs, taxonomies, meta)
4. Brancher le thème sur les données du plugin
