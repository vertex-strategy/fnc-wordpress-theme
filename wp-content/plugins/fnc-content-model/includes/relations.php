<?php
/**
 * Relations entre custom post types.
 *
 * Choix d'implementation : les relations entite-a-entite (ex. une session
 * qui appartient a une edition, une session qui a plusieurs intervenants)
 * sont stockees en post meta (ID ou tableau d'ID), pas via une taxonomie.
 * Une taxonomie duplique les entites en un vocabulaire parallele a
 * synchroniser manuellement ; ici, `intervenant` et `edition` existent deja
 * comme post types, donc les referencer par ID evite toute duplication.
 *
 * Cela reste dans l'esprit "zero dependance tierce" de la Decision 2 de
 * l'ADR-007 : pas de plugin de champs, uniquement des meta boxes natives.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FNC_META_SESSION_EDITION     = '_fnc_session_edition';
const FNC_META_SESSION_SPEAKERS    = '_fnc_session_speakers';
const FNC_META_PUBLICATION_EDITION = '_fnc_publication_edition';

/**
 * Expose les meta en REST (compatibilite editeur de blocs / Polylang).
 */
function fnc_content_model_register_meta() {
	register_post_meta(
		'fnc_session',
		FNC_META_SESSION_EDITION,
		array(
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
		)
	);

	register_post_meta(
		'fnc_session',
		FNC_META_SESSION_SPEAKERS,
		array(
			'type'         => 'array',
			'single'       => true,
			'show_in_rest' => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array( 'type' => 'integer' ),
				),
			),
		)
	);

	register_post_meta(
		'fnc_publication',
		FNC_META_PUBLICATION_EDITION,
		array(
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'fnc_content_model_register_meta' );

/**
 * Meta boxes d'administration.
 */
function fnc_content_model_add_meta_boxes() {
	add_meta_box(
		'fnc_session_relations',
		__( 'Édition et intervenants', 'fnc-content-model' ),
		'fnc_content_model_render_session_meta_box',
		'fnc_session',
		'side'
	);

	add_meta_box(
		'fnc_publication_relations',
		__( 'Édition liée', 'fnc-content-model' ),
		'fnc_content_model_render_publication_meta_box',
		'fnc_publication',
		'side'
	);
}
add_action( 'add_meta_boxes', 'fnc_content_model_add_meta_boxes' );

/**
 * Rendu d'un <select> d'editions publiees.
 */
function fnc_content_model_render_edition_select( $field_name, $selected_id ) {
	$editions = get_posts(
		array(
			'post_type'      => 'fnc_edition',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

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

function fnc_content_model_render_session_meta_box( $post ) {
	wp_nonce_field( 'fnc_session_relations_save', 'fnc_session_relations_nonce' );

	$edition_id  = get_post_meta( $post->ID, FNC_META_SESSION_EDITION, true );
	$speaker_ids = get_post_meta( $post->ID, FNC_META_SESSION_SPEAKERS, true );
	$speaker_ids = is_array( $speaker_ids ) ? array_map( 'intval', $speaker_ids ) : array();

	echo '<p><label for="fnc_session_edition"><strong>' . esc_html__( 'Édition', 'fnc-content-model' ) . '</strong></label></p>';
	fnc_content_model_render_edition_select( 'fnc_session_edition', $edition_id );

	echo '<p style="margin-top:16px;"><strong>' . esc_html__( 'Intervenants', 'fnc-content-model' ) . '</strong></p>';

	$speakers = get_posts(
		array(
			'post_type'      => 'fnc_intervenant',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

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

function fnc_content_model_render_publication_meta_box( $post ) {
	wp_nonce_field( 'fnc_publication_relations_save', 'fnc_publication_relations_nonce' );

	$edition_id = get_post_meta( $post->ID, FNC_META_PUBLICATION_EDITION, true );

	echo '<p><label for="fnc_publication_edition">' . esc_html__( 'Édition liée (optionnel)', 'fnc-content-model' ) . '</label></p>';
	fnc_content_model_render_edition_select( 'fnc_publication_edition', $edition_id );
}

/**
 * Sauvegarde des relations, avec verification de nonce et de capacite.
 */
function fnc_content_model_save_relations( $post_id, $post ) {
	if ( ! isset( $_POST['fnc_session_relations_nonce'] ) && ! isset( $_POST['fnc_publication_relations_nonce'] ) ) {
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

		$speaker_ids = isset( $_POST['fnc_session_speakers'] ) && is_array( $_POST['fnc_session_speakers'] )
			? array_map( 'absint', wp_unslash( $_POST['fnc_session_speakers'] ) )
			: array();
		update_post_meta( $post_id, FNC_META_SESSION_SPEAKERS, $speaker_ids );
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
	}
}
add_action( 'save_post', 'fnc_content_model_save_relations', 10, 2 );
