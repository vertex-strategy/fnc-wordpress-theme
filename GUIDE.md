# Forum Numérique Congo — Guide du template WordPress

Documentation d'installation et de configuration du template **Forum Numérique Congo** :
le site institutionnel bilingue (français / anglais) du Forum — éditions, intervenants,
programme, ressources, partenaires, actualités et demandes en ligne.

> **L'esprit du template.** La mise en forme et l'identité visuelle sont **intégrées et
> verrouillées** : vous administrez le **contenu** depuis l'éditeur WordPress, sans écrire
> de code et sans risquer de casser le design. Tout ce qui se voit sur le site s'édite
> depuis l'administration.

Le template se compose de deux éléments :

| Élément | Rôle |
|---|---|
| **Thème « Forum Numérique Congo »** | L'apparence, les gabarits de page et les animations. |
| **Extension « FNC Core »** | Les contenus (éditions, intervenants, sessions…), les formulaires, la mesure d'audience et l'édition des pages. |

---

## 1. Prérequis

- **WordPress** 6.0 ou plus récent, **PHP** 7.4 ou plus récent.
- **Extensions à activer :**
  - **FNC Core** (fournie avec le template) — indispensable.
  - **Polylang** — gestion du bilingue français / anglais.
  - Un service **SMTP** (extension au choix) — pour l'envoi fiable des accusés de réception.
  - *(Optionnel)* Une extension de champs personnalisés compatible ACF — par exemple
    **Secure Custom Fields** (gratuite, maintenue par WordPress.org). **Non requise** :
    l'édition des pages, des héros et des blocs fonctionne **nativement** dans l'éditeur
    WordPress et le personnalisateur ; cette extension n'ajoute qu'un confort de saisie.

---

## 2. Installation

1. **Thème** : Apparence → Thèmes → *Ajouter* → installer et **activer** « Forum Numérique Congo ».
2. **Extensions** : Extensions → *Ajouter* → installer et **activer** FNC Core et Polylang
   (plus, en option, une extension de champs compatible ACF comme *Secure Custom Fields*).
3. **Langues** : dans Polylang, définir **Français** (par défaut) et **Anglais**.
4. **Permaliens** : Réglages → Permaliens → choisir « Titre de la publication » (recommandé).

Une fois activés, deux menus apparaissent dans l'administration : **Réglages → FNC** (contenu du
site) et **Réglages → FNC (fonctionnalités)** (activation des inscriptions et des actualités).

---

## 3. Premiers réglages du site  *(Réglages → FNC)*

Renseignez une seule fois les informations transverses du portail :

- **Identité** : nom officiel, slogan, sous-titre, description.
- **Coordonnées** : e-mail de contact, téléphone, adresse.
- **Réseaux sociaux** : une ligne par réseau (`plateforme | libellé | adresse`).
- **Logos & favicon**.
- **Menu principal** : les entrées de navigation (ordre, libellés, liens internes ou externes).
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
Éditions → *Ajouter*. Renseignez l'**année**, le **thème**, les **dates de début et de fin**,
le **lieu**, puis réglez le **statut** sur **En cours**. Publiez.
→ La page d'accueil, le compte à rebours et le hub « Édition en cours » se mettent à jour.

### 5.2 Ajouter un intervenant
Intervenants → *Ajouter*. Renseignez :
- **Nom**, **fonction / titre**, **organisation**, **pays** (séparez par « / » si plusieurs),
- **Profil** (par exemple *Officiel*, *Expert*, *Animateur*) — sert aux filtres,
- **Portrait** (image mise en avant) et ses **liens** éventuels,
- **Ordre protocolaire** si un ordre d'affichage précis est requis.

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

### 5.6 Ajouter un partenaire
Partenaires → *Ajouter* : **nom**, **type d'engagement**, **logo** et description. La page
**Partenaires** les regroupe par type.

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
  message honnête et n'accepte aucune demande.
- **Actualités** : cochez pour **afficher** la rubrique Actualités.

Les demandes envoyées via les formulaires (contact, inscription, demande de partenariat)
arrivent dans **Soumissions** (menu de l'administration). Un accusé de réception est envoyé
automatiquement à l'expéditeur ; la demande est toujours enregistrée, même si l'e-mail échoue.

### 5.10 Confidentialité & bandeau cookies
La mesure d'audience est **anonyme et sans cookie** par défaut — elle fonctionne pour tout le
monde et respecte la vie privée. Un **cookie de mesure** n'est déposé **qu'après** un « Autoriser »
explicite. Le bandeau propose **Refuser** et **Autoriser** avec le même poids, et un lien de
pied de page permet de rouvrir le choix à tout moment. Aucune configuration n'est nécessaire côté
éditorial.

---

## 6. Site bilingue (français / anglais)
Chaque contenu et chaque page se traduit via **Polylang** : créez la version anglaise depuis
l'icône de langue de la fiche. Les libellés d'interface (menus, boutons) sont fournis dans les
deux langues. Pensez à traduire les pages composées (leur contenu est propre à chaque langue).

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

*Template **Forum Numérique Congo** — thème + extension « FNC Core ».*
*© 2026 **Grinso & Associés** — [www.grinso.io](https://www.grinso.io). Tous droits réservés.*
*Développé par **Vanel NGOYO ADOUMA**, Lead développeur.*
