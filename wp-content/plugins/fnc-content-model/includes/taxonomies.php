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

	/*
	 * Filtres de l'archive Intervenants (ADR-007, amendement Decision 1) :
	 * alignes sur les fonctionnalites reelles du site officiel (filtre par
	 * profil et par pays), pas sur la maquette statique qui n'en avait pas.
	 */
	register_taxonomy(
		'fnc_profil',
		array( 'fnc_intervenant' ),
		array(
			'labels'            => array(
				'name'          => __( 'Profils', 'fnc-content-model' ),
				'singular_name' => __( 'Profil', 'fnc-content-model' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'profil-intervenant' ),
		)
	);

	register_taxonomy(
		'fnc_pays',
		array( 'fnc_intervenant' ),
		array(
			'labels'            => array(
				'name'          => __( 'Pays', 'fnc-content-model' ),
				'singular_name' => __( 'Pays', 'fnc-content-model' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'pays' ),
		)
	);
}
add_action( 'init', 'fnc_content_model_register_taxonomies' );
