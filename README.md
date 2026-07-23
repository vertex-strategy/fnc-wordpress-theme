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

`fnc_edition` porte en plus un champ booléen `_fnc_edition_active` (« édition en cours ») : une seule édition peut être active à la fois — en cocher une désactive automatiquement les autres à la sauvegarde. Utilisé par `page-edition-en-cours.php`.

`fnc_partenaire` porte une taxonomie `fnc_niveau_partenariat` (Institutionnel/Organisateur/Soutien/Sponsor) et 2 champs meta : `_fnc_partenaire_site` (URL) et `_fnc_partenaire_editions` (relation multiple vers des éditions, même logique que `_fnc_session_speakers`). Utilisés par `page-partenaires.php`.

### Réconciliation du modèle de contenu avec le vrai schéma Payload (v0.2.0)

À la demande du Décideur (« va récupérer tous les éléments et comprendre le vrai site »), le modèle de contenu ci-dessus a été comparé champ par champ aux vraies collections Payload du site officiel (`src/payload/collections/*.ts`, lues directement, pas observées via le rendu). Le plugin ne portait que la structure grossière (relations, quelques métas ajoutées au fil des gabarits) ; plusieurs champs réels en étaient absents. Cette passe les ajoute — **volontairement de façon additive** : aucun champ déjà lu par un gabarit existant n'a été renommé ou supprimé, pour ne rien casser avant la passe de mise à jour des gabarits (prochaine étape, explicitement distincte de celle-ci).

Nouveaux champs par collection :

| CPT | Nouveaux champs meta | Reflète (collection Payload) |
|---|---|---|
| `fnc_edition` | `_fnc_edition_status` (select : à venir/en cours/passée — dérive automatiquement `_fnc_edition_active`), `_fnc_edition_year`, `_fnc_edition_theme`, `_fnc_edition_start_date`/`_fnc_edition_end_date`, `_fnc_edition_location`, `_fnc_edition_is_special` + `_fnc_edition_special_note` | `Editions.eventStatus`, `.year`, `.theme`, `.startDate`/`.endDate`, `.location`, `.isSpecialEdition`/`.specialEditionNote` |
| `fnc_session` | `_fnc_session_type` (select, 9 valeurs : allocution/inaugurale/interview/table-ronde/session/débat/pause/logistique/clôture), `_fnc_session_moderator` (relation simple, distincte des intervenants), `_fnc_session_start`/`_fnc_session_end` (recomposent `_fnc_session_time` automatiquement), `_fnc_session_objectives` (tableau), `_fnc_session_note` | `Sessions.type`, `.moderator` (≠ `.speakers`), `.start`/`.end`, `.objectives`, `.note` |
| `fnc_intervenant` | `_fnc_speaker_title` (civilité), `_fnc_speaker_org`, `_fnc_speaker_country` (texte libre, ex. « France / États-Unis » — distinct de la taxonomie `fnc_pays` déjà utilisée pour le filtre), `_fnc_speaker_protocol_order`, `_fnc_speaker_links` (tableau libellé/URL) | `Speakers.title`, `.org`, `.country`, `.protocolOrder`, `.links` |
| `fnc_publication` | `_fnc_publication_type` (select, 8 valeurs : rapport/livre blanc/actes/communiqué de presse/note conceptuelle/vidéo/interview/autre — **distinct** de la catégorie `fnc_categorie`), `_fnc_publication_media_url` | `Publications.type` (séparé de `.category`), `.mediaUrl` |
| `fnc_partenaire` | `_fnc_partenaire_participations` (tableau `{édition, niveau}` — niveau : principal/majeur/officiel/contributeur, **par édition**), taxonomie `fnc_niveau_partenariat` relabellée « Type de partenaire » (le champ qu'elle modélisait réellement depuis le début) | `Partners.type` (taxonomie existante, mal nommée jusqu'ici) **et** `.participations[].niveau` (nouveau — plus fin, absent avant cette passe) |

Point de vigilance découvert en lisant le vrai schéma : `Partners.type` (institutionnel/organisateur/soutien/sponsor, fixe par partenaire) et `Partners.participations[].niveau` (principal/majeur/officiel/contributeur, variable par édition) sont **deux concepts distincts** dans Payload. La taxonomie `fnc_niveau_partenariat` existante modélisait déjà correctement `type` (ses 4 termes correspondaient) mais portait un nom trompeur — corrigé sans changer le slug ni les termes existants. `niveau` (par édition) était totalement absent avant cette passe ; il est maintenant capturé dans `_fnc_partenaire_participations`, en plus (pas à la place) du tableau plat `_fnc_partenaire_editions` déjà lu par `page-partenaires.php`.

**Non fait dans cette passe, volontairement** : les gabarits du thème (`page-edition-en-cours.php`, `archive-fnc_session.php`, `archive-fnc_edition.php`, `archive-fnc_intervenant.php`, `page-partenaires.php`, `archive-fnc_publication.php`) ne consomment pas encore ces nouveaux champs — ils continuent de fonctionner exactement comme avant. La mise à jour des gabarits (timeline chronologique pour les éditions au lieu d'une grille de cartes, badges de type de session, drapeaux pays sur les intervenants, distinction modérateur/intervenants dans le programme, regroupement partenaires par type **et** niveau, filtre par type sur les publications) est délibérément reportée à une prochaine étape, à la demande du Décideur.

### Passe gabarits (thème v0.2.0) : les nouveaux champs sont maintenant affichés

Suite immédiate de la passe précédente, à la demande du Décideur (« On y va pour la passe gabarits maintenant »). Les 6 gabarits identifiés ci-dessus ont été mis à jour pour consommer les champs ajoutés au plugin :

- **`archive-fnc_edition.php`** : rendu en **frise chronologique** (`.frise`), pas une grille de cartes — année, badge de statut (à venir/en cours/passée), thème, dates, lieu, note d'édition spéciale. Triée par année décroissante (`_fnc_edition_year`, repli sur la date de publication WP si absente), comme la vraie page `/editions`.
- **`archive-fnc_session.php`** et **la prévisualisation programme de `page-edition-en-cours.php`** : badge de type de session à côté du titre (masqué pour pause/logistique, comme le site officiel), ligne « Modérateur : … » distincte des intervenants.
- **`page-edition-en-cours.php`** : thème, dates et lieu de l'édition affichés sous le hero.
- **`archive-fnc_intervenant.php`** : nom affiché avec civilité (« Dr. », « Mme »…), ligne organisation · pays sous chaque nom, **frise « Pays représentés »** avec drapeaux SVG inline au-dessus des filtres (dérivée du champ texte libre `_fnc_speaker_country`, qui peut cumuler plusieurs pays séparés par « / », comme le site officiel).
- **`page-partenaires.php`** : badge de niveau (principal/majeur/officiel/contributeur) à côté de chaque édition associée, en plus du regroupement déjà existant par type.
- **`archive-fnc_publication.php`** : le filtre principal utilise désormais le champ dédié `_fnc_publication_type` (8 valeurs, badge affiché sur chaque carte) au lieu de la taxonomie `fnc_categorie` — reflète la séparation réelle `type`/`category` de la collection Payload `Publications`. Lien « Regarder » pour les types vidéo/interview quand `_fnc_publication_media_url` est renseigné.

Nouvelles fonctions utilitaires dans `functions.php` : `fnc_render_badge()`, `fnc_speaker_display_name()`, `fnc_speaker_meta_line()`, `fnc_country_flag_svg()` (11 pays, mêmes géométries simplifiées que `CountryFlag.tsx` du site officiel, sans dépendance tierce), `fnc_split_countries()`. Nouvelles classes CSS : `.badge`, `.frise*`, `.flag-block`/`.flag-frise`/`.flag-chip`/`.flag-svg`, `.person-meta`, `.moderator-line`.

**Vérifié en conditions réelles**, pas seulement en lint : `php -l` sur les 7 fichiers modifiés, puis contrôle dans la pile de développement locale (conteneurs `fnc-wordpress-theme_*`) — contenu de démonstration enrichi via wp-cli, chaque page rechargée dans le navigateur, logs du conteneur inspectés (aucun fatal/notice/warning). Frise des éditions, badges de type, ligne modérateur, drapeaux pays (3 rendus, dont un pays cumulé « France / Cameroun » correctement décomposé), badge de niveau par partenaire et filtre par type de publication confirmés fonctionnels sur les données de test.

## Pages intérieures (au-delà de l'accueil)

| Page | Gabarit | Source (`forum-numerique-congo`) | Type de rendu |
|---|---|---|---|
| Le Forum | `page-le-forum.php` | `docs/mockups/homepage-v2/interior/le-forum.html` | Statique (contenu exemple) |
| Contact | `page-contact.php` | `docs/mockups/homepage-v2/contact.html` (rendu par `site.js`) | Statique (formulaire non fonctionnel) |
| Éditions | `archive-fnc_edition.php` | `docs/mockups/homepage-v2/editions.html` (rendu par `site.js`) | **Dynamique** — vraies données du plugin |
| Publications | `archive-fnc_publication.php` | `docs/mockups/homepage-v2/publications.html` (rendu par `site.js`) | **Dynamique** — vraies données du plugin |
| Programme | `archive-fnc_session.php` | `docs/mockups/homepage-v2/programme.html` (rendu par `site.js`) | **Dynamique** — agenda réel, groupé par jour |
| Intervenants | `archive-fnc_intervenant.php` | `docs/mockups/homepage-v2/intervenants.html` (rendu par `site.js`) | **Dynamique** — filtres profil/pays réels |
| Édition en cours | `page-edition-en-cours.php` | `docs/mockups/homepage-v2/edition-en-cours.html` (rendu par `site.js`) | **Dynamique** — hub (stats, aperçus programme/intervenants) |
| Informations pratiques | `page-informations-pratiques.php` | `docs/mockups/homepage-v2/informations-pratiques.html` (rendu par `site.js`) | Statique (état d'attente, aligné sur le site réel) |
| Partenaires | `page-partenaires.php` | `docs/mockups/homepage-v2/partenaires.html` (rendu par `site.js`) | **Dynamique** — groupés par niveau réel |
| Espace presse | `page-espace-presse.php` | `docs/mockups/homepage-v2/espace-presse.html` (rendu par `site.js`) | **Dynamique** — communiqués (Publications filtrées) |
| Mentions légales | `page-mentions-legales.php` | `docs/mockups/homepage-v2/mentions-legales.html` (rendu par `site.js`) | Statique (contenu `[DÉMO]`, aligné sur le site réel) |
| Politique de confidentialité | `page-politique-confidentialite.php` | `docs/mockups/homepage-v2/politique-confidentialite.html` (rendu par `site.js`) | Statique (contenu `[DÉMO]`, section « Mesure d'audience » reformulée) |
| CGU | `page-conditions-generales-utilisation.php` | `docs/mockups/homepage-v2/conditions-generales-utilisation.html` (rendu par `site.js`) | Statique — **contenu réel non confirmable** (voir note ci-dessous) |
| Inscription | `page-inscription.php` | `docs/mockups/homepage-v2/inscription.html` (rendu par `site.js`) | Statique (formulaire non fonctionnel) + nom d'édition **dynamique** |
| Mot du Président | `page-mot-du-president.php` | `docs/mockups/homepage-v2/mot-du-president.html` (rendu par `site.js`) | Statique — **contenu réel non repris** (voir note ci-dessous) |
| Déclaration d'accessibilité | `page-declaration-accessibilite.php` | `docs/mockups/homepage-v2/declaration-accessibilite.html` (rendu par `site.js`) | Statique — **contenu réel non confirmable** (voir note ci-dessous) |

**Statut des sources à la date d'intégration :** ces 16 fichiers existent dans le dossier du projet principal mais n'étaient pas committés sur la branche de l'ADR-007 (travail en cours d'un autre agent, non fusionné) — intégrés ici à la demande explicite du Décideur, malgré ce statut. Si les fichiers source évoluent avant d'être committés en amont, les gabarits correspondants devront être resynchronisés.

**Inscription** : la collection « Registrations » du site officiel est explicitement hors périmètre du plugin (voir Modèle de contenu ci-dessus) — ce formulaire reste donc un exemple statique, non connecté, comme sur la maquette source. Seule partie dynamique : le nom de l'édition active réelle (`_fnc_edition_active`) affiché dans « Vous vous inscrivez à ».

**Mot du Président** : sur le site officiel réel, cette page attribue un message institutionnel à une personne réellement nommée et cite un texte réel. **Ni le nom ni le texte ne sont repris ici** — cohérent avec le reste de ce thème (aucune vraie identité de responsable public réutilisée dans ce produit distinct) et avec la maquette source elle-même, dont la note éditoriale dit explicitement « aucune citation n'est inventée ». Le contenu affiché est un placeholder explicite. La page est créée comme page enfant de « Le Forum » pour refléter la hiérarchie d'URL réelle (`/le-forum/mot-du-president/`).

**Déclaration d'accessibilité** : même limite que la page CGU — la route réelle a renvoyé une erreur 404 au moment de l'intégration, contenu non confirmable. Ne déclare aucune conformité WCAG, conformément à la note éditoriale de la maquette source (« ne jamais déclarer conforme sans audit et validation explicite »).

**Pages légales** : structure sobre alignée sur le site officiel réel (fil d'ariane + date de mise à jour + titre, sans photo hero — nouvelle fonction `fnc_render_legal_header()`), contenu marqué `[DÉMO]` comme sur le site réel. La route réelle équivalente à la page CGU a renvoyé une erreur 404 au moment de l'intégration (le `<title>` de la réponse était correct, mais le corps affichait « Page non trouvée ») — impossible de confirmer si cette page existe réellement côté site officiel ou si son contenu diffère de la structure reprise ici par cohérence avec les deux autres pages légales. La section « Mesure d'audience » de la politique de confidentialité réelle (qui décrit une intégration Matomo auto-hébergée) n'a pas été copiée : ce thème de démonstration n'intègre aucune solution de mesure réelle, le texte est donc généralisé/illustratif.

**Partenaires** regroupe les vrais posts `fnc_partenaire` par niveau réel (`fnc_niveau_partenariat`), avec site web et éditions associées si renseignés. **Espace presse** traite les communiqués comme un type de Publication filtré par catégorie (`fnc_categorie` = « Communiques »), pas comme une collection séparée — cohérent avec le constat du site officiel réel (« les communiqués restent des Ressources du portail »). Sa section « Contacts presse » reste volontairement statique : le site réel la relie à des Réglages du site globaux, une configuration hors périmètre du plugin.

**Édition en cours** est le hub le plus dynamique du thème à ce jour : il récupère l'édition marquée « active » (nouveau champ `_fnc_edition_active` sur `fnc_edition`, une seule à la fois), puis calcule en temps réel le nombre de sessions, d'intervenants distincts et de jours à partir des relations déjà construites (session→édition, session→intervenants, session→jour), avec un aperçu programme et un aperçu intervenants. Seul le bloc « Inscription » reste statique/exemple (pas de collection d'inscriptions dans le plugin — hors périmètre, voir modèle de contenu ci-dessus).

**Informations pratiques** reste volontairement sobre : sur le site officiel réel, cette page affiche un état « en cours de finalisation », pas une liste de rubriques (visa, hôtels...) comme le suggérait la maquette statique — reproduit ici avec un texte reformulé, pas copié du site officiel.

## Navigation

Une fois les ~16 pages/archives ci-dessus créées, la navigation par défaut (menu et pied de page) pointait encore vers les ancres `#m2`/`#m3`/`#m5`/`#m7` de la maquette mono-page d'origine — cassée sur toute page autre que l'accueil. Corrigé :

- `fnc_page_url( $slug )` / `fnc_archive_url( $post_type )` (`functions.php`) : URL réelle d'une Page ou d'une archive, repli sur `#` si le contenu n'existe pas encore dans l'installation.
- `fnc_default_menu_items()` : les 6 items du menu principal réel du site officiel (Le Forum, Édition en cours, Éditions, Ressources, Partenaires, Contact), utilisés par le menu de repli desktop et mobile.
- `footer.php` : les 3 colonnes et les liens légaux du bas pointent vers les vraies pages/archives, reprenant les groupes du site officiel réel (Le Forum : Présentation/Édition en cours/Programme/Intervenants ; Ressources : Éditions/Ressources/Informations pratiques/Dossier presse ; Contact : Contact/Devenir partenaire/S'inscrire).
- `header.php` : le CTA statique « Accréditation » (bouton sans lien) devient un vrai lien « S'inscrire » vers la page Inscription.
- Lien d'évitement (`.skip`) corrigé de `#m1` (ne fonctionnait que sur l'accueil) vers `#main` — `id="main"` ajouté à `front-page.php` et `index.php`, qui ne l'avaient pas contrairement à tous les autres gabarits.

Si un menu WordPress réel est configuré dans Apparence → Menus, il prend le pas sur ce repli, comme prévu depuis le départ (`has_nav_menu()`).

**Programme** a nécessité d'étendre le plugin : trois nouveaux champs meta sur `fnc_session` (`_fnc_session_time`, `_fnc_session_room`, `_fnc_session_jour`), ajoutés à la meta box existante « Édition et intervenants », nécessaires pour afficher un agenda réel (horaire, salle, regroupement par journée) plutôt qu'une simple liste de titres.

## Amendement de la Décision 1 (ADR-007) : alignement structurel sur le site officiel réel

Une exploration du site officiel réel (`localhost:3000/fr`) a révélé que Programme, Intervenants et Ressources y sont déjà fonctionnellement plus riches que ce que la seule maquette statique laissait supposer :

- **Intervenants** : filtre par profil (Officiels/Experts/Hôtes) **et** par pays (avec drapeaux), pas de simple liste.
- **Programme** : agenda réel réparti sur plusieurs jours avec navigation par ancres, pas une liste plate.
- **Ressources** : recherche et filtre par type de document.

Le choix de source visuelle (maquette statique, pas le rendu Next.js — Décision 1 initiale) n'a pas été renversé, mais la **structure fonctionnelle** de ces 3 gabarits a été alignée sur celle du site officiel réel :

- `archive-fnc_intervenant.php` : filtre par profil (`fnc_profil`, chips) et par pays (`fnc_pays`, liste déroulante), via requête GET native WordPress (query vars publics de taxonomie), sans JavaScript de soumission automatique.
- `archive-fnc_session.php` : sessions regroupées par jour (`_fnc_session_jour`), avec navigation par ancres.
- `archive-fnc_publication.php` : formulaire de recherche natif (`s`), en plus des chips de catégorie déjà en place.

**Le contenu de démonstration reste explicitement fictif** : les vraies identités de responsables publics visibles sur le site officiel réel (noms, fonctions) ne sont **jamais** reprises dans ce produit distinct — seule la structure des champs (profil, pays, jour) est répliquée, avec des exemples génériques non officiels.

Voir l'amendement complet de la Décision 1 dans [ADR-007](https://github.com/vertex-strategy/forum-numerique-congo/blob/codex/m4-wcag-integration/docs/adr/ADR-007-template-wordpress-produit-parallele.md) (v1.2) — cet amendement a été validé directement par le Décideur, sans nouvelle revue d'architecture indépendante (raffinement de structure, pas un renversement de la Décision 1).

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
