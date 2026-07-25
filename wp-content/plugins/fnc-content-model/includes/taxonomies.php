<?php
/**
 * Forum Numérique Congo — taxonomies (catégories, étiquettes, profils, pays, niveaux de partenariat).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
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
	 * Filtres de l'archive Intervenants :
	 * alignes sur les fonctionnalites reelles du site du Forum (filtre par
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
			// Gere automatiquement : synchronise depuis le champ texte « Pays » de
			// la fiche intervenant (relations.php). On masque le panneau de saisie
			// (block editor + classique) pour eviter la double saisie.
			'show_in_rest'      => false,
			'meta_box_cb'       => false,
			'rewrite'           => array( 'slug' => 'pays' ),
		)
	);

	/*
	 * Type de partenaire : le
	 * site du Forum reel classe chaque partenaire par type d'engagement fixe
	 * (Institutionnel/Organisateur/Soutien/Sponsor) sur la fiche partenaire
	 * elle-meme - c'est le champ `type` de la collection Partners. Le slug de
	 * cette taxonomie (`fnc_niveau_partenariat`) est conserve tel quel pour ne
	 * pas casser les URLs/termes existants ; seul l'intitule admin est
	 * corrige, l'ancien nom pretait a confusion avec le champ distinct
	 * `niveau` (principal/majeur/officiel/contributeur), qui existe en plus,
	 * par edition — voir _fnc_partenaire_participations dans relations.php.
	 */
	register_taxonomy(
		'fnc_niveau_partenariat',
		array( 'fnc_partenaire' ),
		array(
			'labels'            => array(
				'name'          => __( 'Types de partenaire', 'fnc-content-model' ),
				'singular_name' => __( 'Type de partenaire', 'fnc-content-model' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'niveau-partenariat' ),
		)
	);
}
add_action( 'init', 'fnc_content_model_register_taxonomies' );
