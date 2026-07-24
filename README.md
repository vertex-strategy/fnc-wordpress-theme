# FNC WordPress Theme

Thème WordPress + plugin de contenu, dérivés de la maquette statique `docs/mockups/homepage-v2` du dépôt [`forum-numerique-congo`](https://github.com/vertex-strategy/forum-numerique-congo).

**Statut : parité fonctionnelle atteinte avec les surfaces administrables du vrai site.** Le thème et le plugin sont branchés (étape 4 terminée), et les sept lots d'administrabilité sont livrés : réglages globaux (Customizer), composition de pages par blocs, page d'accueil éditable (héros image/vidéo/slider), informations pratiques rattachées à l'édition, SEO par page, fiches individuelles des contenus, et ordre des pays + drapeaux uploadables. Multilinguisme (Polylang) non encore intégré. Détail de chaque lot dans les sections dédiées ci-dessous.

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

## Réglages globaux du site (Customizer) — Lot 1

Le vrai site pilote son identité, ses coordonnées, son footer et son SEO depuis un **Global « Réglages du site »** administrable sans développeur (`src/payload/globals/Settings.ts`, 7 onglets). Le pendant WordPress natif est le **Customizer** (`inc/customizer.php`, panneau « Réglages FNC ») — architecture validée par le Décideur (**Customizer + blocs Gutenberg custom, zéro dépendance tierce**, conforme ADR-007 Décision 2).

Sections couvertes (miroir des onglets Payload) :

| Onglet Payload | Section Customizer FNC | Champs |
|---|---|---|
| Identité | Identité | Nom officiel, slogan, sous-titre, description, présentation courte |
| Logos & icônes | Logos & icônes | Logo principal / clair / sombre (favicon = Icône du site WordPress native) |
| Communication | Communication | Email, téléphone, adresse, 5 réseaux sociaux (LinkedIn/X/Facebook/Instagram/YouTube) |
| Communication › Contacts presse | Contacts presse | Répéteur texte (une ligne : Nom \| Rôle \| Organisation \| Email \| Téléphone) |
| Footer | Footer | Texte, mention de copyright (année ajoutée automatiquement) |
| SEO par défaut | SEO par défaut | Titre, description, image OpenGraph, type de Twitter card, directive robots |

Câblage : `header.php` (logo administrable, repli sur le sigle SVG intégré ; libellé depuis le nom officiel), `footer.php` (marque, texte, coordonnées, réseaux, copyright), `page-espace-presse.php` (contacts presse, masqués si non renseignés — RÈGLE 4), et le `<head>` (métadonnées `description`/OpenGraph/Twitter ; la directive robots passe par le filtre natif `wp_robots` pour ne pas dupliquer la balise). Helpers de lecture dans `inc/customizer.php` (`fnc_get_setting`, `fnc_social_links`, `fnc_parse_press_contacts`…) et `functions.php` (`fnc_site_name`, `fnc_head_meta`, `fnc_filter_robots`, `fnc_header_logo_img`).

**Simplifications assumées (zéro dépendance)** : le Customizer natif n'a pas de répéteur, donc les réseaux sociaux sont 5 champs URL par plateforme (l'énumération Payload est fermée) et les contacts presse un champ texte structuré une-ligne-par-contact. La **navigation configurable** et les **groupes de liens typés du footer** du vrai site restent gérés par les menus WordPress natifs (Apparence → Menus) déjà en place ; l'**ordre éditorial des pays + drapeaux uploadables** (onglet Intervenants) est reporté à un lot ultérieur.

**Non couvert par ce lot (prochains lots — architecture blocs Gutenberg)** : composition des pages institutionnelles/génériques par blocs, homepage éditable (8 moments), informations pratiques de l'édition (8 rubriques), SEO par page.

**Vérifié en conditions réelles** : Customizer chargé (HTTP 200) avec les 6 sections et tous les réglages enregistrés ; métadonnées SEO/OpenGraph/Twitter et directive robots (via filtre natif, sans doublon) confirmées dans le `<head>` ; footer complet (marque/texte/coordonnées/réseaux/copyright) et contacts presse rendus depuis des réglages de test via wp-cli ; logo administrable et repli SVG tous deux confirmés ; aucun fatal/notice/warning dans les logs du conteneur.

## Composition de pages par blocs (Lot 2)

Sur le vrai site, les pages ne sont pas des gabarits figés : elles sont **composées** à partir de palettes de blocs administrables (`institutionalSections.ts` pour Le Forum/Contact, `pageSections.ts` pour les pages composables). Le pendant WordPress natif, ce sont des **blocs Gutenberg custom** — architecture validée par le Décideur.

**14 blocs** sont fournis (`inc/blocks.php`), miroir des palettes Payload :

| Palette | Blocs |
|---|---|
| Institutionnelle | Ouverture, Mot du Président, Mission (média + texte), Objectifs (séquence numérotée), FAQ, Ancrage (manifeste), Callout (CTA) |
| Générique | Héros, Texte riche, Média + texte, Chiffres clés, Appel à action, FAQ, Documents |

**Principe repris à l'identique du vrai site : la DA reste dans le code.** Tous les blocs sont **dynamiques** (`save` retourne `null`, rendu serveur en PHP) et produisent le markup DA figé du thème (`.opening`, `.split`, `.obj`, `.territory`, `.callout`, `.metric-strip`…). L'éditeur administre le contenu, les médias et les CTA — jamais la mise en forme.

**Implémentation « pilotée par schéma »** : les champs de chaque bloc sont déclarés une seule fois en PHP, puis (a) convertis en attributs de bloc, (b) rendus en PHP, (c) transmis à un moteur d'édition générique (`assets/js/blocks.js`) qui construit l'interface. Il n'y a donc pas 14 composants d'édition à maintenir, et **aucune étape de build** (pas de JSX, pas de bundler) — conforme au « zéro dépendance tierce » de l'ADR-007. Types de champs pris en charge : texte, zone de texte, texte riche, URL, liste déroulante, média (image/fichier) et **répéteur** (ajout/suppression/réordonnancement).

### Gabarit `page.php` — manque corrigé

Le thème n'avait **aucun `page.php`** : les Pages sans gabarit dédié retombaient sur `index.php`, qui affiche `the_excerpt()` et non `the_content()` — le contenu composé n'aurait jamais été rendu. Le gabarit est ajouté avec deux modes : page composée de blocs FNC (rendu direct, chaque bloc portant déjà ses sections pleine largeur) ou page de contenu simple (habillage sobre en colonne de lecture).

### Bascule des gabarits dédiés

`page-le-forum.php` et `page-contact.php` avaient priorité sur `page.php` : ces pages n'auraient pas pu être composées. Elles cèdent désormais la place au contenu composé **dès que l'éditorial ajoute un bloc FNC** (`fnc_page_has_blocks()`), tout en conservant leur contenu de démonstration tant que ce n'est pas le cas — non destructif.

**Frontière assumée** : les champs « texte riche » sont des zones de texte rendues avec `wpautop()`. Le vrai site utilise un éditeur riche Lexical ; répliquer un éditeur riche multi-champs sans build JS n'apporterait pas assez au regard de la complexité induite sur un produit vitrine.

**Vérifié en conditions réelles** : 14 blocs enregistrés côté serveur et 14 schémas chargés côté éditeur ; page de démonstration composée de 9 blocs rendue correctement (titre sur deux lignes, numérotation automatique 01/02/03, FAQ dépliables, paragraphes multiples, chiffres clés, callout) ; dans l'éditeur, 9 blocs affichés avec 58 champs et **aucun bloc invalide** ; répéteur testé (ajout d'une ligne propagé au store et à l'interface) ; bascule statique → composé vérifiée sur « Le Forum » puis état initial restauré ; aucun fatal/notice/warning dans les logs.

Une page de démonstration reste disponible dans l'installation locale (« Test blocs ») pour visualiser les blocs assemblés.

## Page d'accueil éditable (Lot 3)

Sur le vrai site, la page d'accueil est administrable via l'archétype « Homepage » (`homepageFields.ts`) : le storyboard M1→M8 est **figé** (groupes fixes, pas des blocs réordonnables) et l'éditorial n'administre que le contenu. Le thème avait sa page d'accueil **entièrement codée en dur**.

Les 8 moments sont désormais administrables dans **Apparence → Personnaliser → « Page d'accueil (M1 → M8) »** (`inc/homepage.php`, 8 sections, ~60 réglages). Ce lot passe par le Customizer et non par des blocs : il ne s'agit pas de composition (cf. Lot 2) mais du **paramétrage d'une structure figée** — et le Customizer offre en prime l'aperçu en direct. Chaque champ retombe sur le contenu de la maquette s'il n'est pas renseigné.

### Héros M1 : image, vidéo ou slider

Extension par rapport au vrai site (demande du Décideur) — celui-ci ne propose qu'une image de fond. Le média du héros est configurable en trois modes :

| Mode | Champs | Comportement |
|---|---|---|
| **Image fixe** | Image de fond | Comportement historique |
| **Vidéo** | Fichier vidéo **ou** URL externe, image d'attente (poster) | Muette, en boucle, `playsinline`, `preload="metadata"`, `poster` (évite le décalage de mise en page) |
| **Slider** | Jusqu'à 5 images, durée par image (3–20 s) | Fondu enchaîné, mise en pause automatique quand l'onglet passe en arrière-plan |

**Replis systématiques** : mode vidéo sans média renseigné → image ; mode slider avec une seule image → image simple sans slider. Le héros n'est jamais vide.

**Accessibilité** : les médias du héros sont décoratifs (le texte porte le sens) — `alt` vide et `aria-hidden`. Sous `prefers-reduced-motion`, la vidéo est masquée au profit de son poster et le slider n'affiche que la première image, sans transition ; le script s'abstient également de lancer la lecture ou le défilement.

### Moments alimentés par les vraies données

Comme sur le vrai site, les moments qui présentent des données métier ne sont plus des placeholders : **M3** (voix → fiches Intervenants, avec civilité/organisation/pays), **M5** (programme → sessions de l'édition en cours), **M6** (partenaires → fiches Partenaires, logo si disponible), **M7** (archives → Éditions, triées par année) et **M8** (compte à rebours **réellement calculé** depuis la date de début de l'édition en cours). Chacun retombe sur un état « à confirmer » explicite si la donnée n'existe pas — jamais sur des données inventées présentées comme confirmées.

**Vérifié en conditions réelles** : panneau Customizer chargé (HTTP 200) avec les 8 sections et 61 réglages, dont les 11 champs du héros ; les trois modes testés (image ; vidéo avec attributs `muted`/`loop`/`playsinline`/`poster`/`aria-hidden` corrects ; slider dont la bascule automatique a été observée après l'intervalle configuré) ; les deux replis testés (vidéo sans média, slider à une image) ; règles `prefers-reduced-motion` confirmées chargées ; moments dynamiques vérifiés sur les données de test (intervenant, session, partenaire, édition 2027 et compte à rebours à 230 jours) ; aucune erreur console ni fatal/notice/warning dans les logs.

## Informations pratiques (Lot 4)

Décision de modélisation reprise **à l'identique du vrai site** (`practicalInfo.ts`) : les informations pratiques ne sont ni un texte libre, ni une collection autonome, mais un **agrégat de rubriques rattaché à l'objet pivot Édition**. Chaque rubrique est optionnelle et **masquée si elle est vide** (Content Readiness Gate) — une page à moitié remplie ne montre jamais de section creuse.

**8 rubriques**, composées dans le contenu de la fiche Édition via des blocs dédiés (catégorie « FNC · Informations pratiques ») :

| Rubrique | Contenu |
|---|---|
| Lieu & plan d'accès | Adresse, détails d'accès, plan statique, carte interactive optionnelle |
| Transport & accès | Texte |
| Hébergement | Liste d'hôtels (nom, précision, site web) |
| Visa & formalités | Texte |
| Badge & accréditation | Texte |
| Contacts utiles | Liste libellé / valeur (email cliquable détecté automatiquement) |
| FAQ logistique | Questions dépliables |
| Accessibilité sur site | Texte |

Ces rubriques sont rendues à trois endroits, toujours depuis la même source : la page **Informations pratiques** (édition en cours), la page **Édition en cours**, et la **fiche de l'édition**. Tant qu'aucune rubrique n'est renseignée, l'état d'attente sobre du site réel est conservé.

**Carte privacy-first** : comme sur le vrai site, la carte tierce n'est **chargée qu'au clic explicite** — aucune requête vers le service externe n'est émise avant. L'iframe créée porte `referrerpolicy="no-referrer"`, `loading="lazy"` et un titre décrivant son contenu.

### Gabarit `single-fnc_edition.php` — manque corrigé

Les liens « Voir l'édition » (frise des éditions, archives de l'accueil, partenaires) retombaient sur `index.php`, qui n'affiche qu'un extrait : la fiche était quasi vide. Le gabarit ajouté présente l'identité de l'édition (année, statut, thème, dates, lieu, note d'édition spéciale), son contenu éditorial, ses informations pratiques, ses sessions et ses ressources liées — chaque section masquée si la donnée n'existe pas.

Le contenu éditorial de la fiche est rendu **en excluant les rubriques pratiques** (`fnc_render_content_excluding_practical()`) : composées dans ce même contenu mais affichées dans leur section dédiée, elles seraient sinon rendues deux fois.

**Vérifié en conditions réelles** : les 8 blocs enregistrés (22 blocs FNC au total) ; 5 rubriques de test rendues sur les trois emplacements, une rubrique laissée vide correctement **masquée** ; privacy-first confirmé (aucune iframe ni requête réseau vers le service tiers avant clic, puis iframe créée avec les bons attributs au clic) ; repli « en cours de finalisation » vérifié en vidant les rubriques, puis état restauré ; aucune erreur console ni fatal/notice/warning.

## SEO par page (Lot 5)

Pendant WordPress des `seoFields` de Payload (groupe `seo` étalé dans les collections du vrai site) et de la logique de `generateMetadata()`. Une métaboîte **SEO** est ajoutée sur les Pages et articles natifs **et** sur les six types de contenu du plugin :

| Champ | Rôle |
|---|---|
| Titre SEO | Surcharge le `<title>` et `og:title` |
| Description SEO | Surcharge `description` et `og:description` |
| Ne pas indexer | Ajoute `noindex, nofollow` pour ce document |

**Cascade appliquée** (identique au vrai site) :

- **Titre** : SEO du document → titre du document → titre par défaut du site → nom du site
- **Description** : SEO du document → extrait du document → description par défaut → description d'identité
- **Image de partage** : image mise en avant du document → image OpenGraph par défaut *(convention WordPress : pas de champ média supplémentaire à saisir)*
- **Robots** : `noindex` du document (prioritaire) → directive par défaut du site

La directive robots passe par le filtre natif `wp_robots`, donc **une seule** balise est émise, fusionnée avec celles de WordPress.

**Choix d'implémentation assumé** : ces champs vivent dans le **thème** (`inc/seo.php`) et non dans le plugin de modèle de contenu, alors que le vrai site les porte dans ses collections. Raison : c'est le thème qui produit les balises, et le besoin couvre uniformément les contenus natifs *et* ceux du plugin — garder toute la cascade au même endroit la rend nettement plus simple à raisonner. Les métadonnées et le filtre robots, précédemment dans `functions.php` (Lot 1), y ont été regroupés.

**Vérifié en conditions réelles** : métaboîte présente sur une Page et deux types du plugin ; cascade testée aux trois niveaux (sans surcharge → titre du document ; avec surcharge → titre et description SEO repris dans `<title>`, `og:title`, `description` et `og:description` ; `noindex` du document appliqué sans dupliquer la balise robots) ; image mise en avant confirmée comme `og:image` ; application aux CPT vérifiée sur la fiche d'une édition ; aucune erreur console ni fatal/notice/warning.

## Fiches individuelles des contenus (Lot 6)

Le thème n'avait aucun gabarit `single` : les permaliens de sessions, intervenants et ressources — pourtant utilisés dans le programme, l'annuaire, l'espace presse et les fiches d'édition — retombaient sur `index.php`, qui n'affiche qu'un extrait. Les fiches étaient donc quasi vides pour n'importe quel visiteur. Trois gabarits sont ajoutés (le quatrième, `single-fnc_edition.php`, l'avait été au Lot 4) :

| Fiche | Contenu |
|---|---|
| **Session** | Type, jour, horaire, salle, édition de rattachement, objectifs, note, **modérateur distingué des intervenants**, ressources de l'édition |
| **Intervenant** | Nom avec civilité, organisation, pays (avec drapeaux, plusieurs pays gérés), profil, biographie, liens externes, sessions où la personne intervient — en signalant celles qu'elle **modère** |
| **Ressource** | Type, date, édition liée, catégorie et étiquettes, action adaptée au type (**Regarder** pour vidéo/interview, **Télécharger** si un fichier est renseigné, sinon état d'attente), autres ressources de la même édition |

Chaque information est masquée si la donnée n'existe pas — aucune section creuse.

### Champ fichier ajouté au plugin (v0.3.0)

Le modèle n'avait **aucun champ fichier** pour les ressources : un rapport ou un livre blanc n'avait donc aucun document téléchargeable (seul le champ média vidéo existait). `_fnc_publication_file` est ajouté. C'est un champ URL et non un sélecteur de média : le plugin reste sans dépendance ni JavaScript d'administration — l'éditeur téléverse dans la Médiathèque puis copie l'adresse. Limite assumée et documentée dans le champ lui-même.

### Bug corrigé : relations d'intervenants non trouvées

La liste des sessions d'un intervenant s'appuyait d'abord sur une `meta_query` `LIKE 'i:12;'`. Or les identifiants du tableau sérialisé sont stockés **tantôt en entiers, tantôt en chaînes** selon la voie d'écriture (formulaire, import, wp-cli) : un `LIKE` sur `i:12;` rate `s:2:"12";`, et pouvait en prime matcher un *indice* de tableau plutôt qu'une valeur. La recherche se fait désormais par filtrage PHP avec comparaison d'entiers — plus sûr et plus lisible, le volume restant faible sur une vitrine.

**Vérifié en conditions réelles** : les trois fiches rendues sur des contenus de test ; distinction modérateur/intervenant confirmée sur deux fiches (mention « En modération » présente sur la session modérée, absente sur celle où la personne est simple intervenante) ; le correctif valide justement le cas où l'identifiant était stocké en chaîne ; pays multiples décomposés en deux drapeaux ; action de ressource testée dans ses deux variantes (Télécharger avec fichier, Regarder pour une vidéo, avec `target="_blank"` et `rel="noopener noreferrer"`) ; masquage vérifié sur les données absentes ; aucune erreur console ni fatal/notice/warning.

Les fiches `single-fnc_partenaire.php` et `single-fnc_actualite.php` complètent la couverture : **partenaire** (logo, type d'engagement, description, site web, éditions associées avec niveau) et **actualité** (contenu éditorial en colonne de lecture, date, catégories/étiquettes). Vérifiées en conditions réelles (HTTP 200, type « Sponsor » + niveau « Principal » sur le partenaire de test, contenu et date sur l'actualité de test, aucune erreur PHP).

Les **logos partenaires sont cliquables** vers leur fiche, sur la page Partenaires (logo + titre de carte) comme sur la page d'accueil (moment M6).

**Conflit de routage corrigé (plugin v0.4.1)** : le CPT `fnc_partenaire` avait `has_archive` **et** le slug `partenaires`, en conflit avec la Page « partenaires » (page-partenaires.php) de même slug — après régénération des permaliens, l'archive du CPT masquait la Page. L'archive est désactivée (`has_archive => false`) : la Page reste la vue liste, les fiches restent servies via le rewrite (`/partenaires/{slug}/`). Aucun code du thème ne référençait l'archive du CPT.

Une **vérification systématique des slugs** a confirmé que `partenaires` était le seul conflit Page ↔ archive de CPT (aucune autre Page ne partage le slug d'un CPT, aucune collision entre CPT). Elle a aussi révélé que `fnc_actualite` avait une archive sans gabarit dédié : `archive-fnc_actualite.php` est ajouté (hero, cartes date/titre/catégorie/extrait liées à la fiche, pagination, état vide honnête), complétant les archives des 6 CPT. Les nouvelles chaînes d'interface (archive actualités et fiches single partenaire/actualité) sont incluses dans les traductions.

## Ordre des pays & drapeaux uploadables (Lot 7)

Pendant WordPress du réglage `countryOrder` (onglet « Intervenants » du Global Settings du vrai site). La frise « Pays représentés » de l'annuaire était triée par ordre alphabétique et n'utilisait que les 11 drapeaux SVG intégrés en dur. Elle est désormais éditorialisable via **Personnaliser → Réglages FNC → Intervenants** :

- **Ordre** : un pays par ligne, dans l'ordre voulu. Les pays effectivement présents et listés apparaissent d'abord dans cet ordre, puis les pays restants par ordre alphabétique. Réglage vide → tri alphabétique (comportement historique).
- **Drapeau uploadable** : chaque ligne accepte une URL de drapeau après un `|` (« Congo | https://…/drapeau.svg »). Le drapeau uploadé est prioritaire, avec repli sur le SVG intégré si le pays est reconnu, sinon seul le nom s'affiche — **exactement la logique du composant `CountryFlag.tsx`** du vrai site (source éditoriale prioritaire, repli SVG). Champ URL plutôt que sélecteur de média, cohérent avec le reste du thème (zéro dépendance, pas de JS d'administration).

Le rapprochement des noms est **insensible à la casse, aux espaces et aux accents** (même tolérance que le vrai site) : « congo » dans le réglage ordonne bien le pays affiché « Congo ». Le nouveau helper `fnc_country_flag()` remplace les appels directs à `fnc_country_flag_svg()` dans l'annuaire et la fiche intervenant.

**Vérifié en conditions réelles** : état par défaut (tri alphabétique, 3 drapeaux SVG intégrés) ; ordre éditorial appliqué (Congo remonté en tête, pays non listé rejeté en fin de liste dans l'ordre alphabétique) ; drapeau uploadé rendu en `<img>` pour la France là où les autres restent en SVG intégré ; tolérance de casse confirmée (« congo »/« CAMEROUN » ordonnent les libellés « Congo »/« Cameroun ») ; section Customizer enregistrée (HTTP 200) ; helper utilisé aussi sur la fiche intervenant ; aucune erreur console ni fatal/notice/warning.

## Multilinguisme (Lot 8)

Dépendance ciblée actée à l'ADR-007 (Décision 2 amendée) : **Polylang** (gratuit, GPL), réservé au multilinguisme. Le socle FR/EN est en place et fonctionnel.

### Interface traduite (thème + plugin)

Toutes les chaînes d'interface passent déjà par `__()`/`esc_html_e()`. Une traduction **anglaise (en_GB)** complète est fournie :

| Domaine | Fichiers | Chaînes traduites |
|---|---|---|
| Thème (`fnc-wordpress-theme`) | `languages/*.pot/.po/.mo/.l10n.php` | 509 |
| Plugin (`fnc-content-model`) | `languages/*.pot/.po/.mo/.l10n.php` | 98 (dont les types de session/publication, statuts d'édition, niveaux de partenariat visibles en front) |

L'anglais est en_GB (et non en_US) : WordPress traite en_US comme la langue source « sans traduction », or les chaînes sources du thème sont en **français**. en_GB est donc la locale anglaise fonctionnelle ; les traductions sont d'ailleurs rédigées en anglais britannique.

### Chargement des traductions — contournement documenté

Le chargement passe par `load_textdomain()` ciblant directement le `.mo` par locale (dans `functions.php` et `fnc-content-model.php`), sur les hooks `init` **et** `wp`, plutôt que par `load_theme_textdomain()`. Deux raisons, observées en conditions réelles avec WordPress 6.8 + Polylang :

1. **Timing Polylang** : Polylang ne fixe la langue de la requête frontend qu'**après** `init` (au parsing de la requête). Un chargement uniquement sur `init` figerait le front dans la langue par défaut ; le rechargement sur `wp` resynchronise sur la langue demandée.
2. **Piège de `load_theme_textdomain`** : cette fonction tente d'abord le dossier global (`wp-content/languages/themes/`) ; sur un fichier absent, cette tentative laisse le domaine dans un état qui empêche la seconde tentative (dossier du thème) d'aboutir. Cibler directement le `.mo` du thème l'évite.

La langue source étant le français (aucun `.mo` fr), on décharge le domaine quand aucune traduction n'existe pour la locale courante, pour revenir aux chaînes sources.

### Version WordPress épinglée

`compose.yaml` épingle **WordPress 6.8** (au lieu de `latest`) : le tag `latest` a livré une pré-version 7.0.x dont le système de traduction (`WP_Translation_Controller`) ne suivait pas la locale par requête, cassant le multilinguisme. La 6.x est représentative des sites réels (le thème cible « 6.4+ »).

### Vérifié en conditions réelles

Polylang installé, deux langues créées (FR=fr_FR par défaut, EN=en_GB), pretty permalinks activés. Routing `/` (FR) et `/en/` (EN) confirmé : interface entièrement traduite en anglais sur `/en/` (héros, navigation, footer, CTA, états), français intact sur `/` (non-régression), badges de type de session traduits côté plugin (« Round table » / « Table ronde »), administration accessible.

### Sélecteur de langue

`fnc_language_switcher()` (header desktop + panneau mobile) génère des liens réels vers chaque langue Polylang. Il s'appuie sur l'API de bas niveau (`PLL()->model->get_languages_list()`, `PLL()->links->get_translation_url()`, repli `pll_home_url()`) plutôt que sur `pll_the_languages()`, qui renvoie une liste vide sur les vues sans traduction assignée (l'accueil-liste, notamment). Chaque lien pointe vers la **traduction de la page courante** quand elle existe, sinon vers l'accueil de la langue ; la langue active porte `aria-current="true"` (stylée par le CSS existant) et chaque lien porte `hreflang`. Repli statique FR/EN conservé si Polylang est absent. Vérifié en conditions réelles : liens corrects sur l'accueil et sur une fiche, bascule effective au clic, marquage de la langue courante.

### Chaînes des Réglages FNC (Customizer) traduisibles

Les réglages du Customizer sont des valeurs **uniques** (un seul `theme_mod`, pas une valeur par langue). Les champs de **contenu localisables** (équivalents des champs `localized` du Global Settings de Payload) sont rendus bilingues via le module « Traductions des chaînes » de Polylang :

- `fnc_register_pll_strings()` (`inc/customizer.php`, hook `init`) enregistre chaque valeur renseignée comme chaîne traduisible, dans le groupe **« Forum Numérique Congo »** (Langues → Traductions des chaînes). Champs couverts : slogan, sous-titre, description, présentation courte, adresse, texte et copyright du footer, titre et description SEO par défaut — plus les rôles/organisations des **contacts presse** (répéteur).
- À l'affichage, `fnc_get_setting_i18n()` et le helper `fnc_pll()` passent la valeur par `pll__()` : version dans la langue courante si saisie, sinon valeur source. Sans Polylang, comportement identique à `fnc_get_setting()`.

Câblé : footer (texte, adresse, copyright), métadonnées SEO (`inc/seo.php` : titre et description par défaut, description d'identité), et contacts presse (`page-espace-presse.php`).

**Vérifié en conditions réelles** : traductions anglaises saisies (via `PLL_MO`) pour le texte du footer, le SEO par défaut et un contact presse ; rendu confirmé — `/en/` affiche « A permanent institution reflecting… », « Forum Numérique Congo — example », « Media relations » ; `/` conserve les sources françaises ; 6 chaînes renseignées listées dans l'admin Polylang ; aucune erreur PHP.

### Contenus traduisibles (CPT & taxonomies)

Les custom post types et taxonomies du plugin sont déclarés traduisibles **par code** (`includes/polylang.php`, plugin v0.4.0), via les filtres `pll_get_post_types` / `pll_get_taxonomies` — plutôt que par la configuration de l'administration. La déclaration est ainsi **versionnée** et s'applique dès que Polylang est actif, sans étape manuelle ; sans Polylang, les filtres ne sont jamais appelés (aucun effet, aucune dépendance).

Sont traduisibles : les 6 CPT (éditions, sessions, intervenants, partenaires, publications, actualités) et les 5 taxonomies (catégories, étiquettes, profils, pays, types de partenaire). Chaque contenu porte alors une langue et peut avoir des traductions ; le sélecteur de langue relie automatiquement les versions entre elles.

**Vérifié en conditions réelles** : les 6 CPT et 5 taxonomies confirmés traduisibles (`pll_is_translated_post_type`/`_taxonomy`) ; contenus et termes existants assignés au français ; une **traduction anglaise d'édition** créée et liée — la fiche `/en/editions/…` affiche le contenu anglais (thème, lieu, dates localisées, badge « CURRENT ») et l'interface anglaise ; le sélecteur relie les deux fiches dans les deux sens (FR ↔ EN, sur la même entité, pas seulement l'accueil) ; les archives filtrent par langue (`/editions/` ne liste que le FR, `/en/editions/` que le EN) ; aucune erreur PHP.

> **Note d'exploitation** : après avoir rendu des CPT traduisibles, régénérer les permaliens (Réglages → Permaliens, ou `wp rewrite flush`) pour que Polylang ajoute les règles de réécriture des URLs traduites (`/en/…`) — sans quoi les fiches traduites renvoient une 404.

### Installation

Polylang n'est pas versionné (plugin tiers, hors dépôt) : il doit être installé et activé sur chaque instance, puis les deux langues créées (FR par défaut, EN=en_GB) et la langue par défaut assignée aux contenus existants.

## Prochaines étapes

Plan de mise en œuvre de l'ADR-007 :
1. ~~Créer le dépôt~~ (fait)
2. ~~Scaffolder la structure thème~~ (fait)
3. ~~Scaffolder le plugin~~ (fait — CPTs, taxonomies, relations en meta)
4. ~~Brancher le thème sur les données du plugin~~ (fait — tous les gabarits interrogent les vraies données)

Réconciliation avec les surfaces administrables du vrai site (sept lots, tous livrés) :
1. ~~Réglages globaux (Customizer)~~
2. ~~Composition de pages par blocs Gutenberg~~
3. ~~Page d'accueil éditable (héros image/vidéo/slider)~~
4. ~~Informations pratiques rattachées à l'édition~~
5. ~~SEO par page~~
6. ~~Fiches individuelles des contenus~~
7. ~~Ordre des pays + drapeaux uploadables~~

Reste :
- **Multilinguisme** (Polylang ou équivalent GPL) — dépendance ciblée actée à l'ADR-007, non encore intégrée.
- **Dette connue** : le champ fichier des ressources et les drapeaux de pays sont des champs URL (téléversement via la Médiathèque puis copie de l'adresse) plutôt que des sélecteurs de média, par choix « zéro dépendance / zéro JS d'administration » ; la traduction éditoriale du contenu de démonstration reste à saisir (la brique technique est en place).
