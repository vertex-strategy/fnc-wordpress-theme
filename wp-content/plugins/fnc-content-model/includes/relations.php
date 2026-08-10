<?php
/**
 * Forum Numérique Congo — relations entre contenus et données dérivées.
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// --- Sessions ---------------------------------------------------------
const FNC_META_SESSION_EDITION    = '_fnc_session_edition';
const FNC_META_SESSION_SPEAKERS   = '_fnc_session_speakers';
const FNC_META_SESSION_MODERATOR  = '_fnc_session_moderator';
const FNC_META_SESSION_TYPE       = '_fnc_session_type';
const FNC_META_SESSION_TIME       = '_fnc_session_time';
const FNC_META_SESSION_START      = '_fnc_session_start';
const FNC_META_SESSION_END        = '_fnc_session_end';
const FNC_META_SESSION_ROOM       = '_fnc_session_room';
const FNC_META_SESSION_JOUR       = '_fnc_session_jour';
const FNC_META_SESSION_OBJECTIVES = '_fnc_session_objectives';
const FNC_META_SESSION_NOTE       = '_fnc_session_note';

// --- Publications -------------------------------------------------------
const FNC_META_PUBLICATION_EDITION   = '_fnc_publication_edition';
const FNC_META_PUBLICATION_TYPE      = '_fnc_publication_type';
const FNC_META_PUBLICATION_MEDIA_URL = '_fnc_publication_media_url';
const FNC_META_PUBLICATION_FILE      = '_fnc_publication_file';

// --- Editions -------------------------------------------------------------
const FNC_META_EDITION_ACTIVE       = '_fnc_edition_active';
const FNC_META_EDITION_STATUS       = '_fnc_edition_status';
const FNC_META_EDITION_YEAR         = '_fnc_edition_year';
const FNC_META_EDITION_THEME        = '_fnc_edition_theme';
const FNC_META_EDITION_START_DATE   = '_fnc_edition_start_date';
const FNC_META_EDITION_END_DATE     = '_fnc_edition_end_date';
const FNC_META_EDITION_LOCATION     = '_fnc_edition_location';
const FNC_META_EDITION_IS_SPECIAL   = '_fnc_edition_is_special';
const FNC_META_EDITION_SPECIAL_NOTE = '_fnc_edition_special_note';
// Rétrospective (édition passée) — aligné sur Editions.review/keyFigures/gallery du Next.
const FNC_META_EDITION_REVIEW       = '_fnc_edition_review';   // bilan (texte multi-ligne)
const FNC_META_EDITION_FIGURES      = '_fnc_edition_figures';  // chiffres clés : [ {value,label} ]
const FNC_META_EDITION_GALLERY      = '_fnc_edition_gallery';  // galerie : [ url, … ]

// --- Intervenants ---------------------------------------------------------
const FNC_META_SPEAKER_TITLE          = '_fnc_speaker_title';
// Fonction / rôle (intitulé de poste) — aligné sur Speakers.role du Next, rendu dans 7 gabarits.
const FNC_META_SPEAKER_ROLE           = '_fnc_speaker_role';
const FNC_META_SPEAKER_ORG            = '_fnc_speaker_org';
const FNC_META_SPEAKER_COUNTRY        = '_fnc_speaker_country';
const FNC_META_SPEAKER_PROTOCOL_ORDER = '_fnc_speaker_protocol_order';
// Départage secondaire de l'ordre global (après l'ordre protocolaire) — éditable.
const FNC_META_SPEAKER_SORT_INDEX     = '_fnc_speaker_sort_index';
// Promotion du carrousel « Les voix » (accueil) : mise en avant + rang.
const FNC_META_SPEAKER_HOME_FEATURED       = '_fnc_speaker_home_featured';
const FNC_META_SPEAKER_HOME_FEATURED_ORDER = '_fnc_speaker_home_featured_order';
const FNC_META_SPEAKER_LINKS          = '_fnc_speaker_links';
// la règle du droit à l’image — droit à l'image (consommé par FNC Core, les données du site : fnc_speaker_portrait).
const FNC_META_SPEAKER_IMAGE_RIGHT    = '_fnc_speaker_image_right';   // non_verifie|obtenu|refuse|expire
const FNC_META_SPEAKER_IMAGE_EXPIRES  = '_fnc_speaker_image_expires'; // date (optionnelle)

// --- Partenaires ------------------------------------------------------
const FNC_META_PARTENAIRE_SITE           = '_fnc_partenaire_site';
// Ordre éditorial des logos (accueil M6 + page Partenaires) — éditable.
const FNC_META_PARTENAIRE_SORT_INDEX     = '_fnc_partenaire_sort_index';
const FNC_META_PARTENAIRE_EDITIONS       = '_fnc_partenaire_editions';
const FNC_META_PARTENAIRE_PARTICIPATIONS = '_fnc_partenaire_participations';

/**
 * Listes de valeurs fixes, reprises telles quelles des champs `select` des
 * définitions correspondantes (voir les fichiers cites en tete de
 * fichier). Fonctions publiques : reutilisables par le theme (prochaine
 * passe gabarits) pour resoudre un libelle a partir d'une valeur stockee.
 */
function fnc_content_model_session_types() {
	return array(
		'allocution'   => __( 'Allocution', 'fnc-content-model' ),
		'inaugurale'   => __( 'Séance inaugurale', 'fnc-content-model' ),
		'interview'    => __( 'Interview', 'fnc-content-model' ),
		'table-ronde'  => __( 'Table ronde', 'fnc-content-model' ),
		'session'      => __( 'Session', 'fnc-content-model' ),
		'debat'        => __( 'Débat', 'fnc-content-model' ),
		'pause'        => __( 'Pause', 'fnc-content-model' ),
		'logistique'   => __( 'Logistique', 'fnc-content-model' ),
		'cloture'      => __( 'Clôture', 'fnc-content-model' ),
	);
}

function fnc_content_model_publication_types() {
	return array(
		'report'           => __( 'Rapport', 'fnc-content-model' ),
		'whitepaper'       => __( 'Livre blanc', 'fnc-content-model' ),
		'proceedings'      => __( 'Actes', 'fnc-content-model' ),
		'pressRelease'     => __( 'Communiqué de presse', 'fnc-content-model' ),
		'noteConceptuelle' => __( 'Note conceptuelle', 'fnc-content-model' ),
		'video'            => __( 'Vidéo', 'fnc-content-model' ),
		'interview'        => __( 'Interview', 'fnc-content-model' ),
		'other'            => __( 'Autre', 'fnc-content-model' ),
	);
}

function fnc_content_model_edition_statuses() {
	return array(
		'upcoming' => __( 'À venir', 'fnc-content-model' ),
		'current'  => __( 'En cours', 'fnc-content-model' ),
		'past'     => __( 'Passée', 'fnc-content-model' ),
	);
}

function fnc_content_model_partner_niveaux() {
	return array(
		'principal'    => __( 'Principal', 'fnc-content-model' ),
		'majeur'       => __( 'Majeur', 'fnc-content-model' ),
		'officiel'     => __( 'Officiel', 'fnc-content-model' ),
		'contributeur' => __( 'Contributeur', 'fnc-content-model' ),
	);
}

/**
 * Expose les meta en REST (compatibilite editeur de blocs / Polylang).
 */
function fnc_content_model_register_meta() {
	// Sessions.
	register_post_meta( 'fnc_session', FNC_META_SESSION_EDITION, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta(
		'fnc_session',
		FNC_META_SESSION_SPEAKERS,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ) ) ),
		)
	);
	register_post_meta( 'fnc_session', FNC_META_SESSION_MODERATOR, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_session', FNC_META_SESSION_TYPE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_session', FNC_META_SESSION_TIME, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_session', FNC_META_SESSION_START, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_session', FNC_META_SESSION_END, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_session', FNC_META_SESSION_ROOM, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_session', FNC_META_SESSION_JOUR, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta(
		'fnc_session',
		FNC_META_SESSION_OBJECTIVES,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ) ),
		)
	);
	register_post_meta( 'fnc_session', FNC_META_SESSION_NOTE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );

	// Publications.
	register_post_meta( 'fnc_publication', FNC_META_PUBLICATION_EDITION, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_publication', FNC_META_PUBLICATION_TYPE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_publication', FNC_META_PUBLICATION_MEDIA_URL, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_publication', FNC_META_PUBLICATION_FILE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );

	// Editions.
	register_post_meta( 'fnc_edition', FNC_META_EDITION_ACTIVE, array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_STATUS, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_YEAR, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_THEME, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_START_DATE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_END_DATE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_LOCATION, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_IS_SPECIAL, array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_SPECIAL_NOTE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_edition', FNC_META_EDITION_REVIEW, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta(
		'fnc_edition',
		FNC_META_EDITION_FIGURES,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'value' => array( 'type' => 'string' ),
							'label' => array( 'type' => 'string' ),
						),
					),
				),
			),
		)
	);
	register_post_meta(
		'fnc_edition',
		FNC_META_EDITION_GALLERY,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ) ),
		)
	);

	// Intervenants.
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_TITLE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_ROLE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_ORG, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_COUNTRY, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_PROTOCOL_ORDER, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_SORT_INDEX, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_HOME_FEATURED, array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_HOME_FEATURED_ORDER, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	// Champs de GOUVERNANCE interne du droit à l'image : usage serveur uniquement
	// (rendu / RÈGLE 7), jamais consommés par l'éditeur de blocs. NON exposés en REST
	// pour ne pas divulguer l'état de consentement d'un intervenant à un visiteur anonyme.
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_IMAGE_RIGHT, array( 'type' => 'string', 'single' => true, 'show_in_rest' => false ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_IMAGE_EXPIRES, array( 'type' => 'string', 'single' => true, 'show_in_rest' => false ) );
	register_post_meta(
		'fnc_intervenant',
		FNC_META_SPEAKER_LINKS,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'label' => array( 'type' => 'string' ),
							'url'   => array( 'type' => 'string' ),
						),
					),
				),
			),
		)
	);

	// Partenaires.
	register_post_meta( 'fnc_partenaire', FNC_META_PARTENAIRE_SITE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_partenaire', FNC_META_PARTENAIRE_SORT_INDEX, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta(
		'fnc_partenaire',
		FNC_META_PARTENAIRE_EDITIONS,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ) ) ),
		)
	);
	register_post_meta(
		'fnc_partenaire',
		FNC_META_PARTENAIRE_PARTICIPATIONS,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'edition' => array( 'type' => 'integer' ),
							'niveau'  => array( 'type' => 'string' ),
						),
					),
				),
			),
		)
	);
}
add_action( 'init', 'fnc_content_model_register_meta' );

/**
 * Meta boxes d'administration.
 */
function fnc_content_model_add_meta_boxes() {
	add_meta_box( 'fnc_session_relations', __( 'Édition, type et intervenants', 'fnc-content-model' ), 'fnc_content_model_render_session_meta_box', 'fnc_session', 'side' );
	add_meta_box( 'fnc_publication_relations', __( 'Édition liée et type', 'fnc-content-model' ), 'fnc_content_model_render_publication_meta_box', 'fnc_publication', 'side' );
	add_meta_box( 'fnc_edition_active', __( 'Statut et informations d’édition', 'fnc-content-model' ), 'fnc_content_model_render_edition_meta_box', 'fnc_edition', 'side' );
	add_meta_box( 'fnc_edition_retro', __( 'Rétrospective (édition passée)', 'fnc-content-model' ), 'fnc_content_model_render_edition_retro_meta_box', 'fnc_edition', 'normal', 'default' );
	add_meta_box( 'fnc_partenaire_details', __( 'Site web et éditions associées', 'fnc-content-model' ), 'fnc_content_model_render_partenaire_meta_box', 'fnc_partenaire', 'side' );
	add_meta_box( 'fnc_speaker_details', __( 'Identité et protocole', 'fnc-content-model' ), 'fnc_content_model_render_speaker_meta_box', 'fnc_intervenant', 'side' );
}
add_action( 'add_meta_boxes', 'fnc_content_model_add_meta_boxes' );

/**
 * Rendu d'un <select> d'editions publiees.
 */
function fnc_content_model_render_edition_select( $field_name, $selected_id ) {
	$editions = get_posts( array( 'post_type' => 'fnc_edition', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

	echo '<select name="' . esc_attr( $field_name ) . '" style="width:100%;">';
	echo '<option value="">' . esc_html__( '— Aucune —', 'fnc-content-model' ) . '</option>';
	foreach ( $editions as $edition ) {
		printf(
			'<option value="%d" %s>%s</option>',
			$edition->ID,
			selected( (int) $selected_id, $edition->ID, false ),
			esc_html( get_the_title( $edition ) )
		);
	}
	echo '</select>';
}

/**
 * Rendu d'un <select> d'intervenants (utilise pour le moderateur d'une
 * session — relation simple, distincte du tableau `speakers`).
 */
function fnc_content_model_render_speaker_select( $field_name, $selected_id ) {
	$speakers = get_posts( array( 'post_type' => 'fnc_intervenant', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

	echo '<select name="' . esc_attr( $field_name ) . '" style="width:100%;">';
	echo '<option value="">' . esc_html__( '— Aucun —', 'fnc-content-model' ) . '</option>';
	foreach ( $speakers as $speaker ) {
		printf(
			'<option value="%d" %s>%s</option>',
			$speaker->ID,
			selected( (int) $selected_id, $speaker->ID, false ),
			esc_html( get_the_title( $speaker ) )
		);
	}
	echo '</select>';
}

/**
 * Rendu d'un <select> generique a partir d'un tableau valeur => libelle.
 */
function fnc_content_model_render_select( $field_name, $options, $selected_value, $empty_label = '' ) {
	echo '<select name="' . esc_attr( $field_name ) . '" style="width:100%;">';
	if ( '' !== $empty_label ) {
		echo '<option value="">' . esc_html( $empty_label ) . '</option>';
	}
	foreach ( $options as $value => $label ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $value ),
			selected( $selected_value, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
}

function fnc_content_model_render_session_meta_box( $post ) {
	wp_nonce_field( 'fnc_session_relations_save', 'fnc_session_relations_nonce' );

	$edition_id  = get_post_meta( $post->ID, FNC_META_SESSION_EDITION, true );
	$moderator_id = get_post_meta( $post->ID, FNC_META_SESSION_MODERATOR, true );
	$type        = get_post_meta( $post->ID, FNC_META_SESSION_TYPE, true );
	$speaker_ids = get_post_meta( $post->ID, FNC_META_SESSION_SPEAKERS, true );
	$speaker_ids = is_array( $speaker_ids ) ? array_map( 'intval', $speaker_ids ) : array();
	$time        = get_post_meta( $post->ID, FNC_META_SESSION_TIME, true );
	$start       = get_post_meta( $post->ID, FNC_META_SESSION_START, true );
	$end         = get_post_meta( $post->ID, FNC_META_SESSION_END, true );
	$room        = get_post_meta( $post->ID, FNC_META_SESSION_ROOM, true );
	$jour        = get_post_meta( $post->ID, FNC_META_SESSION_JOUR, true );
	$objectives  = get_post_meta( $post->ID, FNC_META_SESSION_OBJECTIVES, true );
	$objectives  = is_array( $objectives ) ? implode( "\n", $objectives ) : '';
	$note        = get_post_meta( $post->ID, FNC_META_SESSION_NOTE, true );

	echo '<p><label for="fnc_session_type"><strong>' . esc_html__( 'Type de session', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_select( 'fnc_session_type', fnc_content_model_session_types(), $type, __( '— À préciser —', 'fnc-content-model' ) );

	echo '<p style="margin-top:12px;"><label for="fnc_session_jour"><strong>' . esc_html__( 'Jour', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_session_jour" name="fnc_session_jour" value="%s" placeholder="Jour 1" style="width:100%%;" /></p>', esc_attr( $jour ) );

	echo '<p><label for="fnc_session_start"><strong>' . esc_html__( 'Début', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_session_start" name="fnc_session_start" value="%s" placeholder="9h00" style="width:100%%;" /></p>', esc_attr( $start ) );

	echo '<p><label for="fnc_session_end"><strong>' . esc_html__( 'Fin', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_session_end" name="fnc_session_end" value="%s" placeholder="10h30" style="width:100%%;" /></p>', esc_attr( $end ) );
	echo '<p class="description">' . esc_html__( 'Début et fin recomposent automatiquement l’horaire affiché (déjà utilisé par les gabarits existants).', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_session_room"><strong>' . esc_html__( 'Salle', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_session_room" name="fnc_session_room" value="%s" style="width:100%%;" /></p>', esc_attr( $room ) );

	echo '<p><label for="fnc_session_note"><strong>' . esc_html__( 'Note', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_session_note" name="fnc_session_note" value="%s" style="width:100%%;" /></p>', esc_attr( $note ) );

	echo '<p><label for="fnc_session_objectives"><strong>' . esc_html__( 'Objectifs (un par ligne)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<textarea id="fnc_session_objectives" name="fnc_session_objectives" rows="3" style="width:100%%;">%s</textarea></p>', esc_textarea( $objectives ) );

	echo '<p><label for="fnc_session_edition"><strong>' . esc_html__( 'Édition', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_edition_select( 'fnc_session_edition', $edition_id );

	echo '<p style="margin-top:12px;"><label for="fnc_session_moderator"><strong>' . esc_html__( 'Modérateur', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_speaker_select( 'fnc_session_moderator', $moderator_id );
	echo '<p class="description">' . esc_html__( 'Distinct des intervenants ci-dessous — une session a un seul modérateur mais plusieurs intervenants.', 'fnc-content-model' ) . '</p>';

	echo '<p style="margin-top:16px;"><strong>' . esc_html__( 'Intervenants', 'fnc-content-model' ) . '</strong></p>';

	$speakers = get_posts( array( 'post_type' => 'fnc_intervenant', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

	if ( empty( $speakers ) ) {
		echo '<p>' . esc_html__( 'Aucun intervenant enregistré.', 'fnc-content-model' ) . '</p>';
		return;
	}

	echo '<div style="max-height:180px;overflow-y:auto;border:1px solid #dcdcde;padding:8px;">';
	foreach ( $speakers as $speaker ) {
		printf(
			'<label style="display:block;margin-bottom:4px;"><input type="checkbox" name="fnc_session_speakers[]" value="%1$d" %2$s /> %3$s</label>',
			$speaker->ID,
			checked( in_array( $speaker->ID, $speaker_ids, true ), true, false ),
			esc_html( get_the_title( $speaker ) )
		);
	}
	echo '</div>';
}

function fnc_content_model_render_edition_meta_box( $post ) {
	wp_nonce_field( 'fnc_edition_active_save', 'fnc_edition_active_nonce' );

	$status       = get_post_meta( $post->ID, FNC_META_EDITION_STATUS, true );
	$year         = get_post_meta( $post->ID, FNC_META_EDITION_YEAR, true );
	$theme        = get_post_meta( $post->ID, FNC_META_EDITION_THEME, true );
	$start_date   = get_post_meta( $post->ID, FNC_META_EDITION_START_DATE, true );
	$end_date     = get_post_meta( $post->ID, FNC_META_EDITION_END_DATE, true );
	$location     = get_post_meta( $post->ID, FNC_META_EDITION_LOCATION, true );
	$is_special   = get_post_meta( $post->ID, FNC_META_EDITION_IS_SPECIAL, true );
	$special_note = get_post_meta( $post->ID, FNC_META_EDITION_SPECIAL_NOTE, true );

	echo '<p><label for="fnc_edition_status"><strong>' . esc_html__( 'Statut', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_select( 'fnc_edition_status', fnc_content_model_edition_statuses(), $status );
	echo '<p class="description">' . esc_html__( 'Une seule édition « En cours » à la fois — en choisir une désactive automatiquement les autres (utilisé par la page « Édition en cours »).', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_edition_year"><strong>' . esc_html__( 'Année', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="number" id="fnc_edition_year" name="fnc_edition_year" value="%s" style="width:100%%;" /></p>', esc_attr( $year ) );

	echo '<p><label for="fnc_edition_theme"><strong>' . esc_html__( 'Thème', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_edition_theme" name="fnc_edition_theme" value="%s" style="width:100%%;" /></p>', esc_attr( $theme ) );

	echo '<p><label for="fnc_edition_start_date"><strong>' . esc_html__( 'Début', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="date" id="fnc_edition_start_date" name="fnc_edition_start_date" value="%s" style="width:100%%;" /></p>', esc_attr( $start_date ) );

	echo '<p><label for="fnc_edition_end_date"><strong>' . esc_html__( 'Fin', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="date" id="fnc_edition_end_date" name="fnc_edition_end_date" value="%s" style="width:100%%;" /></p>', esc_attr( $end_date ) );

	echo '<p><label for="fnc_edition_location"><strong>' . esc_html__( 'Lieu', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_edition_location" name="fnc_edition_location" value="%s" style="width:100%%;" /></p>', esc_attr( $location ) );

	printf(
		'<p style="margin-top:12px;"><label><input type="checkbox" name="fnc_edition_is_special" value="1" %s /> %s</label></p>',
		checked( $is_special, '1', false ),
		esc_html__( 'Édition spéciale', 'fnc-content-model' )
	);

	echo '<p><label for="fnc_edition_special_note"><strong>' . esc_html__( 'Note (édition spéciale)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_edition_special_note" name="fnc_edition_special_note" value="%s" style="width:100%%;" /></p>', esc_attr( $special_note ) );
}

/**
 * Méta-box « Rétrospective » (colonne principale) : bilan, chiffres clés et
 * galerie d'une édition passée — repris de Editions.review/keyFigures/gallery
 * du site de référence. Sans dépendance ni JavaScript d'administration
 * (galerie = URLs collées depuis la Médiathèque, même parti pris que le champ
 * « Fichier à télécharger » des publications).
 */
function fnc_content_model_render_edition_retro_meta_box( $post ) {
	wp_nonce_field( 'fnc_edition_retro_save', 'fnc_edition_retro_nonce' );

	$review  = get_post_meta( $post->ID, FNC_META_EDITION_REVIEW, true );
	$figures = get_post_meta( $post->ID, FNC_META_EDITION_FIGURES, true );
	$gallery = get_post_meta( $post->ID, FNC_META_EDITION_GALLERY, true );

	$figures_text = is_array( $figures )
		? implode(
			"\n",
			array_map(
				static function ( $fig ) {
					return trim( ( $fig['value'] ?? '' ) . '|' . ( $fig['label'] ?? '' ), '|' );
				},
				$figures
			)
		)
		: '';
	$gallery_text = is_array( $gallery )
		? implode(
			"\n",
			array_map(
				static function ( $g ) {
					return is_array( $g ) ? trim( ( $g['url'] ?? '' ) . '|' . ( $g['alt'] ?? '' ), '|' ) : (string) $g;
				},
				$gallery
			)
		)
		: '';

	echo '<p class="description">' . esc_html__( 'À renseigner pour les éditions passées : ce contenu s’affiche en bas de la fiche de l’édition (bilan, chiffres, galerie).', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_edition_review"><strong>' . esc_html__( 'Bilan / rétrospective', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<textarea id="fnc_edition_review" name="fnc_edition_review" rows="6" style="width:100%%;">%s</textarea></p>', esc_textarea( $review ) );

	echo '<p><label for="fnc_edition_figures"><strong>' . esc_html__( 'Chiffres clés (un par ligne, format « Valeur|Libellé »)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<textarea id="fnc_edition_figures" name="fnc_edition_figures" rows="4" style="width:100%%;" placeholder="1 200|Participants&#10;54|Pays représentés">%s</textarea></p>', esc_textarea( $figures_text ) );

	echo '<p><label for="fnc_edition_gallery"><strong>' . esc_html__( 'Galerie rétrospective (une image par ligne, format « URL|texte alternatif »)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<textarea id="fnc_edition_gallery" name="fnc_edition_gallery" rows="4" style="width:100%%;" placeholder="https://…/photo-1.jpg|Plénière d’ouverture">%s</textarea></p>', esc_textarea( $gallery_text ) );
	echo '<p class="description">' . esc_html__( 'Téléversez les images dans la Médiathèque puis collez ici leur adresse ET un texte alternatif, séparés par « | » (un média par ligne). Le texte alternatif est OBLIGATOIRE (accessibilité) : une ligne sans alt est ignorée. Aucun sélecteur média : le plugin reste sans dépendance JavaScript.', 'fnc-content-model' ) . '</p>';
}

function fnc_content_model_render_publication_meta_box( $post ) {
	wp_nonce_field( 'fnc_publication_relations_save', 'fnc_publication_relations_nonce' );

	$edition_id = get_post_meta( $post->ID, FNC_META_PUBLICATION_EDITION, true );
	$type       = get_post_meta( $post->ID, FNC_META_PUBLICATION_TYPE, true );
	$media_url  = get_post_meta( $post->ID, FNC_META_PUBLICATION_MEDIA_URL, true );
	$file_url   = get_post_meta( $post->ID, FNC_META_PUBLICATION_FILE, true );

	echo '<p><label for="fnc_publication_type"><strong>' . esc_html__( 'Type', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_select( 'fnc_publication_type', fnc_content_model_publication_types(), $type, __( '— À préciser —', 'fnc-content-model' ) );
	echo '<p class="description">' . esc_html__( 'Champ distinct de la catégorie (fnc_categorie) — le type qualifie la nature du document (rapport, vidéo…), la catégorie le classe thématiquement.', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_publication_media_url"><strong>' . esc_html__( 'URL média (vidéo/interview)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="url" id="fnc_publication_media_url" name="fnc_publication_media_url" value="%s" placeholder="https://" style="width:100%%;" /></p>', esc_attr( $media_url ) );

	echo '<p><label for="fnc_publication_file"><strong>' . esc_html__( 'Fichier à télécharger', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="url" id="fnc_publication_file" name="fnc_publication_file" value="%s" placeholder="https://" style="width:100%%;" /></p>', esc_attr( $file_url ) );
	echo '<p class="description">' . esc_html__( 'URL du document (PDF…). Téléversez-le dans la Médiathèque puis copiez son adresse ici. Un champ URL plutôt qu’un sélecteur de média : le plugin reste sans dépendance ni JavaScript d’administration.', 'fnc-content-model' ) . '</p>';

	echo '<p style="margin-top:16px;"><label for="fnc_publication_edition">' . esc_html__( 'Édition liée (optionnel)', 'fnc-content-model' ) . '</label></p>';
	fnc_content_model_render_edition_select( 'fnc_publication_edition', $edition_id );
}

/**
 * Répartit les liens stockés (_fnc_speaker_links : [ {label,url} ]) dans les
 * champs dédiés (site / LinkedIn / X) + un reliquat « autres », pour pré-remplir
 * l'éditeur. La saisie reste recomposée dans le même _fnc_speaker_links à
 * l'enregistrement : aucun changement du contrat de rendu.
 *
 * @return array{site:string,linkedin:string,x:string,autres:array<int,array{label:string,url:string}>}
 */
function fnc_content_model_bucket_speaker_links( $links ) {
	$out = array( 'site' => '', 'linkedin' => '', 'x' => '', 'autres' => array() );
	if ( ! is_array( $links ) ) {
		return $out;
	}
	foreach ( $links as $link ) {
		$url   = is_array( $link ) ? ( $link['url'] ?? '' ) : '';
		$label = is_array( $link ) ? ( $link['label'] ?? '' ) : '';
		if ( '' === $url ) {
			continue;
		}
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( '' === $out['linkedin'] && false !== strpos( $host, 'linkedin.' ) ) {
			$out['linkedin'] = $url;
		} elseif ( '' === $out['x'] && ( false !== strpos( $host, 'twitter.' ) || preg_match( '#(^|\.)x\.com$#', $host ) ) ) {
			$out['x'] = $url;
		} elseif ( '' === $out['site'] && preg_match( '/site|web/i', (string) $label ) ) {
			$out['site'] = $url;
		} else {
			$out['autres'][] = array( 'label' => (string) $label, 'url' => (string) $url );
		}
	}
	return $out;
}

function fnc_content_model_render_speaker_meta_box( $post ) {
	wp_nonce_field( 'fnc_speaker_details_save', 'fnc_speaker_details_nonce' );

	$title          = get_post_meta( $post->ID, FNC_META_SPEAKER_TITLE, true );
	$role           = get_post_meta( $post->ID, FNC_META_SPEAKER_ROLE, true );
	$org            = get_post_meta( $post->ID, FNC_META_SPEAKER_ORG, true );
	$country        = get_post_meta( $post->ID, FNC_META_SPEAKER_COUNTRY, true );
	$protocol_order = get_post_meta( $post->ID, FNC_META_SPEAKER_PROTOCOL_ORDER, true );
	$links_raw      = get_post_meta( $post->ID, FNC_META_SPEAKER_LINKS, true );

	echo '<p><label for="fnc_speaker_title"><strong>' . esc_html__( 'Civilité', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_title" name="fnc_speaker_title" value="%s" placeholder="Pr., Dr., M., Mme…" style="width:100%%;" /></p>', esc_attr( $title ) );

	echo '<p><label for="fnc_speaker_role"><strong>' . esc_html__( 'Fonction', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_role" name="fnc_speaker_role" value="%s" placeholder="Ministre du Numérique…" style="width:100%%;" /></p>', esc_attr( $role ) );
	echo '<p class="description">' . esc_html__( 'Intitulé exact de la fonction, affiché sous le nom partout (fiche, annuaire, carrousel…).', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_speaker_org"><strong>' . esc_html__( 'Organisation', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_org" name="fnc_speaker_org" value="%s" style="width:100%%;" /></p>', esc_attr( $org ) );

	echo '<p><label for="fnc_speaker_country"><strong>' . esc_html__( 'Pays', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_country" name="fnc_speaker_country" value="%s" placeholder="France / États-Unis" style="width:100%%;" /></p>', esc_attr( $country ) );
	echo '<p class="description">' . esc_html__( 'Champ texte libre (peut cumuler plusieurs pays séparés par « / »), distinct de la taxonomie Profils utilisée pour le filtre par nature d’intervenant.', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_speaker_protocol_order"><strong>' . esc_html__( 'Rang protocolaire', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="number" id="fnc_speaker_protocol_order" name="fnc_speaker_protocol_order" value="%s" style="width:100%%;" /></p>', esc_attr( $protocol_order ) );

	$sort_index = get_post_meta( $post->ID, FNC_META_SPEAKER_SORT_INDEX, true );
	echo '<p><label for="fnc_speaker_sort_index"><strong>' . esc_html__( 'Ordre secondaire', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="number" id="fnc_speaker_sort_index" name="fnc_speaker_sort_index" value="%s" style="width:100%%;" /></p>', esc_attr( $sort_index ) );
	echo '<p class="description">' . esc_html__( 'Départage les intervenants de même rang protocolaire (le plus petit d’abord). Utilisé partout : page Intervenants, participants, aperçus.', 'fnc-content-model' ) . '</p>';

	// Promotion du carrousel « Les voix » (accueil).
	$home_featured       = get_post_meta( $post->ID, FNC_META_SPEAKER_HOME_FEATURED, true );
	$home_featured_order = get_post_meta( $post->ID, FNC_META_SPEAKER_HOME_FEATURED_ORDER, true );
	echo '<hr /><p><label for="fnc_speaker_home_featured">';
	printf( '<input type="checkbox" id="fnc_speaker_home_featured" name="fnc_speaker_home_featured" value="1"%s /> ', checked( (bool) $home_featured, true, false ) );
	echo '<strong>' . esc_html__( 'Mettre en avant dans le carrousel « Les voix » (accueil)', 'fnc-content-model' ) . '</strong></label></p>';
	echo '<p><label for="fnc_speaker_home_featured_order"><strong>' . esc_html__( 'Rang de mise en avant', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="number" id="fnc_speaker_home_featured_order" name="fnc_speaker_home_featured_order" value="%s" style="width:100%%;" /></p>', esc_attr( $home_featured_order ) );
	echo '<p class="description">' . esc_html__( 'Les voix mises en avant passent en tête du carrousel, triées par ce rang. Doit être un participant de l’édition en cours. Carrousel plafonné à 10.', 'fnc-content-model' ) . '</p>';

	// Avertissement de découvrabilité : une voix promue mais NON participante de
	// l'édition en cours n'apparaît pas au carrousel (périmètre « voix de cette
	// édition »). On le signale explicitement à l'éditeur.
	if ( $home_featured && function_exists( 'fnc_current_edition_id' ) && function_exists( 'fnc_edition_participants' ) ) {
		$active_ed = fnc_current_edition_id();
		$is_part   = $active_ed && in_array( (int) $post->ID, array_map( 'intval', fnc_edition_participants( $active_ed ) ), true );
		if ( ! $is_part ) {
			echo '<p class="description" style="padding:8px;border-left:3px solid #d63638;background:#fcf0f1;">'
				. esc_html__( '⚠ Mis en avant mais PAS encore participant de l’édition en cours : cette voix n’apparaîtra pas dans le carrousel tant qu’elle n’est pas rattachée (comme intervenant ou modérateur) à une session publiée de l’édition active.', 'fnc-content-model' )
				. '</p>';
		}
	}

	// Liens : champs dédiés (site / LinkedIn / X) + reliquat « autres ».
	// Recomposés dans _fnc_speaker_links à l'enregistrement (contrat inchangé).
	$link_buckets = fnc_content_model_bucket_speaker_links( $links_raw );
	$autres_text  = implode(
		"\n",
		array_map(
			static function ( $l ) {
				return trim( $l['label'] . '|' . $l['url'], '|' );
			},
			$link_buckets['autres']
		)
	);
	echo '<hr /><p><strong>' . esc_html__( 'Liens', 'fnc-content-model' ) . '</strong></p>';
	echo '<p><label for="fnc_speaker_link_site">' . esc_html__( 'Site web', 'fnc-content-model' ) . '</label><br />';
	printf( '<input type="url" id="fnc_speaker_link_site" name="fnc_speaker_link_site" value="%s" placeholder="https://" style="width:100%%;" /></p>', esc_attr( $link_buckets['site'] ) );
	echo '<p><label for="fnc_speaker_link_linkedin">' . esc_html__( 'LinkedIn', 'fnc-content-model' ) . '</label><br />';
	printf( '<input type="url" id="fnc_speaker_link_linkedin" name="fnc_speaker_link_linkedin" value="%s" placeholder="https://www.linkedin.com/in/…" style="width:100%%;" /></p>', esc_attr( $link_buckets['linkedin'] ) );
	echo '<p><label for="fnc_speaker_link_x">' . esc_html__( 'X (Twitter)', 'fnc-content-model' ) . '</label><br />';
	printf( '<input type="url" id="fnc_speaker_link_x" name="fnc_speaker_link_x" value="%s" placeholder="https://x.com/…" style="width:100%%;" /></p>', esc_attr( $link_buckets['x'] ) );
	echo '<p><label for="fnc_speaker_links"><strong>' . esc_html__( 'Autres liens (un par ligne, format « Libellé|URL »)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<textarea id="fnc_speaker_links" name="fnc_speaker_links" rows="3" style="width:100%%;" placeholder="Rapport 2026|https://…">%s</textarea></p>', esc_textarea( $autres_text ) );

	// la règle du droit à l’image — droit à l'image : le portrait n'est publié que si « obtenu » et non expiré.
	$image_right   = get_post_meta( $post->ID, FNC_META_SPEAKER_IMAGE_RIGHT, true );
	$image_expires = get_post_meta( $post->ID, FNC_META_SPEAKER_IMAGE_EXPIRES, true );
	$right_options = array(
		'non_verifie' => __( 'Non vérifié', 'fnc-content-model' ),
		'obtenu'      => __( 'Obtenu', 'fnc-content-model' ),
		'refuse'      => __( 'Refusé', 'fnc-content-model' ),
		'expire'      => __( 'Expiré', 'fnc-content-model' ),
	);
	echo '<hr /><p><label for="fnc_speaker_image_right"><strong>' . esc_html__( 'Droit à l’image (la règle du droit à l’image)', 'fnc-content-model' ) . '</strong></label><br />';
	echo '<select id="fnc_speaker_image_right" name="fnc_speaker_image_right" style="width:100%;">';
	foreach ( $right_options as $val => $lab ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $val ), selected( $image_right ? $image_right : 'non_verifie', $val, false ), esc_html( $lab ) );
	}
	echo '</select></p>';
	echo '<p class="description">' . esc_html__( 'Le portrait n’est affiché publiquement que si le droit est « Obtenu » et non expiré. Par défaut fermé.', 'fnc-content-model' ) . '</p>';
	echo '<p><label for="fnc_speaker_image_expires"><strong>' . esc_html__( 'Expiration du droit (optionnel)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="date" id="fnc_speaker_image_expires" name="fnc_speaker_image_expires" value="%s" style="width:100%%;" /></p>', esc_attr( $image_expires ) );
}

function fnc_content_model_render_partenaire_meta_box( $post ) {
	wp_nonce_field( 'fnc_partenaire_details_save', 'fnc_partenaire_details_nonce' );

	$site           = get_post_meta( $post->ID, FNC_META_PARTENAIRE_SITE, true );
	$participations = get_post_meta( $post->ID, FNC_META_PARTENAIRE_PARTICIPATIONS, true );
	$participations = is_array( $participations ) ? $participations : array();
	$niveau_by_edition = array();
	foreach ( $participations as $participation ) {
		if ( ! empty( $participation['edition'] ) ) {
			$niveau_by_edition[ (int) $participation['edition'] ] = $participation['niveau'] ?? '';
		}
	}

	echo '<p><label for="fnc_partenaire_site"><strong>' . esc_html__( 'Site web', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="url" id="fnc_partenaire_site" name="fnc_partenaire_site" value="%s" placeholder="https://" style="width:100%%;" /></p>', esc_attr( $site ) );

	$sort_index = get_post_meta( $post->ID, FNC_META_PARTENAIRE_SORT_INDEX, true );
	echo '<p><label for="fnc_partenaire_sort_index"><strong>' . esc_html__( 'Ordre d’affichage (logos)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="number" id="fnc_partenaire_sort_index" name="fnc_partenaire_sort_index" value="%s" style="width:100%%;" /></p>', esc_attr( $sort_index ) );
	echo '<p class="description">' . esc_html__( 'Ordonne les logos sur l’accueil (bloc « La communauté ») et la page Partenaires. Le plus petit d’abord : donnez 0, 1 et 2 aux trois logos de tête (les deux organisateurs et le sponsor officiel). L’accueil affiche 8 logos maximum (3 grands en haut + 5 petits).', 'fnc-content-model' ) . '</p>';

	echo '<p style="margin-top:16px;"><strong>' . esc_html__( 'Participations par édition', 'fnc-content-model' ) . '</strong></p>';
	echo '<p class="description">' . esc_html__( 'Le type de partenaire (institutionnel/organisateur/soutien/sponsor) se règle via la taxonomie « Type de partenaire » dans la colonne de droite. Le niveau ci-dessous est plus fin et varie par édition (ex. sponsor « principal » en 2025, « contributeur » en 2027).', 'fnc-content-model' ) . '</p>';

	$editions = get_posts( array( 'post_type' => 'fnc_edition', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

	if ( empty( $editions ) ) {
		echo '<p>' . esc_html__( 'Aucune édition enregistrée.', 'fnc-content-model' ) . '</p>';
		return;
	}

	echo '<div style="max-height:220px;overflow-y:auto;border:1px solid #dcdcde;padding:8px;">';
	foreach ( $editions as $edition ) {
		$checked_edition = isset( $niveau_by_edition[ $edition->ID ] );
		echo '<div style="margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #f0f0f1;">';
		printf(
			'<label style="display:block;margin-bottom:4px;"><input type="checkbox" name="fnc_partenaire_participation_editions[]" value="%1$d" %2$s /> %3$s</label>',
			$edition->ID,
			checked( $checked_edition, true, false ),
			esc_html( get_the_title( $edition ) )
		);
		fnc_content_model_render_select(
			'fnc_partenaire_participation_niveau_' . $edition->ID,
			fnc_content_model_partner_niveaux(),
			$checked_edition ? $niveau_by_edition[ $edition->ID ] : '',
			__( '— Niveau —', 'fnc-content-model' )
		);
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Sauvegarde des relations, avec verification de nonce et de capacite.
 */
function fnc_content_model_save_relations( $post_id, $post ) {
	if ( ! isset( $_POST['fnc_session_relations_nonce'] )
		&& ! isset( $_POST['fnc_publication_relations_nonce'] )
		&& ! isset( $_POST['fnc_edition_active_nonce'] )
		&& ! isset( $_POST['fnc_edition_retro_nonce'] )
		&& ! isset( $_POST['fnc_partenaire_details_nonce'] )
		&& ! isset( $_POST['fnc_speaker_details_nonce'] )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'fnc_session' === $post->post_type
		&& isset( $_POST['fnc_session_relations_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_session_relations_nonce'] ) ), 'fnc_session_relations_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		$edition_id = isset( $_POST['fnc_session_edition'] ) ? absint( $_POST['fnc_session_edition'] ) : 0;
		if ( $edition_id > 0 ) {
			update_post_meta( $post_id, FNC_META_SESSION_EDITION, $edition_id );
		} else {
			delete_post_meta( $post_id, FNC_META_SESSION_EDITION );
		}

		$moderator_id = isset( $_POST['fnc_session_moderator'] ) ? absint( $_POST['fnc_session_moderator'] ) : 0;
		if ( $moderator_id > 0 ) {
			update_post_meta( $post_id, FNC_META_SESSION_MODERATOR, $moderator_id );
		} else {
			delete_post_meta( $post_id, FNC_META_SESSION_MODERATOR );
		}

		$type = isset( $_POST['fnc_session_type'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_session_type'] ) ) : '';
		update_post_meta( $post_id, FNC_META_SESSION_TYPE, array_key_exists( $type, fnc_content_model_session_types() ) ? $type : '' );

		$speaker_ids = isset( $_POST['fnc_session_speakers'] ) && is_array( $_POST['fnc_session_speakers'] )
			? array_map( 'absint', wp_unslash( $_POST['fnc_session_speakers'] ) )
			: array();
		update_post_meta( $post_id, FNC_META_SESSION_SPEAKERS, $speaker_ids );

		$start = isset( $_POST['fnc_session_start'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_session_start'] ) ) : '';
		$end   = isset( $_POST['fnc_session_end'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_session_end'] ) ) : '';
		update_post_meta( $post_id, FNC_META_SESSION_START, $start );
		update_post_meta( $post_id, FNC_META_SESSION_END, $end );
		// _fnc_session_time reste le libelle affiche par les gabarits actuels
		// (existants avant cette passe) — recompose automatiquement a partir
		// de debut/fin des qu'ils sont renseignes, sans casser ces gabarits.
		if ( '' !== $start || '' !== $end ) {
			update_post_meta( $post_id, FNC_META_SESSION_TIME, trim( $start . ( $end ? ' – ' . $end : '' ) ) );
		}

		update_post_meta( $post_id, FNC_META_SESSION_ROOM, isset( $_POST['fnc_session_room'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_session_room'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SESSION_JOUR, isset( $_POST['fnc_session_jour'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_session_jour'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SESSION_NOTE, isset( $_POST['fnc_session_note'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_session_note'] ) ) : '' );

		$objectives_raw = isset( $_POST['fnc_session_objectives'] ) ? (string) wp_unslash( $_POST['fnc_session_objectives'] ) : '';
		$objectives     = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $objectives_raw ) ) ) );
		update_post_meta( $post_id, FNC_META_SESSION_OBJECTIVES, array_map( 'sanitize_text_field', $objectives ) );
	}

	if ( 'fnc_publication' === $post->post_type
		&& isset( $_POST['fnc_publication_relations_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_publication_relations_nonce'] ) ), 'fnc_publication_relations_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		$edition_id = isset( $_POST['fnc_publication_edition'] ) ? absint( $_POST['fnc_publication_edition'] ) : 0;
		if ( $edition_id > 0 ) {
			update_post_meta( $post_id, FNC_META_PUBLICATION_EDITION, $edition_id );
		} else {
			delete_post_meta( $post_id, FNC_META_PUBLICATION_EDITION );
		}

		$type = isset( $_POST['fnc_publication_type'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_publication_type'] ) ) : '';
		update_post_meta( $post_id, FNC_META_PUBLICATION_TYPE, array_key_exists( $type, fnc_content_model_publication_types() ) ? $type : '' );

		update_post_meta( $post_id, FNC_META_PUBLICATION_MEDIA_URL, isset( $_POST['fnc_publication_media_url'] ) ? esc_url_raw( wp_unslash( $_POST['fnc_publication_media_url'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_PUBLICATION_FILE, isset( $_POST['fnc_publication_file'] ) ? esc_url_raw( wp_unslash( $_POST['fnc_publication_file'] ) ) : '' );
	}

	if ( 'fnc_edition' === $post->post_type
		&& isset( $_POST['fnc_edition_active_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_edition_active_nonce'] ) ), 'fnc_edition_active_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		$status = isset( $_POST['fnc_edition_status'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_edition_status'] ) ) : '';
		$status = array_key_exists( $status, fnc_content_model_edition_statuses() ) ? $status : '';
		update_post_meta( $post_id, FNC_META_EDITION_STATUS, $status );

		// _fnc_edition_active reste le champ lu par les gabarits existants
		// (page-edition-en-cours.php) — derive directement du statut, une
		// seule edition "En cours" a la fois (meme exclusivite qu'avant).
		if ( 'current' === $status ) {
			$other_editions = get_posts(
				array(
					'post_type'      => 'fnc_edition',
					'posts_per_page' => -1,
					'exclude'        => array( $post_id ),
					'fields'         => 'ids',
					'meta_key'       => FNC_META_EDITION_ACTIVE,
					'meta_value'     => '1',
				)
			);
			foreach ( $other_editions as $other_id ) {
				// Contrôle de capacité PAR OBJET : ne jamais écrire la méta d'une édition
				// que l'utilisateur courant n'a pas le droit d'éditer (anti-écriture
				// horizontale). L'exclusivité « une seule édition active » ne doit pas
				// permettre d'altérer l'état d'objets tiers hors périmètre de l'auteur.
				if ( current_user_can( 'edit_post', $other_id ) ) {
					update_post_meta( $other_id, FNC_META_EDITION_ACTIVE, '' );
				}
			}
			update_post_meta( $post_id, FNC_META_EDITION_ACTIVE, '1' );
		} else {
			update_post_meta( $post_id, FNC_META_EDITION_ACTIVE, '' );
		}

		update_post_meta( $post_id, FNC_META_EDITION_YEAR, isset( $_POST['fnc_edition_year'] ) ? absint( $_POST['fnc_edition_year'] ) : '' );
		update_post_meta( $post_id, FNC_META_EDITION_THEME, isset( $_POST['fnc_edition_theme'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_edition_theme'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_EDITION_START_DATE, isset( $_POST['fnc_edition_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_edition_start_date'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_EDITION_END_DATE, isset( $_POST['fnc_edition_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_edition_end_date'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_EDITION_LOCATION, isset( $_POST['fnc_edition_location'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_edition_location'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_EDITION_IS_SPECIAL, ! empty( $_POST['fnc_edition_is_special'] ) ? '1' : '' );
		update_post_meta( $post_id, FNC_META_EDITION_SPECIAL_NOTE, isset( $_POST['fnc_edition_special_note'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_edition_special_note'] ) ) : '' );
	}

	if ( 'fnc_edition' === $post->post_type
		&& isset( $_POST['fnc_edition_retro_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_edition_retro_nonce'] ) ), 'fnc_edition_retro_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		update_post_meta( $post_id, FNC_META_EDITION_REVIEW, isset( $_POST['fnc_edition_review'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fnc_edition_review'] ) ) : '' );

		// Chiffres clés : « Valeur|Libellé » par ligne -> [ {value,label} ].
		$figures_raw = isset( $_POST['fnc_edition_figures'] ) ? (string) wp_unslash( $_POST['fnc_edition_figures'] ) : '';
		$figures     = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $figures_raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts     = array_map( 'trim', explode( '|', $line, 2 ) );
			$figures[] = array(
				'value' => sanitize_text_field( $parts[0] ?? '' ),
				'label' => sanitize_text_field( $parts[1] ?? '' ),
			);
		}
		update_post_meta( $post_id, FNC_META_EDITION_FIGURES, $figures );

		// Galerie : « URL|texte alternatif » par ligne -> [ {url,alt}, … ].
		// #4b : l'alt est OBLIGATOIRE (a11y) — une ligne sans alt est ignorée.
		$gallery_raw = isset( $_POST['fnc_edition_gallery'] ) ? (string) wp_unslash( $_POST['fnc_edition_gallery'] ) : '';
		$gallery     = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $gallery_raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$url   = esc_url_raw( $parts[0] ?? '' );
			$alt   = isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '';
			if ( '' !== $url && '' !== $alt ) {
				$gallery[] = array( 'url' => $url, 'alt' => $alt );
			}
		}
		update_post_meta( $post_id, FNC_META_EDITION_GALLERY, $gallery );
	}

	if ( 'fnc_intervenant' === $post->post_type
		&& isset( $_POST['fnc_speaker_details_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_speaker_details_nonce'] ) ), 'fnc_speaker_details_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		update_post_meta( $post_id, FNC_META_SPEAKER_TITLE, isset( $_POST['fnc_speaker_title'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_title'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_ROLE, isset( $_POST['fnc_speaker_role'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_role'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_ORG, isset( $_POST['fnc_speaker_org'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_org'] ) ) : '' );
		$fnc_country_text = isset( $_POST['fnc_speaker_country'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_country'] ) ) : '';
		update_post_meta( $post_id, FNC_META_SPEAKER_COUNTRY, $fnc_country_text );

		// Synchronisation Pays : le champ texte est l'UNIQUE point de saisie ; on en
		// derive la taxonomie fnc_pays (utilisee par le filtre de l'annuaire), en
		// decoupant les pays multiples (« France / Cameroun »). Le panneau de la
		// taxonomie est masque (meta_box_cb / show_in_rest = false, taxonomies.php)
		// pour eviter la double saisie et l'incoherence affichage/filtre.
		$fnc_country_names = array_values( array_filter( array_map( 'trim', preg_split( '#\s*[/,]\s*#', $fnc_country_text ) ) ) );
		wp_set_object_terms( $post_id, $fnc_country_names, 'fnc_pays', false );

		update_post_meta( $post_id, FNC_META_SPEAKER_PROTOCOL_ORDER, isset( $_POST['fnc_speaker_protocol_order'] ) ? absint( $_POST['fnc_speaker_protocol_order'] ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_SORT_INDEX, isset( $_POST['fnc_speaker_sort_index'] ) && '' !== $_POST['fnc_speaker_sort_index'] ? absint( $_POST['fnc_speaker_sort_index'] ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_HOME_FEATURED, empty( $_POST['fnc_speaker_home_featured'] ) ? '' : 1 );
		update_post_meta( $post_id, FNC_META_SPEAKER_HOME_FEATURED_ORDER, isset( $_POST['fnc_speaker_home_featured_order'] ) && '' !== $_POST['fnc_speaker_home_featured_order'] ? absint( $_POST['fnc_speaker_home_featured_order'] ) : '' );

		$fnc_right_allowed = array( 'non_verifie', 'obtenu', 'refuse', 'expire' );
		$fnc_right         = isset( $_POST['fnc_speaker_image_right'] ) ? sanitize_key( wp_unslash( $_POST['fnc_speaker_image_right'] ) ) : 'non_verifie';
		update_post_meta( $post_id, FNC_META_SPEAKER_IMAGE_RIGHT, in_array( $fnc_right, $fnc_right_allowed, true ) ? $fnc_right : 'non_verifie' );
		update_post_meta( $post_id, FNC_META_SPEAKER_IMAGE_EXPIRES, isset( $_POST['fnc_speaker_image_expires'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_image_expires'] ) ) : '' );

		// Liens : champs dédiés (site / LinkedIn / X) recomposés en tête, puis le
		// reliquat « autres » (Libellé|URL par ligne), dans le même
		// _fnc_speaker_links (contrat de rendu inchangé).
		$links    = array();
		$dedicated = array(
			'fnc_speaker_link_site'     => __( 'Site web', 'fnc-content-model' ),
			'fnc_speaker_link_linkedin' => 'LinkedIn',
			'fnc_speaker_link_x'        => 'X',
		);
		foreach ( $dedicated as $field => $label ) {
			$url = isset( $_POST[ $field ] ) ? esc_url_raw( wp_unslash( $_POST[ $field ] ) ) : '';
			if ( '' !== $url ) {
				$links[] = array( 'label' => $label, 'url' => $url );
			}
		}

		$links_raw = isset( $_POST['fnc_speaker_links'] ) ? (string) wp_unslash( $_POST['fnc_speaker_links'] ) : '';
		foreach ( preg_split( '/\r\n|\r|\n/', $links_raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$url   = esc_url_raw( $parts[1] ?? '' );
			if ( '' === $url ) {
				continue;
			}
			$links[] = array(
				'label' => sanitize_text_field( $parts[0] ?? '' ),
				'url'   => $url,
			);
		}
		update_post_meta( $post_id, FNC_META_SPEAKER_LINKS, $links );
	}

	if ( 'fnc_partenaire' === $post->post_type
		&& isset( $_POST['fnc_partenaire_details_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_partenaire_details_nonce'] ) ), 'fnc_partenaire_details_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		update_post_meta( $post_id, FNC_META_PARTENAIRE_SITE, isset( $_POST['fnc_partenaire_site'] ) ? esc_url_raw( wp_unslash( $_POST['fnc_partenaire_site'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_PARTENAIRE_SORT_INDEX, isset( $_POST['fnc_partenaire_sort_index'] ) && '' !== $_POST['fnc_partenaire_sort_index'] ? absint( $_POST['fnc_partenaire_sort_index'] ) : '' );

		$selected_editions = isset( $_POST['fnc_partenaire_participation_editions'] ) && is_array( $_POST['fnc_partenaire_participation_editions'] )
			? array_map( 'absint', wp_unslash( $_POST['fnc_partenaire_participation_editions'] ) )
			: array();

		$participations = array();
		foreach ( $selected_editions as $edition_id ) {
			$niveau_field = 'fnc_partenaire_participation_niveau_' . $edition_id;
			$niveau       = isset( $_POST[ $niveau_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $niveau_field ] ) ) : '';
			$participations[] = array(
				'edition' => $edition_id,
				'niveau'  => array_key_exists( $niveau, fnc_content_model_partner_niveaux() ) ? $niveau : '',
			);
		}
		update_post_meta( $post_id, FNC_META_PARTENAIRE_PARTICIPATIONS, $participations );

		// _fnc_partenaire_editions reste le champ lu par page-partenaires.php
		// (liste plate d'ID) — conserve en synchronisation avec les
		// participations ci-dessus, pour ne pas casser ce gabarit.
		update_post_meta( $post_id, FNC_META_PARTENAIRE_EDITIONS, $selected_editions );
	}
}
add_action( 'save_post', 'fnc_content_model_save_relations', 10, 2 );

/**
 * Vrai si l'URL pointe vers un PDF (extension .pdf ou piece jointe MIME pdf).
 *
 * @param string $url
 * @return bool
 */
function fnc_content_model_is_pdf_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return false;
	}
	// Extension .pdf (en ignorant une eventuelle chaine de requete/ancre).
	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( is_string( $path ) && preg_match( '/\.pdf$/i', $path ) ) {
		return true;
	}
	// Sinon : piece jointe de la mediatheque dont le type MIME est PDF.
	$attachment_id = attachment_url_to_postid( $url );
	if ( $attachment_id && 'application/pdf' === get_post_mime_type( $attachment_id ) ) {
		return true;
	}
	return false;
}

/**
 * Validation du fichier de publication a la PUBLICATION — alignee sur Payload
 * (Publications.ts) : validation PDF *conditionnelle*, jamais fichier obligatoire.
 *
 * Regle :
 *   - Une publication PEUT etre publiee SANS fichier (pas de blocage sur vide).
 *   - Les types « video » / « interview » utilisent `media_url` : aucun PDF requis.
 *   - Pour TOUT autre type : SI un fichier est renseigne, il DOIT etre un PDF.
 *
 * En cas d'ecart, on ne rejette pas silencieusement : on repasse la publication
 * en brouillon et on affiche un message a l'editeur (transient). Comportement
 * volontairement PAS plus strict que le site Next.
 *
 * @param int      $post_id
 * @param \WP_Post $post
 * @return void
 */
function fnc_content_model_validate_publication_pdf( $post_id, $post ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( 'publish' !== $post->post_status ) {
		return;
	}

	$file = (string) get_post_meta( $post_id, FNC_META_PUBLICATION_FILE, true );
	if ( '' === trim( $file ) ) {
		return; // Publier sans fichier est autorise (parite Payload).
	}

	$type = (string) get_post_meta( $post_id, FNC_META_PUBLICATION_TYPE, true );
	if ( in_array( $type, array( 'video', 'interview' ), true ) ) {
		return; // Ces types s'appuient sur media_url, pas de PDF attendu.
	}

	if ( fnc_content_model_is_pdf_url( $file ) ) {
		return; // Conforme.
	}

	// Non conforme : repasser en brouillon (garde anti-recursion) + message.
	remove_action( 'save_post', 'fnc_content_model_save_relations', 10 );
	remove_action( 'save_post_fnc_publication', 'fnc_content_model_validate_publication_pdf', 20 );
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_status' => 'draft',
		)
	);
	add_action( 'save_post', 'fnc_content_model_save_relations', 10, 2 );
	add_action( 'save_post_fnc_publication', 'fnc_content_model_validate_publication_pdf', 20, 2 );

	set_transient(
		'fnc_cm_pub_notice_' . get_current_user_id(),
		__( 'Publication remise en brouillon : le fichier renseigné doit être un PDF (types « Vidéo » et « Interview » exceptés, qui utilisent l’URL média). Fournissez un PDF ou videz le champ « Fichier à télécharger » pour publier.', 'fnc-content-model' ),
		60
	);
}
add_action( 'save_post_fnc_publication', 'fnc_content_model_validate_publication_pdf', 20, 2 );

/**
 * Message d'admin apres remise en brouillon d'une publication non conforme.
 *
 * @return void
 */
function fnc_content_model_publication_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'fnc_publication' !== $screen->post_type ) {
		return;
	}
	$key     = 'fnc_cm_pub_notice_' . get_current_user_id();
	$message = get_transient( $key );
	if ( ! $message ) {
		return;
	}
	delete_transient( $key );
	printf(
		'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
		esc_html( $message )
	);
}
add_action( 'admin_notices', 'fnc_content_model_publication_admin_notice' );

/* ==========================================================================
 * STATUTS À CHOIX UNIQUE — métabox radio générique
 *
 * Certaines taxonomies portent un STATUT UNIQUE : le PROFIL d'un intervenant
 * (`fnc_profil` : Officiel/Expert/Animateur) et le TYPE d'un partenaire
 * (`fnc_niveau_partenariat` : Institutionnel/Organisateur/Soutien/Sponsor).
 * La boîte de tags par défaut permettait d'en CUMULER plusieurs : ajouter un
 * nouveau statut n'enlevait pas l'ancien, l'objet gardait deux termes et le badge
 * continuait d'afficher le premier → « le statut ne change pas à l'écran ».
 * Cette métabox impose UN SEUL terme et REMPLACE le précédent. Fonctionne en
 * éditeur classique ET blocs (métabox legacy postée à l'enregistrement), sans JS.
 * ======================================================================== */

/** Taxonomies mono-valeur gérées + ordre d'affichage stable des options. */
if ( ! function_exists( 'fnc_cm_single_term_taxonomies' ) ) {
	function fnc_cm_single_term_taxonomies() {
		return array(
			'fnc_profil'             => array( 'official' => 0, 'expert' => 1, 'host' => 2 ),
			'fnc_niveau_partenariat' => array( 'institutionnel' => 0, 'organisateur' => 1, 'soutien' => 2, 'sponsor' => 3 ),
		);
	}
}

/** Rendu de la métabox radio (choix unique). $box['args']['taxonomy'] fourni par WP. */
if ( ! function_exists( 'fnc_cm_single_term_meta_box' ) ) {
	function fnc_cm_single_term_meta_box( $post, $box ) {
		$taxonomy = isset( $box['args']['taxonomy'] ) ? (string) $box['args']['taxonomy'] : '';
		if ( '' === $taxonomy ) {
			return;
		}
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			echo '<p>' . esc_html__( 'Aucune option disponible.', 'fnc-content-model' ) . '</p>';
			return;
		}
		$orders = fnc_cm_single_term_taxonomies();
		$order  = isset( $orders[ $taxonomy ] ) ? $orders[ $taxonomy ] : array();
		usort(
			$terms,
			static function ( $a, $b ) use ( $order ) {
				$oa = isset( $order[ $a->slug ] ) ? $order[ $a->slug ] : 99;
				$ob = isset( $order[ $b->slug ] ) ? $order[ $b->slug ] : 99;
				return ( $oa === $ob ) ? strcmp( $a->name, $b->name ) : ( $oa <=> $ob );
			}
		);
		$assigned = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
		$current  = ( ! is_wp_error( $assigned ) && ! empty( $assigned ) ) ? (int) $assigned[0] : 0;
		wp_nonce_field( 'fnc_cm_single_term_' . $taxonomy, 'fnc_cm_single_term_nonce_' . $taxonomy );
		$field = 'fnc_cm_single_term_' . $taxonomy;
		echo '<p style="margin:0 0 10px;color:#50575e;">' . esc_html__( 'Choix unique (remplace le précédent).', 'fnc-content-model' ) . '</p>';
		echo '<ul style="margin:0;">';
		printf(
			'<li style="margin:0 0 6px;"><label><input type="radio" name="%s" value="0" %s> <em>%s</em></label></li>',
			esc_attr( $field ),
			checked( $current, 0, false ),
			esc_html__( 'Aucun', 'fnc-content-model' )
		);
		foreach ( $terms as $t ) {
			printf(
				'<li style="margin:0 0 6px;"><label><input type="radio" name="%s" value="%d" %s> %s</label></li>',
				esc_attr( $field ),
				(int) $t->term_id,
				checked( $current, (int) $t->term_id, false ),
				esc_html( $t->name )
			);
		}
		echo '</ul>';
	}
}

/** Sauvegarde : pour chaque taxonomie mono-valeur, REMPLACE par le terme choisi. */
if ( ! function_exists( 'fnc_cm_single_term_save' ) ) {
	function fnc_cm_single_term_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$post_type = get_post_type( $post_id );
		foreach ( array_keys( fnc_cm_single_term_taxonomies() ) as $taxonomy ) {
			$nonce_name = 'fnc_cm_single_term_nonce_' . $taxonomy;
			if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ $nonce_name ] ) ), 'fnc_cm_single_term_' . $taxonomy ) ) {
				continue;
			}
			if ( ! is_object_in_taxonomy( $post_type, $taxonomy ) ) {
				continue;
			}
			$field   = 'fnc_cm_single_term_' . $taxonomy;
			$term_id = isset( $_POST[ $field ] ) ? (int) $_POST[ $field ] : 0;
			// `false` en 4e argument = REMPLACE tous les termes existants (choix unique).
			wp_set_object_terms( $post_id, $term_id > 0 ? array( $term_id ) : array(), $taxonomy, false );
		}
	}
}
add_action( 'save_post', 'fnc_cm_single_term_save' );
