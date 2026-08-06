<?php
/**
 * Forum Numérique Congo — point d’entrée du thème (configuration, scripts, navigation, aides d’affichage).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_THEME_VERSION', '1.0.20' );

/**
 * Réglages globaux du site (WordPress Customizer) — pendant du Global
 * « Réglages du site » du CMS. Voir inc/customizer.php.
 */
require_once get_template_directory() . '/inc/customizer.php';

/**
 * Blocs éditoriaux (composition de pages) — pendant des palettes de blocs
 * le CMS. Voir inc/blocks.php.
 */
require_once get_template_directory() . '/inc/blocks.php';

/**
 * Page d'accueil editable — storyboard fige M1 -> M8 (heros image/video/
 * slider inclus). Voir inc/homepage.php.
 */
require_once get_template_directory() . '/inc/homepage.php';

/**
 * SEO par document (titre, description, noindex) et rendu des metadonnees,
 * avec cascade vers les valeurs par defaut du site. Voir inc/seo.php.
 */
require_once get_template_directory() . '/inc/seo.php';

/**
 * Rendu partage des formulaires (réception des formulaires) et des coordonnees (Reglages),
 * reutilise par les gabarits ET par les blocs fnc/form et fnc/coordonnees.
 */
require_once get_template_directory() . '/inc/forms.php';

/**
 * Heros editables (Customizer) des pages a liste dynamique : hero administrable,
 * liste generee automatiquement. Voir inc/hero-settings.php.
 */
require_once get_template_directory() . '/inc/hero-settings.php';

/**
 * Titres de section editables (Customizer) des pages a liste. Voir
 * inc/section-titles.php.
 */
require_once get_template_directory() . '/inc/section-titles.php';

/**
 * Guide d'edition (widget du tableau de bord) : « par ou editer quoi », avec
 * liens directs — contre la fragmentation. Voir inc/admin-guide.php.
 */
require_once get_template_directory() . '/inc/admin-guide.php';

/**
 * Importateur de contenu de démonstration (« starter kit ») : page sous
 * Apparence qui installe en un clic le jeu de données de démo (bilingue si
 * Polylang actif). Voir inc/demo-import.php.
 */
require_once get_template_directory() . '/inc/demo-import.php';

/**
 * Rendu des sections composees (édition des pages) : adaptateur
 * layouts ACF -> fonctions de rendu de blocs verifiees. Voir inc/page-sections.php.
 */
require_once get_template_directory() . '/inc/page-sections.php';

/**
 * l’édition des pages (FNC Core) : base d'URL des images de hero par defaut. fnc_page_hero()
 * resout ainsi l'image de route (« intervenants.png ») vers les assets du theme.
 * NB : le dossier reel est /assets/images (et non /assets/img/da de l'exemple).
 */
add_filter(
	'fnc_hero_image_base_url',
	static function () {
		return get_template_directory_uri() . '/assets/images';
	}
);

/**
 * Theme setup : support des fonctionnalites WordPress utilisees par les gabarits.
 */
function fnc_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support( 'automatic-feed-links' );

	// Styles du thème injectés dans l'éditeur de blocs : les blocs dynamiques
	// (ServerSideRender) s'affichent avec la vraie DA dans le canevas, pour une
	// édition WYSIWYG plutôt qu'un simple formulaire.
	add_editor_style(
		array(
			'style.css',
			'assets/css/wordpress-catchup.css',
			'assets/css/wordpress-tailwind-utilities.css',
			'assets/css/wordpress-catchup-p2.css',
			'assets/css/wordpress-catchup-complet.css',
		)
	);
}
add_action( 'after_setup_theme', 'fnc_theme_setup' );

/**
 * Gabarit par slug pour les pages TRADUITES.
 *
 * Une page anglaise porte un slug distinct (« le-forum-en ») afin d'éviter le
 * conflit Polylang du même slug dans deux langues (qui provoque une redirection
 * 301 vers la version française). Mais elle doit utiliser le MÊME gabarit
 * page-{slug}.php que sa version FR. On résout donc le slug de la traduction en
 * langue par défaut (FR) et on charge page-{slug-FR}.php si elle existe. Sans
 * effet sur les pages FR (leur propre slug est déjà le bon).
 */
function fnc_translated_page_template( $template ) {
	if ( ! is_page() || ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_default_language' ) ) {
		return $template;
	}
	$fnc_id      = get_queried_object_id();
	$fnc_default = pll_get_post( $fnc_id, pll_default_language() );
	if ( ! $fnc_default || (int) $fnc_default === (int) $fnc_id ) {
		return $template; // Déjà en langue par défaut : rien à faire.
	}
	$fnc_slug    = get_post_field( 'post_name', $fnc_default );
	$fnc_located = $fnc_slug ? locate_template( array( "page-{$fnc_slug}.php" ) ) : '';
	return $fnc_located ? $fnc_located : $template;
}
add_filter( 'template_include', 'fnc_translated_page_template' );

/**
 * La page courante est-elle la page « Inscription » (FR ou sa traduction) ?
 *
 * @return bool
 */
function fnc_is_registration_page() {
	if ( ! is_page() ) {
		return false;
	}
	$id = (int) get_queried_object_id();
	if ( ! $id ) {
		return false;
	}
	if ( function_exists( 'fnc_page_for_route' ) ) {
		$page = fnc_page_for_route( 'inscription' );
		if ( $page && (int) $page->ID === $id ) {
			return true;
		}
	}
	// Repli slug (sans le plugin) : « inscription » / « inscription-en » / « registration ».
	$slug = get_post_field( 'post_name', $id );
	return in_array( $slug, array( 'inscription', 'inscription-en', 'registration' ), true );
}

/**
 * Application des feature flags aux niveaux PAGE et SEO (le niveau API est géré
 * par FNC Core, le niveau CTA par les gabarits). Miroir du comportement Next :
 * une surface sous flag fermé renvoie un état honnête + noindex + hors sitemap.
 *
 *   - ACTUALITÉS (`fnc_news_enabled` faux) : /actualites (liste + fiche) → 404.
 *   - INSCRIPTION (`fnc_registration_enabled` faux) : la page rend un état
 *     « fermé » (géré dans le gabarit) et devient noindex.
 *
 * Exécuté sur `template_redirect`, AVANT le choix du gabarit et l'émission de
 * `wp_head`, pour que le 404 et la directive robots s'appliquent réellement.
 *
 * @return void
 */
function fnc_feature_flag_gate() {
	if ( is_admin() ) {
		return;
	}

	// Actualités fermées → 404 complet (WordPress chargera 404.php).
	if ( function_exists( 'fnc_news_enabled' ) && ! fnc_news_enabled()
		&& ( is_singular( 'fnc_actualite' ) || is_post_type_archive( 'fnc_actualite' ) ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}

	// Inscription fermée → la page reste affichée (état « fermé »), mais noindex.
	if ( function_exists( 'fnc_registration_enabled' ) && ! fnc_registration_enabled()
		&& fnc_is_registration_page() ) {
		add_filter( 'fnc_force_noindex', '__return_true' );
	}
}
add_action( 'template_redirect', 'fnc_feature_flag_gate' );

/**
 * Exclusion du sitemap natif WordPress pour les surfaces sous flag fermé :
 * le CPT actualités (entier) et la page inscription.
 *
 * @param array<string,\WP_Post_Type> $post_types
 * @return array<string,\WP_Post_Type>
 */
function fnc_sitemap_filter_post_types( $post_types ) {
	if ( function_exists( 'fnc_news_enabled' ) && ! fnc_news_enabled() ) {
		unset( $post_types['fnc_actualite'] );
	}
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'fnc_sitemap_filter_post_types' );

/**
 * Retire la page inscription du sitemap quand les inscriptions sont fermées.
 *
 * @param array<int,array<string,mixed>> $entries
 * @param string                         $post_type
 * @return array<int,array<string,mixed>>
 */
function fnc_sitemap_filter_entries( $entries, $post_type ) {
	if ( 'page' !== $post_type ) {
		return $entries;
	}
	if ( function_exists( 'fnc_registration_enabled' ) && fnc_registration_enabled() ) {
		return $entries;
	}
	if ( ! function_exists( 'fnc_page_for_route' ) ) {
		return $entries;
	}
	$page = fnc_page_for_route( 'inscription' );
	if ( ! $page ) {
		return $entries;
	}
	$url = get_permalink( $page->ID );
	return array_values(
		array_filter(
			$entries,
			static function ( $entry ) use ( $url ) {
				return ! isset( $entry['loc'] ) || $entry['loc'] !== $url;
			}
		)
	);
}
add_filter( 'wp_sitemaps_posts_entries', 'fnc_sitemap_filter_entries', 10, 2 );

/**
 * Balises `hreflang` (fr / en / x-default) — émetteur unique du template.
 *
 * Next émet hreflang sur chaque page ; Polylang, dans cette configuration, ne
 * les produit pas. On les émet donc ici, à partir des URL de traduction de la
 * page courante (repli accueil de la langue), avec `x-default` = langue par
 * défaut. N'émet rien sans Polylang (pas de multilingue → pas d'alternates).
 *
 * @return void
 */
function fnc_emit_hreflang() {
	if ( ! function_exists( 'PLL' ) || ! PLL() || ! isset( PLL()->model ) ) {
		return;
	}
	if ( is_404() ) {
		return;
	}
	$langs = PLL()->model->get_languages_list();
	if ( empty( $langs ) ) {
		return;
	}
	$default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : 'fr';
	$urls    = array();
	foreach ( $langs as $lang ) {
		$url = '';
		if ( isset( PLL()->links ) && method_exists( PLL()->links, 'get_translation_url' ) ) {
			$url = (string) PLL()->links->get_translation_url( $lang );
		}
		if ( '' === $url && function_exists( 'pll_home_url' ) ) {
			$url = (string) pll_home_url( $lang->slug );
		}
		if ( '' === $url ) {
			continue;
		}
		$urls[ $lang->slug ] = $url;
		printf( '<link rel="alternate" hreflang="%s" href="%s" />' . "\n", esc_attr( $lang->slug ), esc_url( $url ) );
	}
	if ( isset( $urls[ $default ] ) ) {
		printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url( $urls[ $default ] ) );
	}
}
add_action( 'wp_head', 'fnc_emit_hreflang', 5 );

/**
 * Chargement des traductions du theme.
 *
 * On charge le fichier .mo DIRECTEMENT (load_textdomain) plutot que via
 * load_theme_textdomain, pour deux raisons observees sur WordPress 6.8 +
 * Polylang :
 *
 *   1. load_theme_textdomain tente d'abord le dossier global
 *      (wp-content/languages/themes/) ; cette tentative sur un fichier absent
 *      laisse le domaine dans un etat qui empeche la seconde tentative (dossier
 *      du theme) d'aboutir. Cibler directement le .mo du theme evite ce piege.
 *   2. La langue source du theme est le francais, pour laquelle il n'existe
 *      aucun .mo. On ne charge donc un fichier que s'il existe pour la locale
 *      courante ; sinon on decharge, pour revenir aux chaines sources
 *      francaises (et ne pas laisser une traduction anglaise chargee plus tot).
 *
 * Double accroche :
 *   - `init` (priorite 1) couvre l'administration et sert de repli.
 *   - `wp` couvre le FRONT avec Polylang, qui ne fixe la langue de la requete
 *     qu'apres `init` ; sans ce second passage, le front resterait fige dans la
 *     langue par defaut.
 */
function fnc_load_textdomain() {
	$locale = determine_locale();
	$mofile = get_template_directory() . '/languages/fnc-wordpress-theme-' . $locale . '.mo';

	unload_textdomain( 'fnc-wordpress-theme' );
	if ( is_readable( $mofile ) ) {
		load_textdomain( 'fnc-wordpress-theme', $mofile, $locale );
	}
}
add_action( 'init', 'fnc_load_textdomain', 1 );
add_action( 'wp', 'fnc_load_textdomain' );

/**
 * Emplacements de menu. Sur `init` (priorite 1, apres le chargement du
 * textdomain) : les libelles passent par __(), a n'appeler qu'une fois le
 * domaine charge.
 */
function fnc_register_menus() {
	register_nav_menus(
		array(
			'primary' => __( 'Navigation principale', 'fnc-wordpress-theme' ),
			'mobile'  => __( 'Navigation mobile', 'fnc-wordpress-theme' ),
		)
	);
}
add_action( 'init', 'fnc_register_menus', 1 );

/**
 * Archive des intervenants : PÉRIMÈTRE = participants de l'édition en cours
 * (comme le site du Forum, qui n'affiche pas tout l'annuaire historique), dans
 * l'ordre global (protocolaire puis sort_index). Repli sur tout l'annuaire en
 * ordre protocolaire si le périmètre n'est pas résoluble (pas d'édition active).
 */
function fnc_order_intervenant_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'fnc_intervenant' ) ) {
		return;
	}
	if ( function_exists( 'fnc_edition_participants' ) ) {
		$fnc_ids = fnc_edition_participants();
		if ( ! empty( $fnc_ids ) ) {
			$query->set( 'post__in', $fnc_ids );
			$query->set( 'orderby', 'post__in' ); // conserve l'ordre global déjà trié.
			$query->set( 'posts_per_page', -1 );
			return;
		}
	}
	$query->set( 'meta_key', '_fnc_speaker_protocol_order' );
	$query->set( 'orderby', array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ) );
	$query->set( 'posts_per_page', -1 );
}
add_action( 'pre_get_posts', 'fnc_order_intervenant_archive' );

/**
 * Enqueue des styles et scripts du theme.
 *
 * Kit de rattrapage de fidelite DA-D : quatre feuilles extraites du CSS de
 * production du site du Forum, chargees APRES style.css et dans un ordre STRICT
 * (la cascade en depend). L'ordre est garanti par une chaine de dependances :
 * chaque feuille depend de la precedente.
 *
 *   style.css (fnc-theme-style)
 *     -> wordpress-catchup.css            (P1 : familles a zero style)
 *     -> wordpress-tailwind-utilities.css (P4 : utilitaires + tokens DA)
 *     -> wordpress-catchup-p2.css         (P2 : familles partielles)
 *     -> wordpress-catchup-complet.css    (P3 : regles semantiques restantes)
 *
 * Ces fichiers sont des extractions fideles de la prod : ils sont charges
 * tels quels, jamais reecrits (voir /design).
 */
function fnc_theme_assets() {
	$dir = get_template_directory_uri();
	$ver = FNC_THEME_VERSION;

	wp_enqueue_style( 'fnc-theme-style', get_stylesheet_uri(), array(), $ver );

	$fnc_catchup = array(
		'fnc-catchup'            => array( 'assets/css/wordpress-catchup.css', 'fnc-theme-style' ),
		'fnc-tailwind-utilities' => array( 'assets/css/wordpress-tailwind-utilities.css', 'fnc-catchup' ),
		'fnc-catchup-p2'         => array( 'assets/css/wordpress-catchup-p2.css', 'fnc-tailwind-utilities' ),
		'fnc-catchup-complet'    => array( 'assets/css/wordpress-catchup-complet.css', 'fnc-catchup-p2' ),
	);
	foreach ( $fnc_catchup as $handle => $conf ) {
		wp_enqueue_style( $handle, $dir . '/' . $conf[0], array( $conf[1] ), $ver );
	}

	// Correctif empreinte EN DERNIER : le kit reference /images/brand/… (chemin
	// absolu du site d’origine, 404 en WP) ; ce fichier repointe le motif vers ../img/
	// (relatif a assets/css/). Depend de fnc-catchup-complet pour passer apres.
	wp_enqueue_style( 'fnc-empreinte-fix', $dir . '/assets/css/wordpress-empreinte-fix.css', array( 'fnc-catchup-complet' ), $ver );

	// Patch de fidelite DA (dernier) : ne corrige QUE des derives de valeur CSS
	// mesurees a l'ecran par rapport a la charte (ex. graisse du nom du carrousel).
	wp_enqueue_style( 'fnc-fidelity', $dir . '/assets/css/wordpress-fidelity-patch.css', array( 'fnc-empreinte-fix' ), $ver );

	wp_enqueue_script( 'fnc-theme-main', $dir . '/assets/js/main.js', array(), $ver, true );
}
add_action( 'wp_enqueue_scripts', 'fnc_theme_assets' );

/**
 * Ajoute la classe `dad` au <body>.
 *
 * OBLIGATOIRE pour la DA-D : une partie des regles de typographie de base est
 * scopee sous `.dad` (ex. `.dad p`, `.dad h1`…). Sans cette classe, ces regles
 * restent inertes. Le scope `.dad` est deliberement conserve pour ne PAS
 * imposer ces styles a tout le contenu WordPress/Gutenberg hors DA.
 */
function fnc_body_class_dad( $classes ) {
	$classes[] = 'dad';
	if ( fnc_has_linen_header() ) {
		$classes[] = 'linen-header';
	}
	return $classes;
}
add_filter( 'body_class', 'fnc_body_class_dad' );

/**
 * Pages a en-tete SOBRE registre C (bandeau lin, titre navy) : pages legales.
 * Sur ces pages il n'y a pas de hero photo sombre sous la barre : la nav doit
 * donc demarrer — et rester — en mode "solide" (fond clair, texte navy),
 * sinon les liens blancs seraient illisibles sur le lin. Source unique de
 * verite reutilisee par header.php (classe initiale) et main.js (verrou au
 * defilement).
 */
function fnc_has_linen_header() {
	return is_page(
		array(
			'mentions-legales',
			'politique-confidentialite',
			'conditions-generales-utilisation',
			'declaration-accessibilite',
		)
	);
}

/**
 * URL d'une Page WordPress publiee par son slug, avec repli sur "#" si
 * elle n'existe pas encore (installation fraiche du theme sans contenu).
 */
function fnc_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : '#';
}

/**
 * URL de l'archive d'un custom post type, avec le meme repli sur "#".
 */
function fnc_archive_url( $post_type ) {
	$url = get_post_type_archive_link( $post_type );
	return $url ? $url : '#';
}

/**
 * Items de navigation principale, alignes sur le site du Forum
 * (localhost:3000/fr) suite a l'amendement de la Decision 1 de
 * l': 6 items (Le Forum, Edition en cours, Editions,
 * Ressources, Partenaires, Contact), pas les ancres de la référence de conception
 * mono-page d'origine. Liens reels vers les Pages/archives creees
 * dans cette instance ; reste sur "#" pour celles qui n'existent pas
 * encore.
 *
 * @return array Liste de [url, libelle].
 */
function fnc_default_menu_items() {
	return array(
		array( fnc_page_url( 'le-forum' ), __( 'Le Forum', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'edition-en-cours' ), __( 'Édition 2027', 'fnc-wordpress-theme' ) ),
		array( fnc_archive_url( 'fnc_edition' ), __( 'Éditions', 'fnc-wordpress-theme' ) ),
		array( fnc_archive_url( 'fnc_publication' ), __( 'Ressources', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'partenaires' ), __( 'Partenaires', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'contact' ), __( 'Contact', 'fnc-wordpress-theme' ) ),
	);
}

/**
 * Menu de repli pour l'emplacement "primary", tant qu'aucun menu
 * WordPress n'est configure dans l'administration (Apparence > Menus).
 */
/**
 * Vrai si l'URL d'un item de menu correspond a la page courante (ou a sa
 * section) — pour surligner l'entree active, comme le site du Forum. Compare
 * les chemins en ignorant le prefixe de langue (Polylang).
 *
 * @param string $href URL de l'item.
 * @return bool
 */
function fnc_menu_is_active( $href ) {
	// Lien inexistant (page non créée) : jamais actif. Sans ce garde-fou, un
	// href « # » se réduit à un chemin vide et serait pris pour l'accueil, ce qui
	// allumerait l'état actif sur TOUS les liens brisés à la fois.
	if ( '' === $href || '#' === $href ) {
		return false;
	}
	$req  = isset( $GLOBALS['wp']->request ) ? $GLOBALS['wp']->request : '';
	$cur  = trim( (string) wp_parse_url( home_url( $req ), PHP_URL_PATH ), '/' );
	$item = trim( (string) wp_parse_url( $href, PHP_URL_PATH ), '/' );
	$cur  = preg_replace( '#^[a-z]{2}/#', '', $cur );
	$item = preg_replace( '#^[a-z]{2}/#', '', $item );
	if ( '' === $item ) {
		return is_front_page();
	}
	return $cur === $item || 0 === strpos( $cur, $item . '/' );
}

function fnc_default_primary_menu() {
	echo '<ul>';
	foreach ( fnc_default_menu_items() as $fnc_item ) {
		$fnc_active = fnc_menu_is_active( $fnc_item[0] );
		printf(
			'<li%s><a href="%s"%s>%s</a></li>',
			$fnc_active ? ' class="current-menu-item"' : '',
			esc_url( $fnc_item[0] ),
			$fnc_active ? ' aria-current="page"' : '',
			esc_html( $fnc_item[1] )
		);
	}
	echo '</ul>';
}

/**
 * Variante du menu de repli pour le panneau mobile : liens a plat, sans
 * <ul>/<li>, conformement a la structure de la référence de conception (le CSS
 * `.mobile-panel a` cible des liens directs, pas une liste).
 */
function fnc_default_mobile_menu() {
	foreach ( fnc_default_menu_items() as $fnc_item ) {
		$fnc_active = fnc_menu_is_active( $fnc_item[0] );
		printf(
			'<a href="%s"%s>%s</a>',
			esc_url( $fnc_item[0] ),
			$fnc_active ? ' aria-current="page"' : '',
			esc_html( $fnc_item[1] )
		);
	}
}

/**
 * URL de la version d'une page dans une langue Polylang donnee.
 *
 * Utilise la traduction reelle de la page courante quand elle existe, avec
 * repli sur l'accueil de la langue. `pll_the_languages()` renvoie une liste
 * vide sur les vues sans traduction assignee (ex. l'accueil-liste), d'ou le
 * recours a l'API de bas niveau, plus fiable.
 *
 * @param object $lang Objet langue Polylang.
 * @return string
 */
function fnc_pll_language_url( $lang ) {
	$pll = function_exists( 'PLL' ) ? PLL() : null;
	if ( $pll && isset( $pll->links ) && method_exists( $pll->links, 'get_translation_url' ) ) {
		$url = $pll->links->get_translation_url( $lang );
		if ( $url ) {
			return $url;
		}
	}
	if ( function_exists( 'pll_home_url' ) ) {
		return pll_home_url( $lang->slug );
	}
	return home_url( '/' );
}

/**
 * Selecteur de langue.
 *
 * Si Polylang est actif, affiche des liens vers chaque langue
 * configuree (traduction de la page courante, repli sur l'accueil). Sinon,
 * degrade gracieusement vers un rendu statique FR/EN (pas de lien casse).
 */
function fnc_language_switcher() {
	if ( function_exists( 'PLL' ) && PLL() && function_exists( 'pll_current_language' ) ) {
		$langs = PLL()->model->get_languages_list();
		if ( ! empty( $langs ) ) {
			$current = pll_current_language( 'slug' );
			echo '<div class="lang" role="group" aria-label="' . esc_attr__( 'Langue', 'fnc-wordpress-theme' ) . '">';
			$count = count( $langs );
			$i     = 0;
			foreach ( $langs as $lang ) {
				++$i;
				$is_current = ( $lang->slug === $current );
				printf(
					'<a href="%1$s" lang="%2$s" hreflang="%2$s" aria-current="%3$s">%4$s</a>',
					esc_url( fnc_pll_language_url( $lang ) ),
					esc_attr( $lang->slug ),
					$is_current ? 'true' : 'false',
					esc_html( strtoupper( $lang->slug ) )
				);
				if ( $i < $count ) {
					echo '<span class="sep" aria-hidden="true">|</span>';
				}
			}
			echo '</div>';
			return;
		}
	}

	// Repli statique — decoratif tant que Polylang n'est pas actif.
	echo '<div class="lang" role="group" aria-label="' . esc_attr__( 'Langue', 'fnc-wordpress-theme' ) . '">';
	echo '<button aria-pressed="true">FR</button><span class="sep" aria-hidden="true">|</span><button aria-pressed="false">EN</button>';
	echo '</div>';
}

/**
 * Bloc "hero" secondaire partage par les pages interieures generees a
 * partir de la référence de conception (contact, archives, etc.).
 * Distinct du bloc "opening" utilise par page-le-forum.php (référence de conception
 * interior/le-forum.html, systeme de gabarit different mais memes tokens
 * de couleur).
 *
 * @param array $args {
 *     @type string $eyebrow    Texte au-dessus du titre.
 *     @type string $title      Titre H1.
 *     @type string $lead       Texte d'introduction.
 *     @type string $image      URL de l'image de fond.
 *     @type string $image_alt  Texte alternatif de l'image.
 *     @type string $breadcrumb Libelle du fil d'ariane (optionnel).
 * }
 */
function fnc_render_hero( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow'    => '',
			'title'      => '',
			'lead'       => '',
			'image'      => '',
			'image_alt'  => '',
			'breadcrumb' => '',
		)
	);
	?>
	<section class="hero secondary">
		<img src="<?php echo esc_url( $args['image'] ); ?>" alt="<?php echo esc_attr( $args['image_alt'] ); ?>" />
		<div class="hero-inner">
			<?php if ( $args['breadcrumb'] ) : ?>
				<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> / <?php echo esc_html( $args['breadcrumb'] ); ?></p>
			<?php endif; ?>
			<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
			<h1><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['lead'] ) : ?>
				<p class="lead"><?php echo esc_html( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>
		<svg class="trace" viewBox="0 0 1200 96" preserveAspectRatio="none" aria-hidden="true">
			<path class="r" d="M0 68 H390 l22 -28 H792 l24 28 H1200"/>
			<path class="y" d="M0 34 H288 l28 30 H904 l22 -18 H1200"/>
		</svg>
	</section>
	<?php
}

/**
 * Héros registre A — .opening : photo plein cadre + Ken Burns + PCB animé.
 * Pages listing/section et détail d'édition (internal-pages-hero-spec.md §1/§3).
 * Image = image à la une (pages/fiches) SINON l'image par défaut de la route
 * passée en 'image'. Le filet PCB anime vient du partial unique hero-pcb.php.
 *
 * @param array $args eyebrow, title, intro|lead, image (defaut route), image_alt,
 *                    breadcrumb (partie après « Accueil · », peut contenir un lien).
 */
function fnc_render_opening_hero( array $args ) {
	$args  = wp_parse_args(
		$args,
		array( 'eyebrow' => '', 'title' => '', 'title_b' => '', 'lead' => '', 'intro' => '', 'image' => '', 'image_alt' => '', 'breadcrumb' => '' )
	);
	$intro = $args['intro'] ? $args['intro'] : $args['lead'];
	$image = ( is_singular() && has_post_thumbnail() ) ? get_the_post_thumbnail_url( null, 'full' ) : $args['image'];
	?>
	<header class="opening">
		<img class="media-cover" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $args['image_alt'] ); ?>" />
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<?php if ( $args['breadcrumb'] ) : ?>
				<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php echo wp_kses_post( $args['breadcrumb'] ); ?></p>
			<?php endif; ?>
			<?php if ( $args['eyebrow'] ) : ?><span class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span><?php endif; ?>
			<h1><?php echo esc_html( $args['title'] ); if ( $args['title_b'] ) { echo '<br />' . esc_html( $args['title_b'] ); } ?></h1>
			<?php if ( $intro ) : ?><p class="intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		</div>
		<?php get_template_part( 'hero-pcb' ); ?>
	</header>
	<?php
}

/**
 * Héros registre B — .page-head : dégradé navy-deep, sans photo, PCB animé.
 * Fiches détail (intervenant, session, ressource). Voir spec §4. L'eyebrow est
 * optionnel (absent sur la fiche intervenant).
 *
 * @param array $args eyebrow, title, intro|lead, breadcrumb.
 */
function fnc_render_pagehead( array $args ) {
	$args  = wp_parse_args(
		$args,
		array( 'eyebrow' => '', 'title' => '', 'lead' => '', 'intro' => '', 'breadcrumb' => '' )
	);
	$intro = $args['intro'] ? $args['intro'] : $args['lead'];
	?>
	<header class="page-head">
		<?php if ( $args['breadcrumb'] ) : ?>
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php echo wp_kses_post( $args['breadcrumb'] ); ?></p>
		<?php endif; ?>
		<?php if ( $args['eyebrow'] ) : ?><span class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span><?php endif; ?>
		<h1><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $intro ) : ?><p class="intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		<?php get_template_part( 'hero-pcb' ); ?>
	</header>
	<?php
}

/**
 * En-tete sobre — registre C (§5 internal-pages-hero-spec) : bandeau lin,
 * pas de photo, titre navy, filet PCB STATIQUE (traits stroke inline, non
 * animes, a la difference du filet anime des registres A/B). Utilise par
 * le-forum et contact ; les pages legales gardent fnc_render_legal_header.
 */
function fnc_render_pageheader( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'       => '',
			'description' => '',
			'intro'       => '',
			'lead'        => '',
		)
	);
	$desc = '';
	foreach ( array( 'description', 'intro', 'lead' ) as $fnc_k ) {
		if ( '' !== $args[ $fnc_k ] ) {
			$desc = $args[ $fnc_k ];
			break;
		}
	}
	?>
	<header class="page-header">
		<div class="container">
			<h1><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( '' !== $desc ) : ?>
				<p class="lead"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</div>
		<svg class="pcb-static" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0 40 H420 l20 -20 H820 l20 20 H1200" fill="none" stroke="var(--rouge)" stroke-width="2"/>
			<path d="M0 20 H300 l24 20 H900 l18 -14 H1200" fill="none" stroke="var(--jaune)" stroke-width="2"/>
		</svg>
	</header>
	<?php
}

/**
 * Bloc d'en-tete sobre pour les pages legales (mentions, confidentialite,
 * CGU) : meme registre C que le-forum/contact (PageHeader du site du Forum),
 * mais avec la date de mise a jour affichee en meta sous le titre.
 * $breadcrumb est conserve pour compatibilite d'appel mais n'est plus rendu
 * (le PageHeader reel n'affiche pas de fil d'ariane).
 */
function fnc_render_legal_header( $title, $updated_label, $breadcrumb = '' ) {
	fnc_render_pageheader(
		array(
			'title'       => $title,
			'description' => $updated_label,
		)
	);
}

/**
 * Bandeau CTA final partage par les pages interieures (site.js: finalCta()).
 */
function fnc_render_cta_band() {
	?>
	<section class="cta-band">
		<h2><?php esc_html_e( 'Poursuivre le parcours.', 'fnc-wordpress-theme' ); ?></h2>
		<p><?php esc_html_e( 'Les contenus non confirmés restent masqués ou signalés.', 'fnc-wordpress-theme' ); ?></p>
		<a class="btn btn-red" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contacter l’équipe', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
		</a>
	</section>
	<?php
}

/**
 * Carte editoriale partagee (site.js: card()).
 */
function fnc_render_card( $title, $body, $kicker = '' ) {
	?>
	<article class="card fnc-card">
		<?php if ( $kicker ) : ?>
			<p class="card-kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<h3><?php echo esc_html( $title ); ?></h3>
		<p><?php echo esc_html( $body ); ?></p>
	</article>
	<?php
}

/**
 * Passe gabarits (reconciliation du modele de contenu, cf. plugin
 * fnc-content-model v0.2.0) : petits utilitaires d'affichage partages par
 * les gabarits qui consomment desormais les nouveaux champs meta.
 */

/**
 * Badge non interactif (type de session, type de publication, niveau de
 * partenariat...), visuellement proche de `.chip` mais jamais cliquable.
 */
function fnc_render_badge( $label ) {
	if ( ! $label ) {
		return;
	}
	printf( '<span class="badge">%s</span>', esc_html( $label ) );
}

/**
 * COMPOSANT PARTAGÉ — chips de filtre par statut/type (liste des éditions,
 * ressources, etc.). Registre DA unique : `.filters` > `.chip` (radius 2px),
 * libellés (au pluriel côté appelant) + compte par option, `aria-pressed`,
 * préservation des autres paramètres d'URL, et compteur « N affichés » optionnel.
 *
 * @param array{
 *   base_url:string, param:string, current:string, all_label:string,
 *   all_count:?int, options:array<string,array{label:string,count?:int}>,
 *   preserve:array<string,string>, aria_label:string, counter:string
 * } $args
 * @return void
 */
function fnc_render_filter_chips( array $args ) {
	$args     = wp_parse_args(
		$args,
		array(
			'base_url'   => home_url( '/' ),
			'param'      => 'status',
			'current'    => '',
			'all_label'  => __( 'Toutes', 'fnc-wordpress-theme' ),
			'all_count'  => null,
			'options'    => array(),
			'preserve'   => array(),
			'aria_label' => __( 'Filtrer', 'fnc-wordpress-theme' ),
			'counter'    => '',
		)
	);
	$base     = $args['base_url'] ? $args['base_url'] : home_url( '/' );
	$preserve = array_filter( (array) $args['preserve'] );

	echo '<div class="filters" role="group" aria-label="' . esc_attr( $args['aria_label'] ) . '">';
	printf(
		'<a class="chip" href="%s" aria-pressed="%s">%s%s</a>',
		esc_url( $preserve ? add_query_arg( $preserve, $base ) : $base ),
		'' === $args['current'] ? 'true' : 'false',
		esc_html( $args['all_label'] ),
		null !== $args['all_count'] ? ' <span aria-hidden="true">(' . esc_html( $args['all_count'] ) . ')</span>' : ''
	);
	foreach ( $args['options'] as $slug => $opt ) {
		printf(
			'<a class="chip" href="%s" aria-pressed="%s">%s%s</a>',
			esc_url( add_query_arg( array_merge( $preserve, array( $args['param'] => $slug ) ), $base ) ),
			$args['current'] === (string) $slug ? 'true' : 'false',
			esc_html( $opt['label'] ),
			isset( $opt['count'] ) ? ' <span aria-hidden="true">(' . esc_html( $opt['count'] ) . ')</span>' : ''
		);
	}
	echo '</div>';

	if ( '' !== $args['counter'] ) {
		printf(
			'<p class="frise-note" role="status" aria-live="polite" style="margin-top:16px;">%s</p>',
			esc_html( $args['counter'] )
		);
	}
}

/**
 * COMPOSANT PARTAGÉ — section « complément » finale (éditions, ressources…).
 * Gabarit unique DA : bande CLAIRE alignée à gauche (eyebrow « Suite » + titre +
 * sous-titre) et blocs-liens BORDÉS à droite. Jamais un callout navy centré ni
 * des liens inline.
 *
 * @param array{eyebrow:string,title:string,intro:string,links:array<int,array{label:string,href:string}>,linen:bool} $args
 * @return void
 */
function fnc_render_complement_section( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow' => __( 'Suite', 'fnc-wordpress-theme' ),
			'title'   => '',
			'intro'   => '',
			'links'   => array(),
			'linen'   => true,
		)
	);
	?>
	<section class="section<?php echo $args['linen'] ? ' linen' : ''; ?>">
		<div class="container">
			<div class="split">
				<div>
					<?php if ( $args['eyebrow'] ) : ?><p class="eyebrow" style="color:var(--navy);"><?php echo esc_html( $args['eyebrow'] ); ?></p><?php endif; ?>
					<?php if ( $args['title'] ) : ?><h2 class="page-h2"><?php echo esc_html( $args['title'] ); ?></h2><?php endif; ?>
					<?php if ( $args['intro'] ) : ?><p style="color:var(--texte-sec);max-width:46ch;margin-top:14px;"><?php echo esc_html( $args['intro'] ); ?></p><?php endif; ?>
				</div>
				<div style="display:grid;gap:16px;align-content:center;">
					<?php foreach ( $args['links'] as $fnc_lnk ) : ?>
						<a class="link-more" href="<?php echo esc_url( $fnc_lnk['href'] ); ?>" style="justify-content:space-between;border:1px solid var(--border);border-radius:4px;padding:20px 24px;background:#fff;">
							<?php echo esc_html( $fnc_lnk['label'] ); ?> <span class="arrow" aria-hidden="true">→</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Libellé d'affichage d'un type de publication : libellé du registre s'il est
 * connu, sinon « humanisation » du slug (ex. « compte_rendu » → « Compte rendu »)
 * pour rester précis même quand le seed emploie des slugs hors registre. Slug
 * vide → « Publication ».
 *
 * @param string $slug
 * @return string
 */
function fnc_publication_type_label( $slug ) {
	$slug = (string) $slug;
	if ( '' === $slug ) {
		return __( 'Publication', 'fnc-wordpress-theme' );
	}
	$types = function_exists( 'fnc_content_model_publication_types' ) ? fnc_content_model_publication_types() : array();
	if ( isset( $types[ $slug ] ) ) {
		return $types[ $slug ];
	}
	return ucfirst( str_replace( array( '_', '-' ), ' ', $slug ) );
}

/**
 * COMPOSANT PARTAGÉ — carte de publication (registre `.pub` du kit). Mappe les
 * bons champs : type PRÉCIS (kind), date, description, et action (« Regarder »
 * pour vidéo/interview, « Télécharger » si fichier, « Fichier : à confirmer »
 * sinon).
 *
 * @param int $id ID de la publication.
 * @return void
 */
function fnc_render_publication_card( $id ) {
	$id        = (int) $id;
	$type      = (string) get_post_meta( $id, '_fnc_publication_type', true );
	$media_url = (string) get_post_meta( $id, '_fnc_publication_media_url', true );
	$file_url  = (string) get_post_meta( $id, '_fnc_publication_file', true );
	$is_media  = in_array( $type, array( 'video', 'interview' ), true );
	$kind      = fnc_publication_type_label( $type );
	?>
	<article class="pub">
		<div class="body">
			<p class="kind"><?php echo esc_html( $kind ); ?></p>
			<h3 class="title"><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></h3>
			<?php if ( has_excerpt( $id ) ) : ?>
				<p><?php echo esc_html( get_the_excerpt( $id ) ); ?></p>
			<?php endif; ?>
			<p class="date"><?php echo esc_html( get_the_date( '', $id ) ); ?></p>
			<p style="margin-top:12px;">
				<?php if ( $is_media && $media_url ) : ?>
					<a class="link-more" href="<?php echo esc_url( $media_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Regarder', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
				<?php elseif ( $file_url ) : ?>
					<a class="link-more" href="<?php echo esc_url( $file_url ); ?>"><?php esc_html_e( 'Télécharger', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
				<?php else : ?>
					<span class="tbc"><?php esc_html_e( 'Fichier : à confirmer', 'fnc-wordpress-theme' ); ?></span>
				<?php endif; ?>
			</p>
		</div>
	</article>
	<?php
}

/**
 * Libelle de dates d'une edition (« 25–27 mars 2027 »), localise, a partir de
 * _fnc_edition_start_date / _fnc_edition_end_date. Sert de repli a la page
 * d'accueil quand aucun libelle n'est saisi dans les reglages : l'edition
 * active pilote alors les dates affichees, plutot qu'un « A confirmer ».
 *
 * @param int $edition_id 0 = edition active.
 * @return string Chaine vide si aucune date exploitable.
 */
function fnc_edition_dates_label( $edition_id = 0 ) {
	$edition_id = $edition_id ? (int) $edition_id : ( function_exists( 'fnc_current_edition_id' ) ? fnc_current_edition_id() : 0 );
	if ( ! $edition_id ) {
		return '';
	}
	$start = (string) get_post_meta( $edition_id, '_fnc_edition_start_date', true );
	$ts_s  = $start ? strtotime( $start ) : false;
	if ( ! $ts_s ) {
		return '';
	}
	$end  = (string) get_post_meta( $edition_id, '_fnc_edition_end_date', true );
	$ts_e = $end ? strtotime( $end ) : false;
	if ( ! $ts_e || $ts_e === $ts_s ) {
		return wp_date( 'j F Y', $ts_s );
	}
	if ( wp_date( 'n Y', $ts_s ) === wp_date( 'n Y', $ts_e ) ) {
		return wp_date( 'j', $ts_s ) . '–' . wp_date( 'j F Y', $ts_e ); // meme mois : « 25–27 mars 2027 ».
	}
	return wp_date( 'j F', $ts_s ) . ' – ' . wp_date( 'j F Y', $ts_e );
}

/**
 * Nom d'affichage d'un intervenant : "Civilité Nom" si une civilite est
 * renseignee (_fnc_speaker_title), sinon le titre du post seul.
 */
function fnc_speaker_display_name( $speaker_id ) {
	$title_civility = get_post_meta( $speaker_id, FNC_META_SPEAKER_TITLE, true );
	$name           = get_the_title( $speaker_id );
	return $title_civility ? trim( $title_civility . ' ' . $name ) : $name;
}

/**
 * Initiales (monogramme) d'un intervenant : premiere lettre du prenom + du nom.
 * Sert de repli quand aucune photo n'est affichable (droit a l'image) — jamais
 * un faux visage generique, comme le site du Forum.
 *
 * @return string 1 ou 2 lettres majuscules.
 */
function fnc_speaker_initials( $speaker_id ) {
	$name  = wp_strip_all_tags( (string) get_the_title( $speaker_id ) );
	$words = array_values( array_filter( preg_split( '/\s+/u', trim( $name ) ), static function ( $w ) {
		return '' !== $w && preg_match( '/\p{L}/u', $w );
	} ) );
	if ( empty( $words ) ) {
		return '·';
	}
	$first = mb_strtoupper( mb_substr( $words[0], 0, 1 ) );
	$last  = count( $words ) > 1 ? mb_strtoupper( mb_substr( end( $words ), 0, 1 ) ) : '';
	return $first . $last;
}

/**
 * Ligne "organisation · pays" d'un intervenant, sans separateur superflu si
 * l'un des deux champs est vide.
 */
function fnc_speaker_meta_line( $speaker_id ) {
	$org     = get_post_meta( $speaker_id, FNC_META_SPEAKER_ORG, true );
	$country = get_post_meta( $speaker_id, FNC_META_SPEAKER_COUNTRY, true );
	return trim( implode( ' · ', array_filter( array( $org, $country ) ) ) );
}

/**
 * Drapeaux SVG inline des pays representes au Forum — memes pays et memes
 * geometries simplifiees que le composant l’affichage des drapeaux du site du Forum
 * (le site du Forum)/[locale]/intervenants/l’affichage des drapeaux),
 * transposees en PHP puisque zero dependance tierce
 * exclut d'importer un paquet de drapeaux.
 */
function fnc_country_flag_svg( $country ) {
	$country = trim( (string) $country );
	$flags   = array(
		'France'        => '<rect width="20" height="40" fill="#002654"/><rect x="20" width="20" height="40" fill="#FFFFFF"/><rect x="40" width="20" height="40" fill="#ED2939"/>',
		'Belgique'      => '<rect width="20" height="40" fill="#000000"/><rect x="20" width="20" height="40" fill="#FAE042"/><rect x="40" width="20" height="40" fill="#ED2939"/>',
		'Luxembourg'    => '<rect width="60" height="13.34" fill="#ED2939"/><rect y="13.34" width="60" height="13.33" fill="#FFFFFF"/><rect y="26.67" width="60" height="13.33" fill="#00A1DE"/>',
		'Sénégal'       => '<rect width="20" height="40" fill="#00853F"/><rect x="20" width="20" height="40" fill="#FDEF42"/><rect x="40" width="20" height="40" fill="#E31B23"/>',
		'Cameroun'      => '<rect width="20" height="40" fill="#007A5E"/><rect x="20" width="20" height="40" fill="#CE1126"/><rect x="40" width="20" height="40" fill="#FCD116"/>',
		'Congo'         => '<polygon points="0,0 60,0 0,40" fill="#009543"/><polygon points="60,0 60,40 0,40" fill="#DC241F"/><polygon points="0,40 0,26 46,0 60,0 60,14 14,40" fill="#FBDE4A"/>',
		'RDC'           => '<rect width="60" height="40" fill="#007FFF"/><line x1="0" y1="42" x2="62" y2="-2" stroke="#F7D618" stroke-width="14"/><line x1="0" y1="42" x2="62" y2="-2" stroke="#CE1021" stroke-width="8"/>',
		'Royaume-Uni'   => '<rect width="60" height="40" fill="#012169"/><path d="M0,0 60,40 M60,0 0,40" stroke="#FFFFFF" stroke-width="8"/><path d="M0,0 60,40 M60,0 0,40" stroke="#C8102E" stroke-width="4"/><rect x="24" width="12" height="40" fill="#FFFFFF"/><rect y="14" width="60" height="12" fill="#FFFFFF"/><rect x="26" width="8" height="40" fill="#C8102E"/><rect y="16" width="60" height="8" fill="#C8102E"/>',
		'États-Unis'    => '<rect width="60" height="40" fill="#B22234"/><rect width="60" height="20" fill="#FFFFFF"/><rect width="24" height="20" fill="#3C3B6E"/>',
		'Inde'          => '<rect width="60" height="13.34" fill="#FF9933"/><rect y="13.34" width="60" height="13.33" fill="#FFFFFF"/><rect y="26.67" width="60" height="13.33" fill="#138808"/><circle cx="30" cy="20" r="5.2" fill="none" stroke="#000080" stroke-width="1"/>',
		'Côte d’Ivoire' => '<rect width="20" height="40" fill="#F77F00"/><rect x="20" width="20" height="40" fill="#FFFFFF"/><rect x="40" width="20" height="40" fill="#009E60"/>',
	);

	if ( ! isset( $flags[ $country ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="flag-svg" viewBox="0 0 60 40" role="img" aria-label="%s">%s</svg>',
		esc_attr( $country ),
		$flags[ $country ]
	);
}

/**
 * Drapeau d'un pays : drapeau uploadé (via l'ordre éditorial des Réglages FNC)
 * prioritaire, repli sur le drapeau SVG intégré. Même logique que le composant
 * l’affichage des drapeaux du site du Forum (source éditoriale prioritaire, repli SVG). Sans
 * l'un ni l'autre, retourne une chaîne vide (seul le nom du pays s'affiche).
 *
 * @param string $country
 * @return string
 */
function fnc_flag_markup( $country ) {
	$map = function_exists( 'fnc_country_flag_map' ) ? fnc_country_flag_map() : array();
	$key = function_exists( 'fnc_country_key' ) ? fnc_country_key( $country ) : strtolower( trim( $country ) );

	if ( ! empty( $map[ $key ] ) ) {
		return sprintf(
			'<img class="flag-svg" src="%s" alt="%s" width="28" height="19" loading="lazy" />',
			esc_url( $map[ $key ] ),
			esc_attr( $country )
		);
	}
	return fnc_country_flag_svg( $country );
}

/**
 * Decoupe le champ texte libre `_fnc_speaker_country` (ex. "France / États-Unis")
 * en une liste de pays, meme convention que le site du Forum (l’explorateur d’intervenants).
 */
function fnc_split_countries( $country_field ) {
	if ( ! $country_field ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', explode( '/', $country_field ) ) ) );
}

/**
 * Nom du site : nom officiel des Réglages FNC, repli sur le nom WordPress.
 */
function fnc_site_name() {
	return fnc_get_setting( 'officialName', get_bloginfo( 'name' ) );
}

/*
 * Les métadonnées SEO/OpenGraph et la directive robots sont désormais gérées
 * par inc/seo.php (Lot 5) : la cascade « SEO du document → titre du document →
 * valeurs par défaut du site » y vit entièrement.
 */

/**
 * Marque de l'en-tête : les deux versions du logo Forum Numérique Congo. La
 * version claire (blanche) s'affiche sur l'en-tête transparent au-dessus des
 * héros sombres ; la version couleur prend le relais sur l'en-tête « solide »
 * (fond blanc au défilement). Le CSS bascule de l'une à l'autre selon l'état de
 * la barre (.nav / .nav.solid) — cross-fade porté par le kit CSS prod, via les
 * classes .brand-logo--white / .brand-logo--color.
 *
 * Priorité des sources :
 *  - « Logo principal » (Réglages FNC) : s'il est défini, il sert aux deux états
 *    (identité mono-logo choisie par l'administrateur) ;
 *  - sinon « Logo clair » / « Logo sombre » chacun pour son état ;
 *  - à défaut, les logos officiels embarqués dans le thème.
 *
 * Retourne le HTML des deux <img> (toujours non vide : le repli embarqué existe).
 */
function fnc_header_logo_img() {
	$base      = get_template_directory_uri() . '/assets/images/';
	$principal = fnc_get_setting_image_url( 'logo_principal', 'full' );

	if ( $principal ) {
		$light = $principal;
		$dark  = $principal;
	} else {
		$light = fnc_get_setting_image_url( 'logo_light', 'full' );
		$dark  = fnc_get_setting_image_url( 'logo_dark', 'full' );
		$light = $light ? $light : $base . 'logo-fnc-white.png';
		$dark  = $dark ? $dark : $base . 'logo-fnc-color.png';
	}

	return sprintf(
		'<img class="brand-logo brand-logo--white" src="%1$s" alt="%3$s" />' .
		'<img class="brand-logo brand-logo--color" src="%2$s" alt="" aria-hidden="true" />',
		esc_url( $light ),
		esc_url( $dark ),
		esc_attr( fnc_site_name() )
	);
}

/**
 * Logo du pied de page : version claire (blanche) posée sur le footer navy, en
 * remplacement du wordmark texte (comportement du site du Forum). Prend « Logo
 * clair » des Réglages FNC s'il est défini, sinon le logo blanc embarqué.
 */
function fnc_footer_logo_img() {
	$url = fnc_get_setting_image_url( 'logo_light', 'full' );
	if ( ! $url ) {
		$url = get_template_directory_uri() . '/assets/images/logo-fnc-white.png';
	}
	return sprintf(
		'<img class="foot-logo-img" src="%s" alt="%s" />',
		esc_url( $url ),
		esc_attr( fnc_site_name() )
	);
}
