<?php
/**
 * Forum Numérique Congo — compatibilité multilingue (contenus et taxonomies traduisibles).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom post types du plugin geres comme traduisibles.
 *
 * @return array<int,string>
 */
function fnc_content_model_multilingual_post_types() {
	return array(
		'fnc_edition',
		'fnc_session',
		'fnc_intervenant',
		'fnc_partenaire',
		'fnc_publication',
		'fnc_actualite',
	);
}

/**
 * Taxonomies du plugin gerees comme traduisibles.
 *
 * @return array<int,string>
 */
function fnc_content_model_multilingual_taxonomies() {
	return array(
		'fnc_categorie',
		'fnc_tag',
		'fnc_profil',
		'fnc_pays',
		'fnc_niveau_partenariat',
	);
}

/**
 * Declare les CPT du plugin comme traduisibles.
 *
 * @param array<string,string> $post_types
 * @param bool                 $is_settings
 * @return array<string,string>
 */
function fnc_content_model_pll_post_types( $post_types, $is_settings ) {
	foreach ( fnc_content_model_multilingual_post_types() as $pt ) {
		$post_types[ $pt ] = $pt;
	}
	return $post_types;
}
add_filter( 'pll_get_post_types', 'fnc_content_model_pll_post_types', 10, 2 );

/**
 * Declare les taxonomies du plugin comme traduisibles.
 *
 * @param array<string,string> $taxonomies
 * @param bool                 $is_settings
 * @return array<string,string>
 */
function fnc_content_model_pll_taxonomies( $taxonomies, $is_settings ) {
	foreach ( fnc_content_model_multilingual_taxonomies() as $tax ) {
		$taxonomies[ $tax ] = $tax;
	}
	return $taxonomies;
}
add_filter( 'pll_get_taxonomies', 'fnc_content_model_pll_taxonomies', 10, 2 );
