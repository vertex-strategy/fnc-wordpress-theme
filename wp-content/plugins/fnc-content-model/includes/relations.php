<?php
/**
 * Relations entre custom post types + champs metier propres a chaque
 * collection, reflechis depuis le vrai schema Payload CMS du site officiel
 * (collections Editions/Sessions/Speakers/Partners/Publications lues dans
 * forum-numerique-congo/src/payload/collections/*.ts) plutot que depuis la
 * seule maquette statique ou l'observation du rendu.
 *
 * Choix d'implementation inchange (ADR-007, Decision 2, "zero dependance
 * tierce") : tout est stocke en post meta natif (register_post_meta +
 * add_meta_box), aucun plugin de champs. Les relations entite-a-entite
 * restent des ID (ou tableaux d'ID) plutot que des taxonomies, pour la
 * raison deja documentee : `intervenant` et `edition` existent deja comme
 * post types, les referencer par ID evite toute duplication.
 *
 * Cette passe (reconciliation du modele de contenu, cf. exploration du code
 * reel demandee par le Decideur) est volontairement ADDITIVE : les champs
 * deja lus par les gabarits actuels du theme (_fnc_session_time,
 * _fnc_session_room, _fnc_session_jour, _fnc_edition_active,
 * _fnc_partenaire_site, _fnc_partenaire_editions) restent en place et
 * continuent de fonctionner sans modification des gabarits. Les nouveaux
 * champs ci-dessous (type de session, moderateur, objectifs, statut
 * d'edition, theme/dates/lieu, titre/organisation/pays/rang protocolaire des
 * intervenants, type de publication, participations partenaires avec
 * niveau) ne sont pas encore consommes par les gabarits — ce sera l'objet
 * d'une passe suivante explicitement distincte (mise a jour des templates).
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

// --- Intervenants ---------------------------------------------------------
const FNC_META_SPEAKER_TITLE          = '_fnc_speaker_title';
const FNC_META_SPEAKER_ORG            = '_fnc_speaker_org';
const FNC_META_SPEAKER_COUNTRY        = '_fnc_speaker_country';
const FNC_META_SPEAKER_PROTOCOL_ORDER = '_fnc_speaker_protocol_order';
const FNC_META_SPEAKER_LINKS          = '_fnc_speaker_links';

// --- Partenaires ------------------------------------------------------
const FNC_META_PARTENAIRE_SITE           = '_fnc_partenaire_site';
const FNC_META_PARTENAIRE_EDITIONS       = '_fnc_partenaire_editions';
const FNC_META_PARTENAIRE_PARTICIPATIONS = '_fnc_partenaire_participations';

/**
 * Listes de valeurs fixes, reprises telles quelles des champs `select` des
 * collections Payload correspondantes (voir les fichiers cites en tete de
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

	// Intervenants.
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_TITLE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_ORG, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_COUNTRY, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_post_meta( 'fnc_intervenant', FNC_META_SPEAKER_PROTOCOL_ORDER, array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
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

function fnc_content_model_render_publication_meta_box( $post ) {
	wp_nonce_field( 'fnc_publication_relations_save', 'fnc_publication_relations_nonce' );

	$edition_id = get_post_meta( $post->ID, FNC_META_PUBLICATION_EDITION, true );
	$type       = get_post_meta( $post->ID, FNC_META_PUBLICATION_TYPE, true );
	$media_url  = get_post_meta( $post->ID, FNC_META_PUBLICATION_MEDIA_URL, true );

	echo '<p><label for="fnc_publication_type"><strong>' . esc_html__( 'Type', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_select( 'fnc_publication_type', fnc_content_model_publication_types(), $type, __( '— À préciser —', 'fnc-content-model' ) );
	echo '<p class="description">' . esc_html__( 'Champ distinct de la catégorie (fnc_categorie) — le type qualifie la nature du document (rapport, vidéo…), la catégorie le classe thématiquement.', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_publication_media_url"><strong>' . esc_html__( 'URL média (vidéo/interview)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="url" id="fnc_publication_media_url" name="fnc_publication_media_url" value="%s" placeholder="https://" style="width:100%%;" /></p>', esc_attr( $media_url ) );

	echo '<p style="margin-top:16px;"><label for="fnc_publication_edition">' . esc_html__( 'Édition liée (optionnel)', 'fnc-content-model' ) . '</label></p>';
	fnc_content_model_render_edition_select( 'fnc_publication_edition', $edition_id );
}

function fnc_content_model_render_speaker_meta_box( $post ) {
	wp_nonce_field( 'fnc_speaker_details_save', 'fnc_speaker_details_nonce' );

	$title          = get_post_meta( $post->ID, FNC_META_SPEAKER_TITLE, true );
	$org            = get_post_meta( $post->ID, FNC_META_SPEAKER_ORG, true );
	$country        = get_post_meta( $post->ID, FNC_META_SPEAKER_COUNTRY, true );
	$protocol_order = get_post_meta( $post->ID, FNC_META_SPEAKER_PROTOCOL_ORDER, true );
	$links          = get_post_meta( $post->ID, FNC_META_SPEAKER_LINKS, true );
	$links          = is_array( $links )
		? implode(
			"\n",
			array_map(
				static function ( $link ) {
					return trim( ( $link['label'] ?? '' ) . '|' . ( $link['url'] ?? '' ), '|' );
				},
				$links
			)
		)
		: '';

	echo '<p><label for="fnc_speaker_title"><strong>' . esc_html__( 'Civilité', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_title" name="fnc_speaker_title" value="%s" placeholder="Pr., Dr., M., Mme…" style="width:100%%;" /></p>', esc_attr( $title ) );

	echo '<p><label for="fnc_speaker_org"><strong>' . esc_html__( 'Organisation', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_org" name="fnc_speaker_org" value="%s" style="width:100%%;" /></p>', esc_attr( $org ) );

	echo '<p><label for="fnc_speaker_country"><strong>' . esc_html__( 'Pays', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="text" id="fnc_speaker_country" name="fnc_speaker_country" value="%s" placeholder="France / États-Unis" style="width:100%%;" /></p>', esc_attr( $country ) );
	echo '<p class="description">' . esc_html__( 'Champ texte libre (peut cumuler plusieurs pays séparés par « / »), distinct de la taxonomie Profils utilisée pour le filtre par nature d’intervenant.', 'fnc-content-model' ) . '</p>';

	echo '<p><label for="fnc_speaker_protocol_order"><strong>' . esc_html__( 'Rang protocolaire', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<input type="number" id="fnc_speaker_protocol_order" name="fnc_speaker_protocol_order" value="%s" style="width:100%%;" /></p>', esc_attr( $protocol_order ) );

	echo '<p><label for="fnc_speaker_links"><strong>' . esc_html__( 'Liens (un par ligne, format « Libellé|URL »)', 'fnc-content-model' ) . '</strong></label><br />';
	printf( '<textarea id="fnc_speaker_links" name="fnc_speaker_links" rows="3" style="width:100%%;">%s</textarea></p>', esc_textarea( $links ) );
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
				update_post_meta( $other_id, FNC_META_EDITION_ACTIVE, '' );
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

	if ( 'fnc_intervenant' === $post->post_type
		&& isset( $_POST['fnc_speaker_details_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_speaker_details_nonce'] ) ), 'fnc_speaker_details_save' )
		&& current_user_can( 'edit_post', $post_id )
	) {
		update_post_meta( $post_id, FNC_META_SPEAKER_TITLE, isset( $_POST['fnc_speaker_title'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_title'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_ORG, isset( $_POST['fnc_speaker_org'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_org'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_COUNTRY, isset( $_POST['fnc_speaker_country'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_speaker_country'] ) ) : '' );
		update_post_meta( $post_id, FNC_META_SPEAKER_PROTOCOL_ORDER, isset( $_POST['fnc_speaker_protocol_order'] ) ? absint( $_POST['fnc_speaker_protocol_order'] ) : '' );

		$links_raw = isset( $_POST['fnc_speaker_links'] ) ? (string) wp_unslash( $_POST['fnc_speaker_links'] ) : '';
		$links     = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $links_raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$links[] = array(
				'label' => sanitize_text_field( $parts[0] ?? '' ),
				'url'   => esc_url_raw( $parts[1] ?? '' ),
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
