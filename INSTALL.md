# Forum Numérique Congo — Installation

Template institutionnel bilingue (français / anglais) du Forum Numérique Congo.
Le paquet contient le **thème** et deux **extensions**.

## Contenu du paquet

| Fichier | Rôle |
|---|---|
| `forum-numerique-congo-theme.zip` | Le thème : apparence, gabarits de page, animations. |
| `fnc-content-model.zip` | Extension : types de contenu (éditions, intervenants, sessions, publications, partenaires, actualités), taxonomies et relations. |
| `fnc-core.zip` | Extension : réglages du site, formulaires, mesure d'audience, édition des pages. |
| `GUIDE.md` | Guide complet d'administration et d'édition. |

## Prérequis

- **WordPress** 6.4 ou plus récent, **PHP** 8.0 ou plus récent.
- Extensions du répertoire WordPress.org, à installer en plus du paquet :
  - **Polylang** — gestion du bilingue français / anglais (requis).
  - **Secure Custom Fields** — recommandé : active les champs visuels d'édition des pages
    (le contenu reste consultable sans, mais l'édition assistée des pages composées en dépend).
  - **All in One SEO (AIOSEO)** — recommandé pour le référencement (titres, descriptions,
    image de partage, plan de site). **Le thème s'adapte automatiquement** : dès qu'AIOSEO
    (ou Yoast) est actif, il lui laisse `<title>` / meta description / OpenGraph / canonique /
    schema Organization+WebSite et ne conserve que le schema **Event** — aucune balise en
    double. Sans plugin SEO, le thème assure lui-même un référencement de base. *(Voir GUIDE.md §1.)*
- *(Recommandé)* un service **SMTP** pour l'envoi fiable des accusés de réception.

## Étapes

1. **Thème** : Apparence → Thèmes → *Ajouter* → *Téléverser un thème* →
   `forum-numerique-congo-theme.zip` → **Activer**.
   > L'archive du thème inclut les visuels de démonstration (~37 Mo). Si le
   > téléversement échoue, augmentez `upload_max_filesize` / `post_max_size`
   > (php.ini) ou installez le thème par copie du dossier dans `wp-content/themes/`.
2. **Extensions** (Extensions → *Ajouter* → *Téléverser une extension*), à activer **dans cet ordre** :
   1. `fnc-content-model.zip`
   2. `fnc-core.zip`
   3. **Polylang** (depuis le répertoire WordPress.org)
   4. **Secure Custom Fields** (depuis le répertoire WordPress.org, recommandé)
3. **Langues** : dans Polylang, définir **Français** (par défaut) puis **Anglais**.
4. **Permaliens** : Réglages → Permaliens → **« Titre de la publication »**.
5. **Réglages du site** : renseigner l'identité, les coordonnées, les logos et le menu
   dans **Réglages → FNC** (voir `GUIDE.md`, §3).

### Contenu de démonstration (« starter kit »)

Pour découvrir la structure du site sur une installation neuve, le thème installe
en un clic un jeu de contenu de démonstration complet — éditions, intervenants,
sessions, partenaires et publications, avec leurs images et leurs relations,
**bilingue français + anglais** si Polylang est actif.

1. Terminer les étapes ci-dessus (thème + extensions + Polylang fr/en activés).
2. Aller dans **Apparence → Contenu de démonstration**.
3. Cliquer sur **« Importer le contenu de démonstration »**.

L'opération est **sûre et rejouable** (aucun doublon : relancer met à jour). Le
contenu est destiné à être remplacé par les informations réelles ; supprimez les
éléments de démonstration ou modifiez-les directement.

> **Alternative développeur (WP-CLI).** Depuis la racine WordPress :
> ```bash
> wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-dataset.php
> ```
> Puis vider le cache : `wp cache flush`.

### Après un import (ou une mise à jour) : régénérer les miniatures

Le thème génère des tailles d'image dédiées (carte, couverture et **image de
partage social 1200×630**) et sert des images **WebP** allégées. Après l'import de
démonstration — ou après une mise à jour depuis une version antérieure —
**régénérez les miniatures** pour que ces tailles s'appliquent aux médias déjà en
base :

- avec une extension type **« Regenerate Thumbnails »**, ou
- en WP-CLI : `wp media regenerate --yes`.

### Mettre à jour le kit (version existante)

1. Réinstallez le thème et les extensions (mêmes étapes) et **réactivez FNC Content
   Model et FNC Core**.
2. **Permaliens** : Réglages → Permaliens → *Enregistrer les modifications* (régénère
   les règles de réécriture — utile au plan de site XML).
3. **Miniatures** : régénérez-les (voir ci-dessus).
4. *(Uniquement si vous utilisez le contenu de démonstration)* relancez l'import en
   mode **force** pour recomposer les pages institutionnelles et les mises en avant :
   ```bash
   wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-content.php force
   wp cache flush
   ```

## Après l'installation

Deux menus apparaissent : **Réglages → FNC** (contenu du site) et
**Réglages → FNC (fonctionnalités)** (ouverture des inscriptions, affichage des actualités).
La suite — création des contenus, liaisons, traductions — est décrite pas à pas dans `GUIDE.md`.

---

*Template **Forum Numérique Congo** — thème + extensions « FNC Content Model » et « FNC Core ».*
*© 2026 **Grinso & Associés** — [www.grinso.io](https://www.grinso.io). Tous droits réservés.*
*Développé par **Vanel NGOYO ADOUMA**, Lead développeur.*
