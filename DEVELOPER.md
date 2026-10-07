# Forum Numérique Congo — Manuel du développeur

Référence d'architecture et guide de prise en main pour **intervenir sur le code** du template
(corrections, extensions, intégration dans un WordPress existant). Il complète les trois autres
documents :

| Document | Public | Contenu |
|---|---|---|
| `INSTALL.md` | Intégrateur | Installation du paquet, prérequis, mise à jour, durcissement serveur. |
| `GUIDE.md` | Éditeur / administrateur | Où cliquer pour administrer le contenu, sans code. |
| **`DEVELOPER.md`** *(ce document)* | **Développeur** | **Architecture, modèle de réglages, API, hooks, build, « faire une correction ».** |
| `README.md` | Développeur | Résumé d'architecture (condensé de ce manuel). |

> **Principes directeurs.** (1) **La mise en forme vit dans le code** — l'administration gère le
> *contenu*, jamais la DA. (2) **Zéro dépendance tierce obligatoire** hors Polylang (bilingue) ;
> un plugin SEO et une extension de champs type ACF sont *optionnels* et le code **dégrade
> proprement** sans eux. (3) **Aucune donnée fictive publiée comme réelle** : un champ non
> renseigné n'est pas affiché. (4) **Le rendu est la vérité** : on vérifie une correction sur le
> site réel (rendu + journaux), on ne suppose pas.

---

## 1. Architecture d'ensemble

Trois composants, responsabilités séparées :

| Composant | Rôle | Emplacement |
|---|---|---|
| **FNC Content Model** (extension) | **Modèle de données** : types de contenu, taxonomies, relations (post-meta), statut *archivé*, traductibilité. | `wp-content/plugins/fnc-content-model/` |
| **FNC Core** (extension) | **Logique du site** : réglages, données dérivées (programme, annuaire, compteurs), données structurées, formulaires, fonctionnalités (flags), consentement/mesure, archétypes de page. | `wp-content/plugins/fnc-core/modules/` |
| **Thème « Forum Numérique Congo »** | **Présentation** : gabarits, blocs, page d'accueil éditable, héros, SEO par page, formulaires (rendu), i18n de l'interface. | `wp-content/themes/fnc-wordpress-theme/` |

**Ordre de chargement / d'activation : Content Model → Core → thème.** Le thème **consomme** les
extensions mais tous les appels inter-composants sont gardés par `function_exists()` : le thème
seul reste fonctionnel (il bascule alors sur des replis Customizer).

**Dégradation gracieuse — ce qui se passe sans telle brique :**

- **sans FNC Core** : le thème lit ses réglages via le Customizer (`theme_mods`, repli) ; les
  données dérivées (programme, compteurs) sont absentes.
- **sans Polylang** : le site fonctionne en une seule langue (pas de sélecteur, pas de hreflang).
- **sans extension ACF/SCF** : l'édition des pages composées, des héros et des blocs fonctionne
  **nativement** (éditeur + Customizer) ; l'extension n'ajoute qu'un confort de saisie assistée
  pour la couche « archétypes ».
- **sans plugin SEO** : le thème assure lui-même un référencement de base.

```
themes/fnc-wordpress-theme/
├── functions.php              # bootstrap : require inc/*, setup, menus, i18n, nav, hreflang, flags
├── header.php / footer.php    # chrome : nav, sélecteur de langue, CTA conditionnel ; pied de page
├── front-page.php             # accueil, storyboard figé M1 → M8
├── page-{slug}.php            # gabarits de page par slug (le-forum, contact, …)
├── archive-fnc_{cpt}.php      # archives des types de contenu
├── single-fnc_{cpt}.php       # fiches détaillées
├── page.php / index.php / 404.php
├── hero-pcb.php               # partial : filet PCB animé (.pcb-band)
├── inc/                       # sous-systèmes du thème (voir §4, §7)
├── tools/                     # jeu de données + scripts de seed (voir §14)
├── languages/                 # .pot / .po / .mo / .l10n.php (en_GB)
└── assets/                    # css (kit DA), js/main.js + blocks.js, images, flags/ (SVG ISO)

plugins/fnc-content-model/includes/   # post-types, taxonomies, relations, statuses, polylang
plugins/fnc-core/                      # fnc-core.php (loader) + modules/
```

**Versions (constantes à connaître).** `FNC_THEME_VERSION` (`functions.php`), `FNC_CORE_VERSION`
(`fnc-core.php`), `FNC_CONTENT_MODEL_VERSION` (`fnc-content-model.php`). Le versionnage est **par
composant** : voir §16.

---

## 2. Environnement de développement

Rien de spécifique : c'est un thème + deux extensions WordPress standard. Pour travailler
confortablement sur une installation (locale ou préproduction) :

1. **Active `WP_DEBUG`** (`wp-config.php` : `define('WP_DEBUG', true); define('WP_DEBUG_LOG', true);`).
   Toute correction doit laisser `wp-content/debug.log` **vide** (aucun *notice/warning/fatal*).
2. **Permaliens** en « Titre de la publication », sinon les archives des types de contenu ne
   résolvent pas.
3. **WP-CLI** recommandé pour les tâches répétables :
   - `wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-dataset.php` — installe le jeu
     de démonstration (voir §14) ;
   - `wp cache flush` — après un seed ou une modification de réglages (les données dérivées sont
     mises en cache) ;
   - `wp rewrite flush` — après tout changement de slug / de type de contenu ;
   - `wp media regenerate --yes` — après un import d'images (le thème définit des tailles dédiées,
     dont l'image de partage 1200×630, et sert du WebP).
4. **Vérifier une fonction PHP sans redémarrer** : `wp eval-file` voit les éditions de fichiers
   immédiatement, utile pour sonder un accesseur (`fnc_edition_participants()`, etc.).
5. **Épingler WordPress en 6.x** sur l'environnement de dev : une 7.0.x a introduit une régression
   sur la locale par requête (bilingue).

> **Méthode de vérification d'une correction.** Après édition : (a) `php -l` sur les fichiers
> touchés ; (b) recharger la (les) page(s) concernée(s) et contrôler le **rendu réel** + le
> `debug.log` ; (c) pour un changement de données, re-sonder l'accesseur concerné via `wp eval-file`.

---

## 3. Le modèle de réglages *(à lire avant toute intervention sur les réglages)*

C'est le point qui prête le plus à confusion. **Deux espaces de stockage coexistent**, selon que
l'extension FNC Core est active ou non.

### 3.1 Qui fait autorité

- **FNC Core actif (installation complète) → l'option `fnc_settings` fait autorité.**
  L'accesseur **`fnc_get_setting($key, $default)`** (dans `fnc-core/modules/fnc-settings.php`) lit
  cette option unique (un tableau, clés **camelCase**). Les réglages généraux s'administrent dans
  **Réglages → FNC**.
- **Thème seul (sans l'extension) → repli Customizer.** Le thème définit un `fnc_get_setting()`
  de repli (gardé `function_exists`) qui lit `get_theme_mod('fnc_'.$key)` (clés **snake_case**).
  Le panneau Customizer « Réglages FNC » n'est enregistré **que dans ce cas** :
  `fnc_customize_register()` fait un `return` anticipé si `FNC_CORE_VERSION` est défini.

**Correspondance des clés.** `fnc_map_setting_key()` (`inc/customizer.php`) convertit les clés
historiques snake → camel (`official_name → officialName`, `footer_copyright → copyright`,
`seo_default_title → seoDefaultTitle`, `og_default_image → ogDefaultImage`, `logo_principal →
logoPrincipal`…). Les clés inconnues (ex. `country_order`, `press_contacts`) passent telles quelles.

### 3.2 Ce qui passe TOUJOURS par le Customizer (`get_theme_mod`), quelle que soit l'extension

- **Page d'accueil M1 → M8** : clés `fnc_home_*`, panneau **`fnc_homepage`** (`inc/homepage.php`).
- **Héros des pages-listes** : clés `fnc_hero_<route>_*`, panneau **`fnc_heroes`**
  (`inc/hero-settings.php`).
- **Titres de section internes** : clés `fnc_st_<route>_<clé>_<champ>`, panneau **`fnc_sections`**
  (`inc/section-titles.php`).

### 3.3 Les quatre panneaux `customize_register`

| Callback | Fichier | Panneau | Enregistré quand |
|---|---|---|---|
| `fnc_customize_register` | `inc/customizer.php` | `fnc_settings` (identité, logos, communication, presse, pied de page, ordre des pays, SEO) | **thème seul** (plugin inactif) |
| `fnc_homepage_customize_register` | `inc/homepage.php` | `fnc_homepage` (M1 → M8) | toujours |
| `fnc_hero_customize_register` | `inc/hero-settings.php` | `fnc_heroes` (héros par route) | toujours |
| `fnc_section_titles_customize_register` | `inc/section-titles.php` | `fnc_sections` (titres de bandes) | toujours |

### 3.4 « Je veux modifier X → où »

| Modifier… | Où | Stockage |
|---|---|---|
| Identité, logos, coordonnées, réseaux, pied de page, contacts presse, ordre des pays, SEO par défaut | **Réglages → FNC** | option `fnc_settings` |
| Sections / héros / titres de la **page d'accueil** | **Apparence → Personnaliser** | `theme_mod` (`fnc_home_*`) |
| Héros & titres de section des **pages-listes** | **Apparence → Personnaliser** | `theme_mod` |
| Contenu d'une **page éditoriale** (texte, images, ordre des sections) | **Éditeur de la page** (blocs) | contenu du post |
| Éditions, intervenants, sessions, partenaires, publications, actualités | leurs **fiches** | post-meta `_fnc_*` |
| Ouverture des inscriptions / affichage des actualités | **Réglages → FNC (fonctionnalités)** | flags (voir §13) |

> **⚠️ Règle d'or.** Toute **nouvelle surface de réglage général** d'une installation complète
> doit passer par l'**option plugin `fnc_settings`** (défauts dans `fnc_settings_defaults()`,
> assainissement dans `fnc_settings_sanitize()`, lecture via `fnc_get_setting()`), **jamais** par
> un nouveau `theme_mod` du Customizer — sinon la valeur ne sera pas relue quand l'extension est
> active (c'est l'origine du bug historique « les réglages ne passent pas »).

---

## 4. Le modèle de contenu *(FNC Content Model)*

### 4.1 Types de contenu

Enregistrés dans `includes/post-types.php` (`fnc_content_model_register_post_types()`, sur `init`,
`show_in_rest => true`). Les règles de réécriture sont vidées à l'activation/désactivation.

| Type | Slug CPT | Slug d'archive |
|---|---|---|
| Éditions | `fnc_edition` | `editions` |
| Sessions | `fnc_session` | `programme` |
| Intervenants | `fnc_intervenant` | `intervenants` |
| Publications | `fnc_publication` | `ressources` |
| Partenaires | `fnc_partenaire` | *pas d'archive* — liste = Page `partenaires` ; fiche servie via réécriture `partenaires/{slug}` |
| Actualités | `fnc_actualite` | `actualites` |

Les **Pages** natives WordPress portent les contenus éditoriaux (le-forum, contact, légales…).

### 4.2 Taxonomies

`includes/taxonomies.php` (`fnc_content_model_register_taxonomies()`) :

| Taxonomie | Rattachée à | Notes |
|---|---|---|
| `fnc_categorie` | sessions, publications, actualités | hiérarchique |
| `fnc_tag` | idem | non hiérarchique |
| `fnc_profil` | intervenants | **choix unique** (radio) — badge & filtre, **ne trie pas** |
| `fnc_pays` | intervenants | **dérivée automatiquement** de `_fnc_speaker_country` |
| `fnc_niveau_partenariat` | partenaires | choix unique — institutionnel / organisateur / soutien / sponsor |

### 4.3 Statut « archivé »

`includes/statuses.php` : `register_post_status('archived')` — `public=false`,
`exclude_from_search=true`, mais visible dans les listes d'administration. Appliqué aux 6 types
(`fnc_cm_archivable_types()`). Permet de **retirer du site sans supprimer** (distinct de la
corbeille). Réglable via *Modification rapide* ou l'éditeur classique (menu de statut injecté en JS).

### 4.4 Relations & méta

Les relations entre contenus sont des **post-meta** (ID ou tableau d'ID), pas des taxonomies
miroir. Clés, métaboxes et `register_post_meta` dans `includes/relations.php` ; enregistrement sur
`save_post` (`fnc_content_model_save_relations()`). Clés principales :

- **Session** : `_fnc_session_edition` (→ édition), `_fnc_session_speakers` (→ intervenants, tableau),
  `_fnc_session_moderator`, `_fnc_session_type`, `_fnc_session_start/_end/_time/_jour/_room`,
  `_fnc_session_objectives`, `_fnc_session_note`.
- **Intervenant** : `_fnc_speaker_title` (civilité), `_fnc_speaker_role` (fonction), `_fnc_speaker_org`,
  `_fnc_speaker_country` (« A / B »), `_fnc_speaker_protocol_order`, `_fnc_speaker_sort_index`,
  `_fnc_speaker_home_featured` (+ `_order`), `_fnc_speaker_links`, **`_fnc_speaker_image_right`** +
  **`_fnc_speaker_image_expires`** (droit à l'image — **hors REST**, voir §9).
- **Partenaire** : `_fnc_partenaire_site`, `_fnc_partenaire_sort_index`, `_fnc_partenaire_editions`,
  `_fnc_partenaire_participations` (`[{edition, niveau}]` — relation + niveau par édition).
- **Édition** : `_fnc_edition_active`, `_fnc_edition_status` (`upcoming`/`current`/`past`),
  `_fnc_edition_year/_theme/_start_date/_end_date/_location`, `_fnc_edition_is_special` (+ `_special_note`),
  `_fnc_edition_review`, `_fnc_edition_figures` (`[{value,label}]`), `_fnc_edition_gallery`.
- **Publication** : `_fnc_publication_edition`, `_fnc_publication_type`, `_fnc_publication_media_url`,
  `_fnc_publication_file` (validation PDF conditionnelle à la publication — voir `GUIDE.md` §5.5).
- **SEO (tous)** : `_fnc_seo_title`, `_fnc_seo_description`, `_fnc_seo_noindex` (`inc/seo.php`).

---

## 5. Gabarits (hiérarchie WordPress)

- **Accueil** : `front-page.php` — storyboard **figé** M1 → M8 (voir §10).
- **Pages** : `page-{slug}.php` s'applique par slug ; à défaut `page.php`. Une page composée en
  **blocs FNC** (`fnc_page_has_blocks()`) éclipse le contenu de démonstration du gabarit dédié.
- **Archives** : `archive-fnc_{cpt}.php` (boucle sur le contenu publié ; l'archive des intervenants
  est restreinte aux participants de l'édition en cours ; celle des actualités renvoie 404 quand la
  fonctionnalité est désactivée).
- **Fiches** : `single-fnc_{cpt}.php`.
- **Héros** : `fnc_render_opening_hero()` (`.opening`, pages éditoriales/listes),
  `fnc_render_pagehead()` (`.page-head` sobre, fiches), en-têtes dédiés le-forum/contact/légales.
  Filet PCB animé via `hero-pcb.php`.
- **Pages traduites** : `fnc_translated_page_template()` (filtre `template_include`) applique le
  gabarit **FR** (`page-{slug}.php`) à la page **EN** dont le slug diffère — évite d'avoir à
  dupliquer les gabarits par langue.

---

## 6. Blocs Gutenberg dynamiques *(thème)*

**24 blocs `fnc/*`**, tous **dynamiques** (rendu PHP, DA figée). Schémas dans `fnc_block_schemas()`,
enregistrés par `fnc_register_blocks()` (`register_block_type('fnc/'.$slug, …, render_callback =>
fnc_render_block)`). L'**éditeur** est un moteur de champs générique (`assets/js/blocks.js`, piloté
par `window.fncBlockSchemas`) : on édite les champs dans l'inspecteur, le rendu réel s'affiche dans
le canevas. Deux catégories : `fnc` et `fnc-pratique`.

- **Institutionnels** : `fnc/inst-hero`, `fnc/inst-president`, `fnc/inst-split` (mission),
  `fnc/inst-objectives`, `fnc/inst-faq`, `fnc/inst-manifesto`, `fnc/inst-callout`.
- **Composables génériques** : `fnc/hero`, `fnc/richtext`, `fnc/split`, `fnc/stats`, `fnc/cta`,
  `fnc/faq`, `fnc/documents`.
- **Infos pratiques** (catégorie `fnc-pratique`, rattachés à l'édition) : `fnc/pract-venue`,
  `fnc/pract-transport`, `fnc/pract-lodging`, `fnc/pract-visa`, `fnc/pract-badge`,
  `fnc/pract-contacts`, `fnc/pract-faq`, `fnc/pract-accessibility`.
- **Fonctionnels** : `fnc/form` (formulaire contact/inscription/partenariat, rendu par FNC Core,
  respecte le flag inscriptions), `fnc/coordonnees` (coordonnées officielles depuis les réglages).

**Palette verrouillée.** `fnc_restrict_page_blocks()` (filtre `allowed_block_types_all`) limite les
pages d'archétype `institutional`/`generic` aux blocs `fnc/*` + `core/paragraph` ; les pages
`legal`/`list`/`detail` gardent l'éditeur standard.

> **Ajouter / modifier un bloc.** (1) Déclarer/éditer le schéma dans `fnc_block_schemas()`
> (`inc/blocks.php`) ; (2) écrire/adapter le rendu dans la fonction `fnc_render_block_*`
> correspondante ; (3) le moteur JS générique prend le schéma en charge sans code côté éditeur. Ne
> **jamais** dupliquer de markup : les adaptateurs d'archétypes (§7) délèguent aux mêmes
> `fnc_render_block_*`.

---

## 7. Pages composées & archétypes (champs optionnels)

La couche « archétypes de page » (`fnc-core/modules/fnc-page-archetypes.php`) classe chaque Page
(`legal` / `institutional` / `list` / `detail` / `generic` / `homepage`) et expose des accesseurs
portables : `fnc_page_hero()`, `fnc_page_for_route()`, `fnc_page_archetype()`, `fnc_page_sections()`.

Elle enregistre **par code** deux groupes de champs (« FNC — Type de page », « FNC — Contenu de
page ») via `acf_add_local_field_group()`, **sous garde** `function_exists('acf_add_local_field_group')`
— donc **inopérants et sans erreur** si aucune extension de champs n'est active. C'est le **seul**
point d'intégration ACF/SCF du kit (via `get_field()`, lui aussi gardé) ; il n'y a **ni bloc ACF,
ni `have_rows`** ailleurs.

Côté thème, `inc/page-sections.php` est l'adaptateur qui, pour une page institutionnelle, lit les
sections via les accesseurs du plugin et les **délègue aux mêmes `fnc_render_block_*`** que les
blocs natifs (zéro markup dupliqué). Sans extension de champs, les pages institutionnelles se
composent **en blocs** dans l'éditeur, comme en §6.

---

## 8. Données dérivées *(FNC Core — le cœur métier)*

`fnc-core/modules/fnc-derived-data.php` construit **automatiquement** les vues à partir du contenu
publié (fonctions publiques `fnc_*`, mises en cache) :

- `fnc_current_edition_id()` / `fnc_resolve_active_edition()` — l'édition « en cours » (une seule).
- `fnc_edition_participants($edition=0)` — participants (≥ 1 session publiée de l'édition), triés
  **rang protocolaire → `_sort_index` → titre**.
- `fnc_home_voices($count=10, $edition=0)` — carrousel « Les voix » : promus (`_home_featured`,
  triés par `_home_featured_order`) d'abord, puis le reste, plafonné.
- `fnc_edition_countries()` — pays représentés (split `/`, **dédup sur clé normalisée**, pays hôte
  en tête à défaut d'ordre défini) ; `fnc_speaker_facets()` compte sur la même clé.
- Programme par jour, compteurs (`intervenants`, `pays`, `jours`).

> **Après un changement de données/réglages, vider le cache** (`wp cache flush`) : participants et
> facettes sont mis en cache.

**Droit à l'image (protection des portraits).** `fnc_speaker_portrait($id)` ne renvoie une `<img>`
que si `_fnc_speaker_image_right === 'obtenu'` et non expiré ; sinon le gabarit rend un
**monogramme** (`fnc_speaker_initials()`). Ces méta sont **exclues de l'API REST**, et les portraits
non consentis / médias de contenus non publiés sont masqués côté REST (le durcissement d'accès
direct aux fichiers relève du serveur — voir `INSTALL.md`). Surcharge possible via le filtre
`fnc_speaker_image_allowed`.

---

## 9. Accueil (storyboard figé M1 → M8)

`front-page.php`, ordre non modifiable (les textes, eux, sont éditables au Customizer, panneau
`fnc_homepage`) : M1 ouverture (CTA `/programme` + `/inscription` si ouvert) · M2 mission ·
**M3 carrousel « Les voix »** (eyebrow dynamique « N intervenants, N pays » ; `fnc_home_voices()` ;
auto-défilement JS avec pause au survol/focus et respect de `prefers-reduced-motion`) ·
M4 territoire · M5 programme · **M6 partenaires** (publiés + logo, triés par `_sort_index`, 3 grands
en tête) · M7 archives · **M8 compte à rebours** (depuis la date de début de l'édition en cours ;
recalcul client anti-cache, repli serveur).

---

## 10. Bilingue *(Polylang)*

- Locales : **`fr` (défaut, fr_FR)** + **`en` (en_GB)** — correspond aux `.mo` embarqués.
- **Traductibilité du contenu** : les 6 types et 5 taxonomies sont déclarés traduisibles par code
  (`includes/polylang.php` : filtres `pll_get_post_types` / `pll_get_taxonomies`) ; sans effet si
  Polylang est absent. Les **méta d'ordre** sont synchronisées entre langues (`pll_copy_post_metas`).
- **Interface** : libellés traduits via `__()` ; le `.mo` est chargé par locale sur `init`/`wp`
  (`fnc_load_textdomain()`) pour contourner un piège de timing Polylang.
- **Réglages & accueil localisables** : via *Réglages → Langues → Traductions de chaînes*
  (`fnc_get_setting_i18n`, `fnc_register_pll_strings` ; groupe « FNC Accueil » pour M1–M8).
- **Convention de traduction des pages composées** : créer la version EN **depuis la FR** (même
  structure de blocs), traduire **sur place** sans réorganiser (voir `GUIDE.md` §6).
- **hreflang** : émis par le thème (`fnc_emit_hreflang()`, `wp_head` : `fr` / `en` / `x-default`).

> **Créer les deux langues en ligne de commande (Polylang gratuit).** La commande `wp pll` n'existe
> **pas** en Polylang gratuit (réservée à la version Pro). Passer par l'API modèle :
> ```php
> $langs = PLL()->model->languages;
> $langs->add( array('name'=>'Français','slug'=>'fr','locale'=>'fr_FR','rtl'=>0,'flag'=>'fr','term_group'=>0) );
> $langs->add( array('name'=>'English', 'slug'=>'en','locale'=>'en_GB','rtl'=>0,'flag'=>'gb','term_group'=>1) );
> $langs->clean_cache();
> ```
> (via `wp eval-file`), puis `wp rewrite flush`. En interface, c'est simplement *Langues → Ajouter*.

---

## 11. SEO & données structurées

- **SEO par page** (`inc/seo.php`) : métabox (titre / description / noindex) + cascade et émission
  dans `<head>` (`fnc_head_meta()`). `fnc_seo_delegated()` détecte un plugin SEO actif.
- **Données structurées** (`fnc-core/modules/fnc-structured-data.php`) : JSON-LD `Organization` +
  `WebSite` (partout) + `Event` (accueil, si nom & date). **Anti-doublon** : si AIOSEO/Yoast est
  actif, le kit lui laisse `<title>`/description/OpenGraph/canonique/`Organization`+`WebSite` et ne
  conserve que l'**`Event`** (qu'un plugin SEO ne sait pas produire).

---

## 12. Formulaires, soumissions & fonctionnalités

- **Formulaires** : rendu partagé dans `inc/forms.php` (`fnc_form_fields()`,
  `fnc_render_contact_coordinates()`), réutilisé par les gabarits ET les blocs `fnc/form` /
  `fnc/coordonnees` (même markup, nonce + champs cachés).
- **Soumissions** (`fnc-core/modules/fnc-submissions.php`) : réception contact / inscription /
  partenariat → stockage privé + accusé de réception (la demande est **toujours** enregistrée,
  même si l'e-mail échoue) + `do_action('fnc_submission_stored')`. Arrivent dans **Soumissions**.
- **Fonctionnalités (flags)** (`fnc-core/modules/fnc-feature-flags.php`) :
  `fnc_registration_enabled()` / `fnc_news_enabled()` (résolution **constante → variable d'env →
  option**). Appliqués à **3 niveaux** : API (`fnc_submission_accepts`), CTA (`fnc_registration_cta()`)
  et **page/SEO** (gate thème sur `template_redirect` : actualités fermées → 404 ; inscription
  fermée → noindex + état « fermé » honnête ; retrait du plan de site). Bascules dans
  **Réglages → FNC (fonctionnalités)**.
- **Consentement / mesure** (`fnc-core/modules/fnc-consent-matomo.php`) : mesure **anonyme et sans
  cookie** par défaut, bandeau **Refuser / Autoriser à poids égal**, cookie **seulement après un
  « oui »** explicite (choix en `localStorage`), région non bloquante, lien de réouverture en pied
  de page. URL/siteId via réglages (`fnc_matomo_url()` / `fnc_matomo_site_id()`), **jamais en dur**.

---

## 13. Jeu de données & seed *(tools/)*

Pipeline reproductible et versionné :

1. **`dataset.json`** (committé) : le jeu de données de démonstration autonome.
2. **`seed-dataset.php`** (`fnc_ds_run_seed()`, idempotent, clé `_fnc_seed_legacy`) : réglages,
   pages éditoriales, types de contenu + **traductions EN** liées FR↔EN ; `fnc_ds_remove_seed()`
   retire proprement le contenu de démo + ses médias.
3. **`seed-content.php`** (`[force]`) : (re)compose les pages éditoriales en blocs, FR + EN.
4. **`seed-settings.php`** (`[force]`) : réglages du site.

Exposé en un clic : **Apparence → Contenu de démonstration** (`inc/demo-import.php`). En CLI :
`wp eval-file …/tools/seed-dataset.php` puis `wp cache flush`. *(Le générateur `build-dataset.mjs`
est un outil interne qui produit `dataset.json` ; il n'est pas livré dans le paquet.)*

---

## 14. Points d'extension (hooks)

| Hook | Type | Usage |
|---|---|---|
| `fnc_settings` | filtre | surcharger les réglages résolus |
| `fnc_hero_image_base_url` | filtre | base d'URL des images de héros |
| `fnc_page_hero_defaults` | filtre | héros par défaut d'une page/route |
| `fnc_matomo_url` / `fnc_matomo_site_id` / `fnc_matomo_should_track` | filtres | infra de mesure (jamais en dur) |
| `fnc_consent_strings` | filtre | libellés du bandeau de consentement |
| `fnc_speaker_image_allowed` | filtre | surcharge de la protection du droit à l'image |
| `fnc_submission_accepts` | filtre | validation des soumissions |
| `fnc_privacy_url` | filtre | URL de la politique de confidentialité |
| `fnc_client_ip_header` | filtre | en-tête d'IP client derrière un proxy de confiance |
| `fnc_submission_stored` | action | après enregistrement d'une soumission |

Conventions : préfixe **`fnc_`** ; toute fonction inter-composant gardée par `function_exists()` ;
`register_post_meta(..., show_in_rest => true)` sauf données gouvernées (droit à l'image).

---

## 15. Build & publication

- **Packaging** : `python build-package.py` → `dist/forum-numerique-congo-template-{version}/` :
  les **3 zips installables** (thème + 2 extensions, dossier racine unique) + `INSTALL.md`,
  `GUIDE.md`, `DEVELOPER.md`, et une archive complète. **Toujours en Python**, jamais
  `Compress-Archive` (séparateurs `\` qui cassent la décompression WordPress sur Linux).
- **Versionnage par composant** : chaque release aligne les composants touchés. **Emplacements à
  garder cohérents** : en-tête `style.css`, `FNC_THEME_VERSION`, en-tête **+ constante** de chaque
  extension (`FNC_CORE_VERSION`, `FNC_CONTENT_MODEL_VERSION`), `VERSION` de `build-package.py`.
- À installer côté WordPress : les **zips individuels**, pas l'archive complète (qui n'a pas de
  `style.css` à sa racine).

---

## 16. Durcissement serveur

Deux protections relèvent de la configuration serveur (pas de PHP seul) : l'accès direct aux
fichiers média sensibles (portraits sans droit à l'image, médias de contenus non publiés) et
l'anti-abus des formulaires derrière un proxy/CDN (`FNC_TRUSTED_PROXY` + filtre
`fnc_client_ip_header`). Procédure complète dans **`INSTALL.md` → « Durcissement serveur »**.

---

## 17. Faire une correction de bout en bout *(méthode)*

Exemple : « le pied de page affiche une colonne de liens erronée ».

1. **Localiser.** Le pied de page est rendu par `fnc_footer_columns()` (`inc/customizer.php`) depuis
   `footer.php`. Les colonnes viennent, par ordre de priorité : texte éditable → tableau structuré
   du plugin → colonnes par défaut.
2. **Déterminer la source.** Installation complète (FNC Core actif) → la donnée est dans l'option
   `fnc_settings` (clé `footerLinkGroupsText`), administrée dans **Réglages → FNC → Pied de page**.
   Vérifier d'abord si c'est un **réglage** (corrigible sans code) avant de toucher au code.
3. **Si c'est du code.** Éditer la fonction concernée. Respecter : DA figée (pas de style inline),
   garde `function_exists` pour tout appel plugin, libellés via `__()`.
4. **Vérifier.** `php -l` ; recharger `/` **et** `/en/` ; contrôler le rendu **et** `debug.log`
   (zéro ligne). Pour une donnée dérivée, re-sonder l'accesseur via `wp eval-file`.
5. **Internationaliser.** Vérifier le rendu dans les **deux langues** ; si une chaîne est nouvelle,
   l'ajouter au `.pot`/`.po`/`.mo` ou aux chaînes Polylang selon le cas.
6. **Versionner.** Bumper le(s) composant(s) touché(s) à tous les emplacements (§15) et régénérer
   le paquet si livraison.

> **Rappels de pièges fréquents.** (a) Un réglage général qui « ne passe pas » → il est édité au
> mauvais endroit (Customizer au lieu de Réglages → FNC) ou défini en `theme_mod` au lieu de
> l'option plugin (§3). (b) Une page en « préparation » → contenu non publié, ou cache de données
> dérivées à vider (`wp cache flush`). (c) Un portrait absent → droit à l'image non « obtenu » /
> expiré (§8), comportement voulu.

---

*Template **Forum Numérique Congo** — thème + extensions « FNC Content Model » et « FNC Core ».*
*© 2026 **Grinso & Associés** — [www.grinso.io](https://www.grinso.io). Tous droits réservés.*
*Développé par **Vanel NGOYO ADOUMA**, Lead développeur.*
