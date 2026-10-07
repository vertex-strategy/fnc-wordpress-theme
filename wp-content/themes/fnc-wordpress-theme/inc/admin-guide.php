<?php
/**
 * Forum Numérique Congo — guide d’édition dans le tableau de bord.
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Lien d'edition d'une Page par slug (vide si absente). */
function fnc_guide_page_link( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_edit_post_link( $page->ID, 'raw' ) : '';
}

/** Lien Personnaliser cible sur un panneau. */
function fnc_guide_customize_panel( $panel ) {
	return admin_url( 'customize.php?autofocus%5Bpanel%5D=' . rawurlencode( $panel ) );
}

/** Rend une liste de liens { libelle => url }, en ignorant les url vides. */
function fnc_guide_links( $links ) {
	echo '<ul style="margin:6px 0 14px;">';
	foreach ( $links as $label => $url ) {
		if ( ! $url ) {
			continue;
		}
		printf(
			'<li style="margin:3px 0;"><a href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/** Rendu du widget. */
function fnc_admin_guide_render() {
	echo '<p style="color:#50575e;">' . esc_html__( 'Chaque type de contenu a son point d’édition. Cliquez pour y aller directement.', 'fnc-wordpress-theme' ) . '</p>';

	echo '<h4 style="margin:14px 0 0;">' . esc_html__( 'Pages éditoriales (blocs — texte, images, ordre des sections)', 'fnc-wordpress-theme' ) . '</h4>';
	fnc_guide_links(
		array(
			__( 'Le Forum', 'fnc-wordpress-theme' )            => fnc_guide_page_link( 'le-forum' ),
			__( 'Le mot du Président', 'fnc-wordpress-theme' ) => fnc_guide_page_link( 'le-forum/mot-du-president' ),
			__( 'Contact', 'fnc-wordpress-theme' )             => fnc_guide_page_link( 'contact' ),
			__( 'Inscription', 'fnc-wordpress-theme' )         => fnc_guide_page_link( 'inscription' ),
		)
	);

	echo '<h4 style="margin:14px 0 0;">' . esc_html__( 'Héros & titres de section des pages à liste', 'fnc-wordpress-theme' ) . '</h4>';
	fnc_guide_links(
		array(
			__( 'Héros des pages (sur-titre, titre, intro, image)', 'fnc-wordpress-theme' ) => fnc_guide_customize_panel( 'fnc_heroes' ),
			__( 'Titres de section (bandes internes)', 'fnc-wordpress-theme' )              => fnc_guide_customize_panel( 'fnc_sections' ),
		)
	);

	echo '<h4 style="margin:14px 0 0;">' . esc_html__( 'Fiches (annuaires, programme, ressources…)', 'fnc-wordpress-theme' ) . '</h4>';
	fnc_guide_links(
		array(
			__( 'Intervenants', 'fnc-wordpress-theme' ) => admin_url( 'edit.php?post_type=fnc_intervenant' ),
			__( 'Sessions (programme)', 'fnc-wordpress-theme' ) => admin_url( 'edit.php?post_type=fnc_session' ),
			__( 'Ressources / publications', 'fnc-wordpress-theme' ) => admin_url( 'edit.php?post_type=fnc_publication' ),
			__( 'Éditions', 'fnc-wordpress-theme' )     => admin_url( 'edit.php?post_type=fnc_edition' ),
			__( 'Actualités', 'fnc-wordpress-theme' )   => admin_url( 'edit.php?post_type=fnc_actualite' ),
			__( 'Partenaires', 'fnc-wordpress-theme' )  => admin_url( 'edit.php?post_type=fnc_partenaire' ),
		)
	);

	echo '<h4 style="margin:14px 0 0;">' . esc_html__( 'Pages légales (titre + corps)', 'fnc-wordpress-theme' ) . '</h4>';
	fnc_guide_links(
		array(
			__( 'Mentions légales', 'fnc-wordpress-theme' )              => fnc_guide_page_link( 'mentions-legales' ),
			__( 'Politique de confidentialité', 'fnc-wordpress-theme' )  => fnc_guide_page_link( 'politique-confidentialite' ),
			__( 'Conditions générales d’utilisation', 'fnc-wordpress-theme' ) => fnc_guide_page_link( 'conditions-generales-utilisation' ),
			__( 'Déclaration d’accessibilité', 'fnc-wordpress-theme' )   => fnc_guide_page_link( 'declaration-accessibilite' ),
		)
	);

	echo '<h4 style="margin:14px 0 0;">' . esc_html__( 'Site', 'fnc-wordpress-theme' ) . '</h4>';
	fnc_guide_links(
		array(
			__( 'Page d’accueil & réglages (Personnaliser)', 'fnc-wordpress-theme' ) => admin_url( 'customize.php' ),
			__( 'Menu de navigation', 'fnc-wordpress-theme' )                        => admin_url( 'nav-menus.php' ),
		)
	);
}

/** Enregistre le widget en haut du tableau de bord. */
function fnc_admin_guide_register() {
	if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
		return; // hors contexte admin (jamais atteint en usage normal).
	}
	wp_add_dashboard_widget(
		'fnc_edit_guide',
		__( 'Éditer le contenu du Forum — par où commencer', 'fnc-wordpress-theme' ),
		'fnc_admin_guide_render'
	);

	// Remonte le widget en tete de colonne principale.
	global $wp_meta_boxes;
	if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['fnc_edit_guide'] ) ) {
		$widget = array( 'fnc_edit_guide' => $wp_meta_boxes['dashboard']['normal']['core']['fnc_edit_guide'] );
		unset( $wp_meta_boxes['dashboard']['normal']['core']['fnc_edit_guide'] );
		$wp_meta_boxes['dashboard']['normal']['core'] = $widget + $wp_meta_boxes['dashboard']['normal']['core'];
	}
}
add_action( 'wp_dashboard_setup', 'fnc_admin_guide_register' );
