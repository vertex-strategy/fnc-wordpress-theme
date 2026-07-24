<?php
/**
 * Custom Post Types — reflete les collections publiques Payload CMS.
 *
 * Correspondance (ADR-007, Decision 2, docs/adr/ADR-007-*.md du depot
 * forum-numerique-congo) :
 *
 *   Editions    -> edition
 *   Sessions    -> session
 *   Speakers    -> intervenant
 *   Partners    -> partenaire
 *   Publications -> publication
 *   News        -> actualite
 *
 * Categories/Tags -> taxonomies dediees (voir taxonomies.php).
 * Pages -> pages WordPress natives (pas de CPT).
 *
 * Collections explicitement hors perimetre (transactionnelles, sans usage
 * dans une vitrine) : ContactSubmissions, Registrations,
 * PartnershipRequests, Users, Media (mediatheque WP native).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fnc_content_model_register_post_types() {

	register_post_type(
		'fnc_edition',
		array(
			'labels'       => array(
				'name'          => __( 'Éditions', 'fnc-content-model' ),
				'singular_name' => __( 'Édition', 'fnc-content-model' ),
				'add_new_item'  => __( 'Ajouter une édition', 'fnc-content-model' ),
				'edit_item'     => __( 'Modifier l’édition', 'fnc-content-model' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'editions' ),
			'menu_icon'    => 'dashicons-calendar-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'fnc_session',
		array(
			'labels'       => array(
				'name'          => __( 'Sessions', 'fnc-content-model' ),
				'singular_name' => __( 'Session', 'fnc-content-model' ),
				'add_new_item'  => __( 'Ajouter une session', 'fnc-content-model' ),
				'edit_item'     => __( 'Modifier la session', 'fnc-content-model' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'programme' ),
			'menu_icon'    => 'dashicons-microphone',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'fnc_intervenant',
		array(
			'labels'       => array(
				'name'          => __( 'Intervenants', 'fnc-content-model' ),
				'singular_name' => __( 'Intervenant', 'fnc-content-model' ),
				'add_new_item'  => __( 'Ajouter un intervenant', 'fnc-content-model' ),
				'edit_item'     => __( 'Modifier l’intervenant', 'fnc-content-model' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'intervenants' ),
			'menu_icon'    => 'dashicons-businessperson',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'fnc_partenaire',
		array(
			'labels'       => array(
				'name'          => __( 'Partenaires', 'fnc-content-model' ),
				'singular_name' => __( 'Partenaire', 'fnc-content-model' ),
				'add_new_item'  => __( 'Ajouter un partenaire', 'fnc-content-model' ),
				'edit_item'     => __( 'Modifier le partenaire', 'fnc-content-model' ),
			),
			'public'       => true,
			// Pas d'archive de CPT : la vue liste des partenaires est la Page
			// « partenaires » (page-partenaires.php), qui partage le meme slug.
			// Une archive a /partenaires/ entrerait en conflit avec cette Page et
			// la masquerait. Les fiches restent servies via le rewrite ci-dessous
			// (/partenaires/{slug}/), rendues par single-fnc_partenaire.php.
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'partenaires' ),
			'menu_icon'    => 'dashicons-groups',
			'supports'     => array( 'title', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'fnc_publication',
		array(
			'labels'       => array(
				'name'          => __( 'Publications', 'fnc-content-model' ),
				'singular_name' => __( 'Publication', 'fnc-content-model' ),
				'add_new_item'  => __( 'Ajouter une publication', 'fnc-content-model' ),
				'edit_item'     => __( 'Modifier la publication', 'fnc-content-model' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'publications' ),
			'menu_icon'    => 'dashicons-media-document',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'fnc_actualite',
		array(
			'labels'       => array(
				'name'          => __( 'Actualités', 'fnc-content-model' ),
				'singular_name' => __( 'Actualité', 'fnc-content-model' ),
				'add_new_item'  => __( 'Ajouter une actualité', 'fnc-content-model' ),
				'edit_item'     => __( 'Modifier l’actualité', 'fnc-content-model' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'actualites' ),
			'menu_icon'    => 'dashicons-megaphone',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'fnc_content_model_register_post_types' );
