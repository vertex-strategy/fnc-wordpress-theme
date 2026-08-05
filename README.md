# Forum Numérique Congo — template WordPress (référence développeur)

Thème **« Forum Numérique Congo »** + extensions **« FNC Content Model »** et **« FNC Core »**.
Produit vitrine **parallèle** au site officiel Next.js/Payload (source de vérité), pas une
migration — voir [ADR-007](https://github.com/vertex-strategy/forum-numerique-congo/blob/main/docs/adr/ADR-007-template-wordpress-produit-parallele.md).

Ce document est une **référence d'architecture**. Pour l'installation → `INSTALL.md` ; pour
l'édition/administration → `GUIDE.md` ; pour l'état d'intégration vs Next →
`docs/design/wordpress-integration-audit.md` (dépôt principal).

Principe directeur : **zéro dépendance tierce** (hors Polylang pour le multilinguisme et,
optionnellement, un plugin SEO) ; **la DA vit dans le code** (l'éditeur administre le contenu,
jamais la mise en forme) ; **aucune donnée fictive publiée comme réelle** (états « à confirmer »).

---

## 1. Architecture

Trois composants, responsabilités séparées :

| Composant | Rôle | Emplacement |
|---|---|---|
| **fnc-content-model** (extension) | Modèle de données : CPT, taxonomies, relations (méta), statut *archivé*, compat Polylang | `wp-content/plugins/fnc-content-model/` |
| **fnc-core** (extension) | Logique du site : réglages, données dérivées, données structurées, feature flags, consentement/Matomo, soumissions, archétypes de pages | `wp-content/plugins/fnc-core/modules/` |
| **fnc-wordpress-theme** (thème) | Présentation : gabarits, blocs Gutenberg, page d'accueil éditable, héros, SEO par page, formulaires, i18n de l'UI | `wp-content/themes/fnc-wordpress-theme/` |

Le thème **consomme** les extensions mais dégrade proprement si elles sont absentes (tous les
appels aux fonctions du plugin sont gardés par `function_exists()`).

```
themes/fnc-wordpress-theme/
├── functions.php            # bootstrap : requires inc/*, setup, menus, i18n, filtre template traduit
├── header.php / footer.php  # chrome (nav, sélecteur langue, CTA conditionnel ; footer + coordonnées)
├── front-page.php           # accueil, storyboard figé M1→M8
├── page-{slug}.php          # gabarits de page par slug (le-forum, contact, edition-en-cours, …)
├── archive-fnc_{cpt}.php    # archives des CPT (edition, session, intervenant, publication, actualite)
├── single-fnc_{cpt}.php     # fiches détaillées
├── hero-pcb.php             # partial : filet PCB animé (.pcb-band)
├── inc/                     # sous-systèmes du thème (voir §5)
├── tools/                   # pipeline de données : build-dataset.mjs, dataset.json, seed-dataset.php, seed-settings.php
├── languages/              # .pot/.po/.mo/.l10n.php (en_GB)
└── assets/                  # css (kit DA extrait de globals.css), js/main.js, images
plugins/fnc-content-model/includes/  # post-types, taxonomies, relations, statuses, polylang
plugins/fnc-core/modules/            # settings, derived-data, structured-data, feature-flags, consent-matomo, submissions, page-archetypes
```

---

## 2. Modèle de contenu (fnc-content-model)

6 CPT (miroir des collections publiques Payload) — `includes/post-types.php` :

| CPT | Slug d'archive | Collection Payload |
|---|---|---|
| `fnc_edition` | `editions` | Editions |
| `fnc_session` | `programme` | Sessions |
| `fnc_intervenant` | `intervenants` | Speakers |
| `fnc_publication` | `ressources` | Publications |
| `fnc_partenaire` | *(archive désactivée, vue = Page `partenaires`)* | Partners |
| `fnc_actualite` | `actualites` | News |

Les **Pages** WordPress natives portent les contenus éditoriaux (le-forum, contact, légales…).

**Taxonomies** (`includes/taxonomies.php`) : `fnc_categorie` (hiérarchique), `fnc_tag`, `fnc_profil`
(official/expert/host — badge & filtre, **ne trie pas**), `fnc_pays`, `fnc_niveau_partenariat`
(institutionnel/organisateur/soutien/sponsor).

**Relations = post-meta** (ID ou tableau d'ID), pas de taxonomie miroir. Constantes + métaboxes
+ `register_post_meta` dans `includes/relations.php`. Méta principales :

| Objet | Méta | Notes |
|---|---|---|
| Édition | `_fnc_edition_active`, `_status` (upcoming/current/past), `_year`, `_theme`, `_start_date`, `_end_date`, `_location`, `_is_special`, `_special_note` | une seule `current` (résolveur) |
| Session | `_fnc_session_edition`, `_speakers[]`, `_moderator`, `_type`, `_start`/`_end`/`_time`, `_room`, `_jour`(int), `_objectives[]`, `_note` | speakers = tableau d'ID (single) |
| Intervenant | `_fnc_speaker_title`(civilité), `_org`, `_country`(« A / B »), `_protocol_order`, **`_sort_index`**, **`_home_featured`**, **`_home_featured_order`**, `_links[]`, `_image_right`, `_image_expires` | ordre + promotion carrousel |
| Partenaire | `_fnc_partenaire_site`, **`_sort_index`**, `_editions[]`, `_participations[]`(edition+niveau) | ordre M6 + page |
| Publication | `_fnc_publication_edition`, `_type`, `_media_url`, `_file` | |
| SEO (tous) | `_fnc_seo_title`, `_seo_description`, `_seo_noindex` | métabox, `inc/seo.php` |

**Statut `archived`** (`includes/statuses.php`) : `register_post_status('archived')` non public
(retiré du front, conservé) pour les 6 CPT ; exposé dans les menus de statut
(`fnc_cm_archivable_types()`, `fnc_cm_register_archived_status()`).

**Multilinguisme** (`includes/polylang.php`) : les 6 CPT + 5 taxonomies déclarés traduisibles par
code (`pll_get_post_types` / `pll_get_taxonomies`), sans effet si Polylang est absent.

---

## 3. Gabarits (hiérarchie WordPress)

- **Accueil** : `front-page.php` — storyboard **figé** M1→M8 (voir §6).
- **Pages** : `page-{slug}.php` s'applique par slug ; à défaut `page.php` (mode blocs OU colonne
  de lecture). Une page composée de **blocs FNC** (`fnc_page_has_blocks()`) éclipse le contenu de
  démonstration du gabarit dédié.
- **Archives** : `archive-fnc_{cpt}.php` (boucle `WP_Query` sur le contenu publié).
- **Fiches** : `single-fnc_{cpt}.php`.
- **Héros** : `fnc_render_opening_hero()` (`.opening`, photo, pages éditoriales/listes),
  `fnc_render_pagehead()` (`.page-head` sobre, fiches détail), `fnc_render_pageheader()` /
  `fnc_render_legal_header()` (registre C, le-forum/contact/légales). Filet PCB via `hero-pcb.php`.
- **Pages traduites** : `fnc_translated_page_template()` (`template_include`) applique
  `page-{slug-FR}.php` à une page EN dont le slug diffère (`-en`) — évite le conflit Polylang.

---

## 4. Sous-systèmes fnc-core (API principale)

Toutes ces fonctions sont **publiques** (préfixe `fnc_`, gardées `function_exists`).

**Réglages** (`fnc-settings.php`) — miroir du Global Settings Payload, stockés en un seul
`option('fnc_settings')` :
- `fnc_get_setting($key, $default)` / `fnc_get_setting_i18n($key)` (traduit via `pll__`) /
  `fnc_get_setting_image_url($key, $size)`.
- `fnc_site_name()` (= réglage `officialName` sinon blogname), `fnc_social_links()` (filtrées).
- **Accesseurs anti-fictif** : `fnc_public_email/phone/address()`, `fnc_is_placeholder_phone()`
  (motif `^\+?2420?60+$`), `fnc_is_usable_social()` (rejette le LinkedIn nu).

**Données dérivées** (`fnc-derived-data.php`) — le cœur métier :
- `fnc_edition_participants($edition=0)` : participants (≥1 session publiée de l'édition en cours),
  **triés protocolOrder → `_sort_index` → titre**.
- `fnc_home_voices($count=10, $edition=0)` : **promotion** (`_home_featured` d'abord, triés par
  `_home_featured_order`) puis reste, plafonné.
- `fnc_edition_countries()` (split `/`, dédup, Congo en tête à défaut d'ordre CMS),
  `fnc_session_speaker_ids()`, `fnc_current_edition_id()` / `fnc_resolve_active_edition()`.
- **RÈGLE 7** : `fnc_speaker_portrait($id)` ne renvoie l'`<img>` que si `_image_right='obtenu'`
  et non expiré ; sinon le gabarit rend un monogramme (`fnc_speaker_initials()`,
  `fnc_speaker_display_name()`).

**Données structurées** (`fnc-structured-data.php`) : `fnc_sd_output()` (wp_head) émet
`Organization`+`WebSite` (partout) + `Event` (accueil, si nom & date de début). **Anti-doublon** :
`fnc_sd_seo_plugin_active()` supprime Organization/WebSite si AIOSEO/Yoast est actif (garde l'Event).

**Feature flags** (`fnc-feature-flags.php`) : `fnc_registration_enabled()` (CTA conditionnel) +
flag actualités. Bascules d'administration (« Réglages → FNC (fonctionnalités) »), pas des variables
d'environnement.

**Consentement / Matomo** (`fnc-consent-matomo.php`) : `fnc_matomo_head()` — file `_paq` dans
l'ordre `requireCookieConsent → (localStorage 'fnc.consent.analytics') → setDoNotTrack,true →
trackPageView → enableLinkTracking`, cookie **seulement après « oui »**. URL/siteId via réglages
(`fnc_matomo_url()`/`fnc_matomo_site_id()`), **jamais codés en dur**.

**Soumissions** (`fnc-submissions.php`) : réception contact/inscription/partenariat → CPT interne,
accusé de réception, `do_action('fnc_submission_stored')`.

---

## 5. Sous-systèmes du thème (`inc/`)

| Fichier | Rôle | Points d'entrée |
|---|---|---|
| `customizer.php` | Réglages FNC (identité, coordonnées, logos, SEO défaut, ordre pays) | panneau Customizer |
| `blocks.php` | 14 blocs Gutenberg **dynamiques** (rendu PHP, DA figée) + rubriques pratiques | `fnc_render_practical_info()`, `fnc/*` |
| `homepage.php` | Storyboard M1→M8 administrable (Customizer, ~60 réglages) | `fnc_home_setting()` |
| `hero-settings.php` / `section-titles.php` | Héros + titres de section éditables des pages-listes | Customizer |
| `seo.php` | SEO par page (métabox + cascade) + `<head>` | `fnc_head_meta()`, **`fnc_seo_delegated()`** |
| `forms.php` | Rendu des formulaires + coordonnées | `fnc_form_fields()`, `fnc_render_contact_coordinates()` |
| `page-sections.php` | Adaptateur archétypes ACF/SCF → rendu de blocs | |
| `demo-import.php` | Page « Contenu de démonstration » (import + retrait) | Apparence → menu |
| `admin-guide.php` | Widget « par où éditer quoi » (tableau de bord) | |

Chrome & nav (`functions.php`) : `fnc_default_menu_items()` (6 items, ordre du Forum),
`fnc_menu_is_active()` (`aria-current`, ignore `#`), `fnc_page_url()`/`fnc_archive_url()`,
`fnc_language_switcher()` (séparateur `|`).

---

## 6. Accueil (storyboard figé M1→M8)

`front-page.php`, ordre non modifiable. M1 ouverture (CTA `/programme` + `/inscription` si flag) ·
M2 mission · **M3 carrousel « Les voix »** (eyebrow dynamique « Les voix · N intervenants, N pays »
= `count(fnc_edition_participants())` / `count(fnc_edition_countries())` ; `fnc_home_voices()`,
plafond réglable 4-12, défaut 10 ; JS `main.js` : auto 4000 ms, pause survol/focus,
`prefers-reduced-motion`) · M4 territoire · M5 programme · **M6 partenaires** (publiés + logo, tri
`_sort_index` ; **3 grands en tête** = écart MOA vs 2+4 du Next) · M7 archives · **M8** compte à
rebours (`daysToForum` depuis la date de début de l'édition en cours).

---

## 7. Données & starter kit (`tools/`)

Pipeline en 2 temps, reproductible et versionné :

1. **`build-dataset.mjs`** (Node/`tsx`) lit la source Next (`src/data/*`, corpus markdown) →
   écrit **`dataset.json`** (committé, donc seed autonome). Les `themeEn` des éditions passées et
   le thème FR 2024 canonique sont des **overrides MOA** (`PAST_THEME_EN`, `THEME_FR_OVERRIDE`).
2. **`seed-dataset.php`** expose **`fnc_ds_run_seed()`** (idempotent, clé `_fnc_seed_legacy`) :
   - `fnc_ds_ensure_settings()` — nom du site, coordonnées, SEO par défaut, **image OG** ;
   - `fnc_ds_ensure_pages()` — crée/adopte les **pages éditoriales** (mot-du-président enfant de
     le-forum ; pages EN en slug `-en` liées Polylang) ;
   - CPT + **traductions EN** liées FR↔EN (`fnc_ds_upsert_en()`, relations remappées).
   - `fnc_ds_remove_seed()` retire proprement tout le contenu marqué démo + les médias importés.

Exposé en un clic : **Apparence → Contenu de démonstration** (`inc/demo-import.php`, import + bouton
de retrait). En CLI : `wp eval-file …/tools/seed-dataset.php` (+ `wp cache flush`).

> Après un seed/retrait : vider le cache objet + transients (Module « données dérivées » met en
> cache participants/facettes). Après changement de slug/CPT : `wp rewrite flush`.

---

## 8. i18n / Polylang

- Locales `fr` (défaut) / `en=en_GB`. UI traduite via `__()` (thème 509 chaînes, plugin 98) ;
  chargement direct du `.mo` par locale sur `init`+`wp` (`fnc_load_textdomain()` — contourne un
  piège de `load_theme_textdomain` et le timing Polylang).
- Réglages Customizer localisables via le module « Traductions des chaînes » (`fnc_get_setting_i18n`,
  `fnc_pll`, `fnc_register_pll_strings`).
- **Contenu** : CPT traduits par le seed (roleEn/titleEn/themeEn). **Décision MOA** : les slugs de
  section EN restent en FR (pas de `/speakers`, `/partners`) ; les pages EN utilisent le gabarit FR
  via `fnc_translated_page_template()`.
- Épingler **WordPress 6.x** (le tag `latest` a livré une 7.0.x cassant la locale par requête).

---

## 9. Points d'extension (hooks)

| Hook | Type | Usage |
|---|---|---|
| `fnc_settings` | filtre | surcharger les réglages résolus |
| `fnc_hero_image_base_url` | filtre | base d'URL des images de héros par route |
| `fnc_page_hero_defaults` | filtre | valeurs de héros par défaut d'une page/route |
| `fnc_matomo_url` / `fnc_matomo_site_id` / `fnc_matomo_should_track` | filtres | infra de mesure (jamais en dur) |
| `fnc_consent_strings` | filtre | libellés du bandeau de consentement |
| `fnc_speaker_image_allowed` | filtre | surcharge de la RÈGLE 7 |
| `fnc_submission_accepts` | filtre | validation des soumissions |
| `fnc_privacy_url` | filtre | URL de la politique de confidentialité |
| `fnc_submission_stored` | action | après enregistrement d'une soumission |

Conventions : préfixe `fnc_` ; toute fonction inter-composant gardée par `function_exists()` ;
`register_post_meta(..., show_in_rest => true)` pour l'exposition REST/éditeur.

---

## 10. Développement local & packaging

- **Pile locale** : conteneurs `fnc-wordpress-theme_{wordpress,wpcli,db}` (podman/compose,
  WordPress 6.8 épinglé). Front sur `:8090`. Le thème est bind-mounté ; l'extension **fnc-core**
  peut nécessiter `podman cp` de ses fichiers modifiés vers les conteneurs (fnc-content-model est
  bind-mounté). Après import d'images : `chmod 0777 wp-content/uploads` (UID podman).
- **Vérification** : `php -l` (via conteneur) + contrôle du rendu réel (`curl`/navigateur), logs
  conteneur sans fatal/notice/warning. « Le rendu est la vérité » — mesurer, pas supposer.
- **Packaging** : `python build-package.py` → `dist/forum-numerique-congo-template-{version}/`
  (thème + 2 extensions + `INSTALL.md`/`GUIDE.md`) et une archive conteneur. Séparateurs ZIP `/`
  (décompression correcte sur tout hébergement). Versions alignées : en-tête `style.css`,
  `FNC_THEME_VERSION`, en-têtes+defines des 2 extensions, `VERSION` du build.

> À installer côté WordPress : les **zips individuels** (thème + 2 extensions), **pas** l'archive
> conteneur `…-template-{version}.zip` (qui n'a pas de `style.css` à sa racine).

---

## 11. Contraintes & dette connues

- **Zéro dépendance** : champ fichier des ressources et drapeaux de pays = champs **URL**
  (téléversement Médiathèque puis copie de l'adresse), pas de sélecteur de média ; texte riche des
  blocs = `wpautop`, pas un éditeur Lexical.
- **Statut `archived`** : non public (WordPress n'a pas d'« archivé » natif) ; réglable via
  Modification rapide / éditeur classique (pas d'intégration complète au panneau Gutenberg).
- **Image OG par défaut** : un visuel du thème seedé ; remplacer par un 1200×630 dédié en prod
  (ou via l'assistant AIOSEO). `x-default` hreflang non émis par Polylang.
- **Écarts VOLONTAIRES (MOA)** vs Next : M6 « 3 en tête », slugs de section EN en FR, feature flags
  en bascules d'administration. Voir `docs/design/wordpress-integration-audit.md` pour l'état
  d'intégration détaillé (règle par règle).

---

*Template **Forum Numérique Congo** — thème + extensions « FNC Content Model » et « FNC Core ».*
*© 2026 **Grinso & Associés** — [www.grinso.io](https://www.grinso.io). Tous droits réservés.*
*Développé par **Vanel NGOYO ADOUMA**, Lead développeur.*
