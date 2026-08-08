# Forum Numérique Congo — Guide du template WordPress

Documentation d'installation et de configuration du template **Forum Numérique Congo** :
le site institutionnel bilingue (français / anglais) du Forum — éditions, intervenants,
programme, ressources, partenaires, actualités et demandes en ligne.

> **L'esprit du template.** La mise en forme et l'identité visuelle sont **intégrées et
> verrouillées** : vous administrez le **contenu** depuis l'éditeur WordPress, sans écrire
> de code et sans risquer de casser le design. Tout ce qui se voit sur le site s'édite
> depuis l'administration.

Le template se compose de **trois éléments** :

| Élément | Rôle |
|---|---|
| **Thème « Forum Numérique Congo »** | L'apparence, les gabarits de page et les animations. |
| **Extension « FNC Content Model »** | Le modèle de données : types de contenu (éditions, intervenants, sessions…), taxonomies, relations, statut *archivé*. |
| **Extension « FNC Core »** | La logique du site : réglages, données dérivées (programme, annuaire, compteurs), formulaires, mesure d'audience, SEO. |

> Voir aussi : **`INSTALL.md`** (installation pas à pas) et **`README.md`** (référence
> technique développeur).

---

## 1. Prérequis

- **WordPress** 6.0 ou plus récent, **PHP** 7.4 ou plus récent.
- **Extensions à activer :**
  - **FNC Content Model** et **FNC Core** (fournies avec le template, à activer dans cet
    ordre) — indispensables.
  - **Polylang** — gestion du bilingue français / anglais.
  - Un service **SMTP** (extension au choix) — pour l'envoi fiable des accusés de réception.
  - *(Optionnel)* Une extension de champs personnalisés compatible ACF — par exemple
    **Secure Custom Fields** (gratuite, maintenue par WordPress.org). **Non requise** :
    l'édition des pages, des héros et des blocs fonctionne **nativement** dans l'éditeur
    WordPress et le personnalisateur ; cette extension n'ajoute qu'un confort de saisie.
  - *(Recommandé pour le SEO)* **All in One SEO (AIOSEO)** — gratuit. Gère les titres,
    descriptions, image de partage (OpenGraph) et le plan de site. **Le thème s'adapte
    automatiquement** : dès qu'AIOSEO (ou Yoast) est actif, il lui laisse le `<title>`,
    la meta description, l'OpenGraph, la canonique et le schema Organization/WebSite —
    aucune balise en double. Le thème conserve seulement le schema **Event** de l'édition
    (dates, lieu, intervenants), qu'AIOSEO ne sait pas produire. Sans plugin SEO, le thème
    assure lui-même un référencement de base (voir §7.1).

    > Après activation d'AIOSEO : lancer son assistant (type **Organisation**, nom
    > « Forum Numérique Congo », logo, **image OG 1200×630**, profils sociaux), activer sa
    > **compatibilité Polylang** (pour n'avoir qu'une série de balises hreflang) et le
    > laisser gérer le **plan de site** (`/sitemap.xml`).

---

## 2. Installation

1. **Thème** : Apparence → Thèmes → *Ajouter* → installer et **activer** « Forum Numérique Congo ».
2. **Extensions** : Extensions → *Ajouter* → installer et **activer** FNC Core et Polylang
   (plus, en option, une extension de champs compatible ACF comme *Secure Custom Fields*).
3. **Langues** : dans Polylang, définir **Français** (par défaut) et **Anglais**.
4. **Permaliens** : Réglages → Permaliens → choisir « Titre de la publication » (recommandé).

Une fois activés, deux menus apparaissent dans l'administration : **Réglages → FNC** (contenu du
site) et **Réglages → FNC (fonctionnalités)** (activation des inscriptions et des actualités).

### 2.1 Contenu de démonstration *(Apparence → Contenu de démonstration)*

Sur une installation neuve, vous pouvez **découvrir la structure du site** en installant un jeu
de contenu de démonstration complet — éditions, intervenants, sessions, partenaires et
publications, avec leurs images et leurs liaisons.

**Installer la démonstration.**
1. Terminez d'abord l'installation ci-dessus (thème + extensions + langues Polylang).
2. Ouvrez **Apparence → Contenu de démonstration**.
3. Cliquez sur **« Importer le contenu de démonstration »**.

La page vérifie les prérequis avant l'import (extension de contenu requise ; Polylang recommandé
pour obtenir la version **bilingue français + anglais**). L'opération est **sûre et rejouable** :
la relancer met à jour le contenu de démonstration, sans jamais créer de doublon.

**Retirer la démonstration.**
Une fois votre **vrai contenu** saisi, revenez sur la même page et utilisez, tout en bas, le
bouton **« Retirer le contenu de démonstration »** (zone en rouge). Il supprime définitivement
tout le contenu installé par l'import — dans les **deux langues** — ainsi que les **images**
importées avec lui. Il ne touche à **aucun** autre contenu ni média que vous avez ajouté.

> **À savoir.** Le retrait supprime aussi un élément de démonstration que vous auriez modifié,
> tant qu'il reste marqué « démonstration ». Si vous partez d'un contenu de démo pour bâtir un
> contenu réel, préférez **le modifier en profondeur et le conserver** plutôt que de lancer le
> retrait global. L'action de retrait demande une confirmation et est **irréversible**.

---

## 3. Premiers réglages du site  *(Réglages → FNC)*

Renseignez une seule fois les informations transverses du portail :

- **Identité** : nom officiel, slogan, sous-titre, description.
- **Coordonnées** : e-mail de contact, téléphone, adresse.
- **Réseaux sociaux** : une ligne par réseau (`plateforme | libellé | adresse`).
- **Logos & favicon**.
- **Menu principal** : les entrées de navigation (ordre, libellés, liens internes ou externes).
- **Pied de page → Groupes de liens** : les colonnes de liens du bas de page. Une ligne
  d'en-tête `Titre | genre` ouvre un groupe (genre optionnel : main, resources, press,
  legal, useful, institutional, custom) ; les lignes `- Libellé | lien` qui suivent sont
  ses liens, dans l'ordre. Options en 3ᵉ champ : `newtab` (nouvel onglet), `off` (masqué).
  Une ligne `// …` est une note interne. **Laissé vide → colonnes par défaut** (le pied de
  page n'est jamais sans navigation).
- **SEO par défaut** : titre et description affichés lors des partages.

> Ces valeurs alimentent l'en-tête, le pied de page et les informations de contact partout
> sur le site. Tant qu'une coordonnée n'est pas renseignée, elle n'est simplement pas affichée.

---

## 4. Comprendre le contenu

Le site s'organise autour de types de contenu dédiés (menu latéral de l'administration) :

| Type | À quoi ça sert |
|---|---|
| **Éditions** | Chaque édition du Forum (année, thème, dates, lieu, statut). |
| **Intervenants** | Les personnes qui interviennent (portrait, fonction, pays, profil). |
| **Sessions** | Les moments du programme (plénières, tables rondes, ateliers). |
| **Publications** | Ressources et documents officiels (rapports, communiqués…). |
| **Partenaires** | Institutions, organisateurs et soutiens. |
| **Actualités** | Brèves de la vie du Forum entre les éditions. |

**Le principe des liaisons.** Une **session** se rattache à une **édition** et cite ses
**intervenants** (et son modérateur). C'est à partir de ces liaisons que le site construit
**automatiquement** le programme, la liste des intervenants, les pays représentés et les
compteurs — vous n'assemblez jamais ces pages à la main.

**L'édition « en cours ».** Chaque édition a un **statut** : *En cours*, *À venir* ou *Passée*.
L'édition marquée **En cours** pilote la page d'accueil, le compte à rebours et le hub
« Édition en cours ». Une seule édition est « en cours » à la fois.

---

## 5. Tâches éditoriales

### 5.1 Définir l'édition en cours
Éditions → *Ajouter*. Dans **« Statut et informations d'édition »** (colonne de droite),
renseignez l'**année**, le **thème**, les **dates de début et de fin**, le **lieu**, puis réglez
le **statut** sur **En cours**. La **description générale** se saisit dans l'éditeur principal, le
**visuel de couverture** via l'**image mise en avant**. Publiez.
→ La page d'accueil, le compte à rebours et le hub « Édition en cours » se mettent à jour.

**Rétrospective (édition passée).** Le panneau **« Rétrospective (édition passée) »** (sous
l'éditeur) alimente le bas de la fiche de l'édition. Ces sections ne s'affichent **que pour une
édition « passée »** — une édition **« en cours »** ne montre jamais sa rétrospective (sa fiche
renvoie au hub / à l'agenda). Chaque section est masquée si elle est vide :
- **Bilan / rétrospective** — texte libre ;
- **Chiffres clés** — une ligne par chiffre au format `Valeur|Libellé` (ex. `1 200|Participants`).
  **La valeur ET le libellé sont requis** : une ligne incomplète est ignorée ;
- **Galerie** — une image par ligne au format **`URL|texte alternatif`**
  (ex. `https://…/photo.jpg|Plénière d'ouverture` ; téléversez d'abord dans la Médiathèque).
  Le **texte alternatif est obligatoire** (accessibilité) : une ligne sans alt est ignorée.

  *(La fiche d'une édition passée affiche aussi automatiquement, s'ils existent, ses **partenaires
  par niveau**, ses **ressources** et ses **vidéos** liées — rien à saisir ici, c'est dérivé.)*

### 5.2 Ajouter un intervenant
Intervenants → *Ajouter*. Renseignez :
- **Nom**, **civilité** (Pr./Dr./M./Mme…), **Fonction** (l'intitulé exact, affiché sous le nom
  partout), **organisation**, **pays** (séparez par « / » si plusieurs),
- **Profil** (par exemple *Officiel*, *Expert*, *Animateur*) — sert aux **filtres et au badge**
  (il ne change **pas** l'ordre : voir ci-dessous),
- **Portrait** (image mise en avant),
- **Liens** — champs dédiés **Site web**, **LinkedIn**, **X (Twitter)** (icônes automatiques au
  rendu), plus une zone **« Autres liens »** (une ligne par lien, format `Libellé|URL`).

**Drapeaux des pays.** Sur la page Intervenants, le drapeau de chaque pays s'affiche
**automatiquement** à partir du nom saisi (« France », « Congo », « RDC », « États-Unis »,
« Côte d'Ivoire »… — la casse et les accents sont indifférents ; un pays écrit de deux façons
proches ne compte qu'une fois). Aucun réglage à faire. Pour imposer un drapeau précis, téléversez-le
dans **Personnaliser → FNC → Intervenants** (« Ordre des pays représentés »).

**Ordre d'affichage (partout : annuaire, participants, aperçus).** Deux champs, du plus
prioritaire au moins prioritaire :
- **Rang protocolaire** — le plus petit d'abord (les officiels reçoivent un rang bas pour
  passer devant). Laissé vide, l'intervenant est rejeté en fin de liste.
- **Ordre secondaire** — départage deux intervenants de **même rang protocolaire** (le plus
  petit d'abord), puis, à égalité, l'ordre alphabétique.

**Mise en avant dans le carrousel « Les voix » (accueil).** Cochez **« Mettre en avant »** pour
faire passer un intervenant **en tête du carrousel** de la page d'accueil, indépendamment de
l'ordre général ; le **« Rang de mise en avant »** ordonne les intervenants ainsi promus. Le
carrousel affiche au plus **10 voix** (les promus d'abord, puis le reste dans l'ordre général).
Un intervenant promu doit être **participant de l'édition en cours** pour apparaître (rattaché,
comme intervenant ou modérateur, à une **session publiée** de l'édition active). Si vous cochez
« Mettre en avant » sans que ce soit le cas, l'éditeur affiche un **avertissement** rappelant que
la voix n'apparaîtra pas tant qu'elle n'est pas rattachée.

**Autorisation d'image (droit à l'image).** Le portrait n'est publié **que si** l'autorisation
est **obtenue** et non expirée. Renseignez le statut de l'autorisation (*non vérifié / obtenue /
refusée / expirée*) et, le cas échéant, sa **date d'expiration**. À défaut, le site affiche
automatiquement un **monogramme** (les initiales) à la place de la photo — jamais d'image publiée
sans droit.

### 5.3 Créer une session et faire les liaisons
Sessions → *Ajouter*. Renseignez le **titre**, le **type** (plénière, table ronde, atelier), le
**jour** (1, 2, 3), l'**horaire** (début / fin), la **salle**, puis reliez :
- l'**édition** concernée,
- le **modérateur** et les **intervenants**.
Ajoutez au besoin la **description**, les **objectifs** et les **mots-clés**. Publiez.

### 5.4 Le programme se compose tout seul
La page **Programme** regroupe les sessions **publiées** par **jour**, triées par **horaire**.
Vous n'avez rien à assembler : ajoutez/éditez des sessions, la page suit.

### 5.5 Publier une ressource
Publications → *Ajouter* : **titre**, **type**, contenu et fichiers. La page **Ressources**
propose automatiquement la recherche et les filtres par type.

> **Fichier à télécharger.** Une ressource peut être publiée **sans** fichier. Mais si un
> fichier est renseigné, il doit être un **PDF** — sauf pour les types **Vidéo** et
> **Interview**, qui s'appuient sur le champ **URL média**. Une ressource publiée avec un
> fichier non-PDF est automatiquement **remise en brouillon**, avec un message d'explication :
> fournissez un PDF, videz le champ fichier, ou choisissez le type Vidéo/Interview.

### 5.6 Ajouter un partenaire
Partenaires → *Ajouter* : **nom**, **type d'engagement** (Institutionnel / Organisateur /
Soutien / Sponsor), **logo** et description. La page **Partenaires** les **regroupe par type**
dans l'ordre Institutionnel → Organisateur → Soutien → Sponsor.

**Ordre d'affichage des logos** — champ **« Ordre d'affichage »** (le plus petit d'abord). Il
pilote à la fois la page Partenaires et le bloc « La communauté » de l'accueil. Sur l'accueil,
seuls les partenaires **munis d'un logo** apparaissent : **3 grands en tête** (donnez 0, 1, 2 aux
deux organisateurs + le sponsor officiel) puis jusqu'à 5 plus petits — **8 au maximum**.

### 5.7 Publier une actualité
Actualités → *Ajouter* : **titre**, **catégorie**, image et contenu.
La surface publique des actualités s'active dans **Réglages → FNC (fonctionnalités)** (voir §5.9).

### 5.8 Éditer une page
Les pages (Le Forum, Contact, Infos pratiques, pages légales…) se règlent dans **Pages**. Chaque
page a un **type de page** (colonne latérale) qui détermine ce que vous pouvez éditer :

- **Page liste** (Intervenants, Programme, Ressources…) : vous réglez le **héros** (image,
  sur-titre, titre, introduction), le bloc d'introduction et le message d'**état vide**. Les
  éléments listés viennent des types de contenu. *Si vous ne renseignez rien, le héros affiche
  déjà l'image et le titre officiels de la page.*
- **Page institutionnelle** (Le Forum, Contact) : vous **composez** la page à partir d'une palette
  de blocs prêts à l'emploi (ouverture, mission, objectifs, FAQ, manifeste, mot du Président,
  appel à action). La mise en forme est figée ; vous saisissez le contenu et les médias.
- **Page légale** : un simple éditeur de texte enrichi.

> Vous ne pouvez pas déplacer la structure graphique ni changer les couleurs : c'est volontaire,
> pour préserver l'identité visuelle du Forum sur toutes les pages.

### 5.9 Ouvrir les inscriptions & recevoir les demandes
Dans **Réglages → FNC (fonctionnalités)** :
- **Inscriptions** : cochez pour **ouvrir** la page d'inscription. Fermée, la page affiche un
  message honnête, n'accepte aucune demande, le bouton « S'inscrire » disparaît partout et la page
  est retirée des moteurs de recherche.
- **Actualités** : cochez pour **afficher** la rubrique Actualités. Décochée, la liste et les
  articles renvoient une page « introuvable » et n'apparaissent ni dans les menus ni dans le plan
  du site.

Les demandes envoyées via les formulaires (contact, inscription, demande de partenariat)
arrivent dans **Soumissions** (menu de l'administration). Un accusé de réception est envoyé
automatiquement à l'expéditeur ; la demande est toujours enregistrée, même si l'e-mail échoue.

### 5.10 Confidentialité & bandeau cookies
La mesure d'audience est **anonyme et sans cookie** par défaut — elle fonctionne pour tout le
monde et respecte la vie privée. Un **cookie de mesure** n'est déposé **qu'après** un « Autoriser »
explicite. Le bandeau propose **Refuser** et **Autoriser** avec le même poids, et un lien de
pied de page permet de rouvrir le choix à tout moment. Aucune configuration n'est nécessaire côté
éditorial.

### 5.11 Archiver un contenu (retirer sans supprimer)
Chaque contenu du Forum (édition, intervenant, session, partenaire, publication, actualité) peut
prendre le statut **« Archivé »**, en plus de *Brouillon* et *Publié*. Un contenu **archivé** est
**retiré du site public** (invisible en front, exclu de la recherche) mais **conservé** — distinct
de la corbeille. Utile pour retirer proprement un contenu daté sans le perdre.

Pour archiver : depuis la **liste** du type de contenu, **Modification rapide** → menu *Statut* →
**Archivé** (également disponible dans le menu Publier de l'éditeur classique). Repasser en
*Publié* le remet en ligne.

---

## 6. Site bilingue (français / anglais)
Chaque contenu et chaque page se traduit via **Polylang** : créez la version anglaise depuis
l'icône de langue de la fiche. Les libellés d'interface (menus, boutons) sont fournis dans les
deux langues.

> **Convention pour les pages composées (blocs).** Créez toujours la traduction **depuis la
> version française** (icône « + » de la langue cible) : la nouvelle page démarre avec la
> **même structure de blocs** que le français. **Traduisez le texte sur place**, sans
> réorganiser, ajouter ni supprimer de blocs différemment d'une langue à l'autre. Ainsi les
> deux versions restent alignées. Le contenu de chaque page reste propre à sa langue : une
> modification de structure côté français n'est **pas** reportée automatiquement — reprenez
> la traduction si vous changez l'ossature. *(L'ordre éditorial des listes — intervenants,
> partenaires — est, lui, synchronisé automatiquement entre les langues.)*

> **Textes de la page d'accueil (sections M1–M8).** Les sur-titres, titres, taglines, manifeste
> et libellés de boutons de l'accueil se saisissent dans **Apparence → Personnaliser** (réglages
> FNC de la page d'accueil). Leur **traduction anglaise** se fait dans **Réglages → Langues →
> Traductions de chaînes** (Polylang), groupe **« FNC Accueil »** : le français saisi au
> Personnalisateur y apparaît, avec sa colonne anglaise à compléter. *(La chaîne à traduire
> apparaît dans la liste dès que la page d'accueil a été affichée au moins une fois.)*

---

## 7. Bonnes pratiques
- **Images** : privilégiez des visuels nets et bien cadrés ; renseignez toujours le **texte
  alternatif** (accessibilité et référencement).
- **Droit à l'image** : ne réglez le statut sur « obtenue » que lorsque l'autorisation est
  réellement acquise. Le monogramme est une valeur de repli parfaitement acceptable.
- **Contenu vrai** : ne saisissez pas de coordonnées ou de contacts fictifs ; un champ vide n'est
  simplement pas affiché.
- **Publication** : un contenu en **brouillon** n'apparaît pas sur le site ; passez-le en
  **publié** quand il est prêt.

---

## 8. Questions fréquentes

**Une page affiche « en préparation ».**
Le contenu correspondant n'est pas encore publié (édition, sessions, intervenants…). Publiez les
éléments concernés.

**Le portrait d'un intervenant n'apparaît pas.**
L'autorisation d'image n'est pas « obtenue » ou a expiré : le site affiche le monogramme. Mettez à
jour le statut de l'autorisation.

**Le programme est vide.**
Aucune session publiée n'est encore reliée à l'édition en cours. Créez des sessions et reliez-les
à l'édition et à ses intervenants.

**La page d'inscription refuse les demandes.**
Les inscriptions sont fermées : cochez « Inscriptions » dans *Réglages → FNC (fonctionnalités)*.

**Comment changer l'image ou le titre d'un héros de page ?**
Ouvrez la page correspondante (type « Page liste ») et renseignez les champs *Héros*. Sans saisie,
l'image et le titre officiels s'affichent par défaut.

---

*Template **Forum Numérique Congo** — thème + extensions « FNC Content Model » et « FNC Core ».*
*© 2026 **Grinso & Associés** — [www.grinso.io](https://www.grinso.io). Tous droits réservés.*
*Développé par **Vanel NGOYO ADOUMA**, Lead développeur.*
