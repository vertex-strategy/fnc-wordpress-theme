<?php
/**
 * Taxonomies — reflete les collections Categories/Tags de Payload CMS.
 *
 * Perimetre d'attachement aligne sur ADR-006 du depot forum-numerique-congo
 * (taxonomie des publications) : categories/tags s'appliquent aux
 * actualites, publications et sessions — pas aux intervenants, partenaires
 * ni editions, qui ne portent pas ce type de classification cote Payload.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fnc_content_model_register_taxonomies() {
	$post_types = array( 'fnc_session', 'fnc_publication', 'fnc_actualite' );

	register_taxonomy(
		'fnc_categorie',
		$post_types,
		array(
			'labels'            => array(
				'name'          => __( 'Catégories', 'fnc-content-model' ),
				'singular_name' => __( 'Catégorie', 'fnc-content-model' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'categorie' ),
		)
	);

	register_taxonomy(
		'fnc_tag',
		$post_types,
		array(
			'labels'            => array(
				'name'          => __( 'Étiquettes', 'fnc-content-model' ),
				'singular_name' => __( 'Étiquette', 'fnc-content-model' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'tag' ),
		)
	);
}
add_action( 'init', 'fnc_content_model_register_taxonomies' );
