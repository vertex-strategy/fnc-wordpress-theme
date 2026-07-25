<?php
/**
 * Forum Numérique Congo — semis du jeu complet de données (types de contenu).
 *
 * Consomme tools/dataset.json (produit par build-dataset.mjs) et crée/actualise
 * les éditions, intervenants, sessions, partenaires et publications, avec leurs
 * relations. Idempotent : chaque enregistrement porte une clé stable
 * `_fnc_seed_legacy` ; rejouer le script met à jour au lieu de dupliquer.
 *
 * Exécution :  wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-dataset.php
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fnc_ds_log( $m ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $m );
	} else {
		echo $m . "\n";
	}
}

/** Langue par défaut (Polylang) posée sur un contenu créé par code. */
function fnc_ds_language( $post_id ) {
	if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post_language' ) && ! pll_get_post_language( $post_id ) ) {
		pll_set_post_language( $post_id, function_exists( 'pll_default_language' ) ? pll_default_language() : 'fr' );
	}
}

/** Retrouve un post par clé stable + type. */
function fnc_ds_find( $legacy, $post_type ) {
	$q = get_posts( array(
		'post_type'   => $post_type,
		'post_status' => 'any',
		'numberposts' => 1,
		'fields'      => 'ids',
		'meta_key'    => '_fnc_seed_legacy',
		'meta_value'  => $legacy,
	) );
	return ! empty( $q ) ? (int) $q[0] : 0;
}

/** Crée ou met à jour un post ; renvoie l'ID. */
function fnc_ds_upsert( $post_type, $legacy, $title, $content = '', $slug = '' ) {
	$id  = fnc_ds_find( $legacy, $post_type );
	$arr = array(
		'post_type'    => $post_type,
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
	);
	if ( $slug ) {
		$arr['post_name'] = $slug;
	}
	if ( $id ) {
		$arr['ID'] = $id;
		wp_update_post( $arr );
	} else {
		$id = (int) wp_insert_post( $arr );
		update_post_meta( $id, '_fnc_seed_legacy', $legacy );
	}
	fnc_ds_language( $id );
	return $id;
}

/** Garantit un terme (par slug) et renvoie son term_id. */
function fnc_ds_term( $taxonomy, $slug, $name ) {
	$t = get_term_by( 'slug', $slug, $taxonomy );
	if ( $t ) {
		return (int) $t->term_id;
	}
	$r = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
	return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
}

/** Écrit une liste de méta (en ignorant les valeurs vides sauf 0). */
function fnc_ds_meta( $id, $pairs ) {
	foreach ( $pairs as $k => $v ) {
		if ( '' === $v || null === $v ) {
			continue;
		}
		update_post_meta( $id, $k, $v );
	}
}

/* ====================================================================== */

$fnc_ds_file = __DIR__ . '/dataset.json';
if ( ! is_readable( $fnc_ds_file ) ) {
	fnc_ds_log( '⚠ dataset.json introuvable — lancez d’abord : npx tsx tools/build-dataset.mjs' );
	return;
}
$data = json_decode( file_get_contents( $fnc_ds_file ), true );
if ( ! is_array( $data ) ) {
	fnc_ds_log( '⚠ dataset.json illisible.' );
	return;
}

$PROFILS = array(
	'official' => 'Officiel',
	'expert'   => 'Expert',
	'host'     => 'Animateur',
);
$NIVEAUX = array(
	'institutionnel' => 'Institutionnel',
	'organisateur'   => 'Organisateur',
	'soutien'        => 'Soutien',
	'sponsor'        => 'Sponsor',
);

$ed_map = array();
$sp_map = array();

/* ---- 1. Éditions ---- */
fnc_ds_log( 'Éditions :' );
foreach ( $data['editions'] as $e ) {
	$id = fnc_ds_upsert( 'fnc_edition', $e['legacyId'], $e['title'], '', $e['slug'] );
	fnc_ds_meta( $id, array(
		'_fnc_edition_year'       => $e['year'],
		'_fnc_edition_status'     => $e['status'],
		'_fnc_edition_theme'      => $e['theme'],
		'_fnc_edition_start_date' => $e['startDate'],
		'_fnc_edition_end_date'   => $e['endDate'],
		'_fnc_edition_location'   => $e['location'],
	) );
	update_post_meta( $id, '_fnc_edition_active', (int) $e['active'] );
	$ed_map[ $e['legacyId'] ] = $id;
	fnc_ds_log( "  ✔ {$e['year']} (#$id" . ( $e['active'] ? ', active' : '' ) . ")" );
}

/* ---- 2. Intervenants ---- */
fnc_ds_log( 'Intervenants :' );
foreach ( $data['speakers'] as $s ) {
	// Le rôle (fonction) est aussi placé en contenu : il alimente la fiche détail
	// et reste disponible même si le gabarit change.
	$id = fnc_ds_upsert( 'fnc_intervenant', $s['legacyId'], $s['name'], $s['roleFr'] ? wpautop( $s['roleFr'] ) : '', $s['slug'] );
	fnc_ds_meta( $id, array(
		'_fnc_speaker_title'          => $s['title'],   // Civilité (Pr./Dr./Ing./M./Mme), préfixe du nom.
		'_fnc_speaker_role'           => $s['roleFr'],  // Fonction — affichée par « Les voix » et la fiche.
		'_fnc_speaker_org'            => $s['org'],
		'_fnc_speaker_country'        => $s['country'],
		'_fnc_speaker_protocol_order' => (int) $s['protocolOrder'],
	) );
	// Droit à l'image : fermé par défaut (portrait masqué → monogramme). À ouvrir
	// au cas par cas quand l'autorisation est réellement acquise.
	if ( '' === get_post_meta( $id, '_fnc_speaker_image_right', true ) ) {
		update_post_meta( $id, '_fnc_speaker_image_right', 'non_verifie' );
	}
	$term = fnc_ds_term( 'fnc_profil', $s['kind'], isset( $PROFILS[ $s['kind'] ] ) ? $PROFILS[ $s['kind'] ] : ucfirst( $s['kind'] ) );
	if ( $term ) {
		wp_set_object_terms( $id, array( $term ), 'fnc_profil', false );
	}
	$sp_map[ $s['legacyId'] ] = $id;
}
fnc_ds_log( '  ✔ ' . count( $sp_map ) . ' intervenants (droit à l’image fermé par défaut → monogrammes)' );

/* ---- 3. Sessions (relations) ---- */
fnc_ds_log( 'Sessions :' );
foreach ( $data['sessions'] as $s ) {
	$id = fnc_ds_upsert( 'fnc_session', $s['legacyId'], $s['titleFr'], '', $s['slug'] );
	$speaker_ids = array();
	foreach ( $s['speakerLegacyIds'] as $lg ) {
		if ( isset( $sp_map[ $lg ] ) ) {
			$speaker_ids[] = $sp_map[ $lg ];
		}
	}
	fnc_ds_meta( $id, array(
		'_fnc_session_type'  => $s['type'],
		'_fnc_session_jour'  => $s['jour'],
		'_fnc_session_start' => $s['start'],
		'_fnc_session_end'   => $s['end'],
		'_fnc_session_time'  => $s['time'],
		'_fnc_session_note'  => $s['note'],
	) );
	if ( isset( $ed_map[ $s['editionLegacy'] ] ) ) {
		update_post_meta( $id, '_fnc_session_edition', $ed_map[ $s['editionLegacy'] ] );
	}
	if ( $s['moderatorLegacy'] && isset( $sp_map[ $s['moderatorLegacy'] ] ) ) {
		update_post_meta( $id, '_fnc_session_moderator', $sp_map[ $s['moderatorLegacy'] ] );
	}
	update_post_meta( $id, '_fnc_session_speakers', $speaker_ids ); // single (tableau d'ID)
}
fnc_ds_log( '  ✔ ' . count( $data['sessions'] ) . ' sessions reliées (édition + modérateur + intervenants)' );

/* ---- 4. Partenaires ---- */
fnc_ds_log( 'Partenaires :' );
foreach ( $data['partners'] as $p ) {
	$id = fnc_ds_upsert( 'fnc_partenaire', $p['legacyId'], $p['name'], $p['description'] ? wpautop( $p['description'] ) : '', $p['legacyId'] );
	if ( $p['website'] ) {
		update_post_meta( $id, '_fnc_partenaire_site', esc_url_raw( $p['website'] ) );
	}
	$slug = sanitize_title( $p['type'] );
	$term = fnc_ds_term( 'fnc_niveau_partenariat', $slug, isset( $NIVEAUX[ $slug ] ) ? $NIVEAUX[ $slug ] : ucfirst( $p['type'] ) );
	if ( $term ) {
		wp_set_object_terms( $id, array( $term ), 'fnc_niveau_partenariat', false );
	}
}
fnc_ds_log( '  ✔ ' . count( $data['partners'] ) . ' partenaires' );

/* ---- 5. Publications ---- */
fnc_ds_log( 'Publications :' );
foreach ( $data['publications'] as $p ) {
	$id = fnc_ds_upsert( 'fnc_publication', $p['legacyId'], $p['title'], $p['description'] ? wpautop( $p['description'] ) : '', $p['legacyId'] );
	fnc_ds_meta( $id, array(
		'_fnc_publication_type'      => $p['type'],
		'_fnc_publication_media_url' => $p['url'] ? esc_url_raw( $p['url'] ) : '',
	) );
	if ( $p['editionLegacy'] && isset( $ed_map[ $p['editionLegacy'] ] ) ) {
		update_post_meta( $id, '_fnc_publication_edition', $ed_map[ $p['editionLegacy'] ] );
	}
}
fnc_ds_log( '  ✔ ' . count( $data['publications'] ) . ' publications' );

fnc_ds_log( 'Terminé.' );
