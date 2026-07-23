<?php
/**
 * FNC WordPress Theme — fonctions du theme.
 *
 * Voir ADR-007 (depot forum-numerique-congo) pour le contexte produit :
 * template vitrine parallele, derive de docs/mockups/homepage-v2.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_THEME_VERSION', '0.1.0' );

/**
 * Theme setup : support des fonctionnalites WordPress utilisees par les gabarits.
 */
function fnc_theme_setup() {
	load_theme_textdomain( 'fnc-wordpress-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus(
		array(
			'primary' => __( 'Navigation principale', 'fnc-wordpress-theme' ),
			'mobile'  => __( 'Navigation mobile', 'fnc-wordpress-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'fnc_theme_setup' );

/**
 * Enqueue des styles et scripts du theme.
 */
function fnc_theme_assets() {
	wp_enqueue_style( 'fnc-theme-style', get_stylesheet_uri(), array(), FNC_THEME_VERSION );
	wp_enqueue_script( 'fnc-theme-main', get_template_directory_uri() . '/assets/js/main.js', array(), FNC_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'fnc_theme_assets' );

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
 * Items de navigation principale, alignes sur le site officiel reel
 * (localhost:3000/fr) suite a l'amendement de la Decision 1 de
 * l'ADR-007 : 6 items (Le Forum, Edition en cours, Editions,
 * Ressources, Partenaires, Contact), pas les ancres de la maquette
 * mono-page d'origine. Liens reels vers les Pages/archives creees
 * dans cette instance ; reste sur "#" pour celles qui n'existent pas
 * encore.
 *
 * @return array Liste de [url, libelle].
 */
function fnc_default_menu_items() {
	return array(
		array( fnc_page_url( 'le-forum' ), __( 'Le Forum', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'edition-en-cours' ), __( 'Édition en cours', 'fnc-wordpress-theme' ) ),
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
function fnc_default_primary_menu() {
	echo '<ul>';
	foreach ( fnc_default_menu_items() as $fnc_item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $fnc_item[0] ), esc_html( $fnc_item[1] ) );
	}
	echo '</ul>';
}

/**
 * Variante du menu de repli pour le panneau mobile : liens a plat, sans
 * <ul>/<li>, conformement a la structure de la maquette source (le CSS
 * `.mobile-panel a` cible des liens directs, pas une liste).
 */
function fnc_default_mobile_menu() {
	foreach ( fnc_default_menu_items() as $fnc_item ) {
		printf( '<a href="%s">%s</a>', esc_url( $fnc_item[0] ), esc_html( $fnc_item[1] ) );
	}
}

/**
 * Selecteur de langue.
 *
 * Si Polylang est actif (ADR-007, Decision 2 — dependance ciblee acceptee
 * uniquement pour le multilinguisme), affiche les langues reellement
 * configurees. Sinon, degrade gracieusement vers le rendu statique FR/EN
 * de la maquette source (pas de contenu invente, pas de lien casse).
 */
function fnc_language_switcher() {
	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages(
			array(
				'raw'               => true,
				'hide_if_no_translation' => 0,
			)
		);

		if ( ! empty( $languages ) ) {
			echo '<div class="lang" role="group" aria-label="' . esc_attr__( 'Langue', 'fnc-wordpress-theme' ) . '">';
			$count = count( $languages );
			$i     = 0;
			foreach ( $languages as $lang ) {
				++$i;
				printf(
					'<a href="%s" aria-current="%s">%s</a>',
					esc_url( $lang['url'] ),
					$lang['current_lang'] ? 'true' : 'false',
					esc_html( strtoupper( $lang['slug'] ) )
				);
				if ( $i < $count ) {
					echo '<span class="sep" aria-hidden="true">·</span>';
				}
			}
			echo '</div>';
			return;
		}
	}

	// Repli statique — decoratif tant que Polylang n'est pas configure.
	echo '<div class="lang" role="group" aria-label="' . esc_attr__( 'Langue', 'fnc-wordpress-theme' ) . '">';
	echo '<button aria-pressed="true">FR</button><span class="sep" aria-hidden="true">·</span><button aria-pressed="false">EN</button>';
	echo '</div>';
}

/**
 * Bloc "hero" secondaire partage par les pages interieures generees a
 * partir de docs/mockups/homepage-v2/site.js (contact, archives, etc.).
 * Distinct du bloc "opening" utilise par page-le-forum.php (maquette
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
 * Bloc d'en-tete sobre pour les pages legales (mentions, confidentialite,
 * CGU), aligne sur le site officiel reel : pas de photo, juste un fil
 * d'ariane, une date de mise a jour et un titre — a la difference du
 * bloc "hero" utilise par les autres pages interieures.
 */
function fnc_render_legal_header( $title, $updated_label, $breadcrumb ) {
	?>
	<header class="section" style="padding-top:calc(clamp(64px,9vh,118px) + 60px);">
		<div class="container reading">
			<p class="breadcrumb" style="color:var(--texte-tert);"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php echo esc_html( $breadcrumb ); ?></p>
			<p class="eyebrow"><?php echo esc_html( $updated_label ); ?></p>
			<h1 style="font-size:var(--h2);color:var(--navy);"><?php echo esc_html( $title ); ?></h1>
		</div>
	</header>
	<?php
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
	<article class="card">
		<?php if ( $kicker ) : ?>
			<p class="card-kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<h3><?php echo esc_html( $title ); ?></h3>
		<p><?php echo esc_html( $body ); ?></p>
	</article>
	<?php
}
