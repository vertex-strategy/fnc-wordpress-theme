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
- Deux extensions du répertoire WordPress.org, à installer en plus du paquet :
  - **Polylang** — gestion du bilingue français / anglais (requis).
  - **Secure Custom Fields** — recommandé : active les champs visuels d'édition des pages
    (le contenu reste consultable sans, mais l'édition assistée des pages composées en dépend).
- *(Recommandé)* un service **SMTP** pour l'envoi fiable des accusés de réception.

## Étapes

1. **Thème** : Apparence → Thèmes → *Ajouter* → *Téléverser un thème* →
   `forum-numerique-congo-theme.zip` → **Activer**.
   > L'archive du thème inclut les visuels de démonstration (~33 Mo). Si le
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

### (Option) Contenu de démonstration

Le thème fournit un jeu de contenu de démonstration reproductible. Depuis la racine
WordPress, avec WP-CLI :

```bash
wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-content.php
```

Ajouter l'argument `force` pour réécrire un contenu déjà en place. Ce contenu est marqué
« démo » et destiné à être remplacé par les informations réelles.

## Après l'installation

Deux menus apparaissent : **Réglages → FNC** (contenu du site) et
**Réglages → FNC (fonctionnalités)** (ouverture des inscriptions, affichage des actualités).
La suite — création des contenus, liaisons, traductions — est décrite pas à pas dans `GUIDE.md`.

---

*Template **Forum Numérique Congo** — thème + extensions « FNC Content Model » et « FNC Core ».*
*© 2026 **Grinso & Associés** — [www.grinso.io](https://www.grinso.io). Tous droits réservés.*
*Développé par **Vanel NGOYO ADOUMA**, Lead développeur.*
