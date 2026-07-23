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
 * Menu de repli pour l'emplacement "primary", tant qu'aucun menu WordPress
 * n'est configure dans l'administration (Apparence > Menus).
 *
 * Reprend les ancres de la maquette homepage-v2 (page unique, M1 a M8) :
 * seule la page d'accueil est aujourd'hui committee et validee (ADR-007).
 */
function fnc_default_primary_menu() {
	$items = array(
		'#m2'     => __( 'Le Forum', 'fnc-wordpress-theme' ),
		'#m3'     => __( 'Intervenants', 'fnc-wordpress-theme' ),
		'#m5'     => __( 'Programme', 'fnc-wordpress-theme' ),
		'#m7'     => __( 'Éditions', 'fnc-wordpress-theme' ),
		'#'       => __( 'Publications', 'fnc-wordpress-theme' ),
		'#footer' => __( 'Contact', 'fnc-wordpress-theme' ),
	);

	echo '<ul>';
	foreach ( $items as $href => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $href ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Variante du menu de repli pour le panneau mobile : liens a plat, sans
 * <ul>/<li>, conformement a la structure de la maquette source (le CSS
 * `.mobile-panel a` cible des liens directs, pas une liste).
 */
function fnc_default_mobile_menu() {
	$items = array(
		'#m2'     => __( 'Le Forum', 'fnc-wordpress-theme' ),
		'#m3'     => __( 'Intervenants', 'fnc-wordpress-theme' ),
		'#m5'     => __( 'Programme', 'fnc-wordpress-theme' ),
		'#m7'     => __( 'Éditions', 'fnc-wordpress-theme' ),
		'#'       => __( 'Publications', 'fnc-wordpress-theme' ),
		'#footer' => __( 'Contact', 'fnc-wordpress-theme' ),
	);

	foreach ( $items as $href => $label ) {
		printf( '<a href="%s">%s</a>', esc_url( $href ), esc_html( $label ) );
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
