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

/** Langue par défaut résolue (repli « fr » si Polylang absent). */
function fnc_ds_def_lang() {
	return function_exists( 'pll_default_language' ) ? pll_default_language() : 'fr';
}

/**
 * Crée/actualise la TRADUCTION EN d'un post FR et la lie via Polylang.
 * Clé stable propre « {legacy}::en » (idempotent). Renvoie l'ID EN, ou 0 si
 * Polylang est absent (le seed reste alors purement FR, sans erreur).
 */
function fnc_ds_upsert_en( $post_type, $fr_id, $legacy, $title, $content = '', $slug = '' ) {
	if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return 0;
	}
	$en_legacy = $legacy . '::en';
	$en_id     = fnc_ds_find( $en_legacy, $post_type );
	$arr = array(
		'post_type'    => $post_type,
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
	);
	if ( $slug ) {
		$arr['post_name'] = $slug . '-en';
	}
	if ( $en_id ) {
		$arr['ID'] = $en_id;
		wp_update_post( $arr );
	} else {
		$en_id = (int) wp_insert_post( $arr );
		update_post_meta( $en_id, '_fnc_seed_legacy', $en_legacy );
	}
	pll_set_post_language( $en_id, 'en' );
	pll_set_post_language( $fr_id, fnc_ds_def_lang() );
	pll_save_post_translations( array( fnc_ds_def_lang() => $fr_id, 'en' => $en_id ) );
	return $en_id;
}

/** Copie une liste de méta (données partagées) du post FR vers le post EN. */
function fnc_ds_copy_meta( $fr_id, $en_id, array $keys ) {
	if ( ! $en_id ) {
		return;
	}
	foreach ( $keys as $k ) {
		update_post_meta( $en_id, $k, get_post_meta( $fr_id, $k, true ) );
	}
}

/** Copie l'affectation d'une taxonomie du post FR vers le post EN. */
function fnc_ds_copy_terms( $fr_id, $en_id, $taxonomy ) {
	if ( ! $en_id ) {
		return;
	}
	$ids = wp_get_object_terms( $fr_id, $taxonomy, array( 'fields' => 'ids' ) );
	if ( ! is_wp_error( $ids ) ) {
		wp_set_object_terms( $en_id, array_map( 'intval', $ids ), $taxonomy, false );
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

/**
 * Importe la photo officielle d'un intervenant (assets/images/speakers/{legacy}.ext)
 * comme image mise en avant, et renvoie true si une photo a ete posee. Idempotent
 * (attachement reutilise via la meta _fnc_ds_photo).
 */
function fnc_ds_speaker_photo( $post_id, $legacy ) {
	$dir   = get_template_directory() . '/assets/images/speakers/';
	$found = '';
	foreach ( array( 'jpeg', 'jpg', 'png', 'webp' ) as $ext ) {
		if ( is_readable( $dir . $legacy . '.' . $ext ) ) {
			$found = $dir . $legacy . '.' . $ext;
			break;
		}
	}
	if ( '' === $found ) {
		return false;
	}
	$existing = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_fnc_ds_photo',
		'meta_value'  => $legacy,
		'fields'      => 'ids',
		'numberposts' => 1,
	) );
	if ( ! empty( $existing ) ) {
		$att = (int) $existing[0];
	} else {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload = wp_upload_bits( basename( $found ), null, file_get_contents( $found ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}
		$type = wp_check_filetype( $upload['file'] );
		$att  = wp_insert_attachment( array(
			'post_mime_type' => $type['type'],
			'post_title'     => get_the_title( $post_id ),
			'post_status'    => 'inherit',
		), $upload['file'], $post_id );
		if ( is_wp_error( $att ) || ! $att ) {
			return false;
		}
		wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $upload['file'] ) );
		update_post_meta( $att, '_fnc_ds_photo', $legacy );
	}
	set_post_thumbnail( $post_id, $att );
	return true;
}

/**
 * Importe le logo d'un partenaire (assets/images/partners/{legacy}.ext) comme
 * image mise en avant. Idempotent (attachement reutilise via _fnc_ds_logo).
 */
function fnc_ds_partner_logo( $post_id, $legacy ) {
	$dir   = get_template_directory() . '/assets/images/partners/';
	$found = '';
	foreach ( array( 'png', 'jpeg', 'jpg', 'webp', 'svg' ) as $ext ) {
		if ( is_readable( $dir . $legacy . '.' . $ext ) ) {
			$found = $dir . $legacy . '.' . $ext;
			break;
		}
	}
	if ( '' === $found ) {
		return false;
	}
	$existing = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_fnc_ds_logo',
		'meta_value'  => $legacy,
		'fields'      => 'ids',
		'numberposts' => 1,
	) );
	if ( ! empty( $existing ) ) {
		$att = (int) $existing[0];
	} else {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload = wp_upload_bits( basename( $found ), null, file_get_contents( $found ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}
		$type = wp_check_filetype( $upload['file'] );
		$att  = wp_insert_attachment( array(
			'post_mime_type' => $type['type'],
			'post_title'     => get_the_title( $post_id ),
			'post_status'    => 'inherit',
		), $upload['file'], $post_id );
		if ( is_wp_error( $att ) || ! $att ) {
			return false;
		}
		wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $upload['file'] ) );
		update_post_meta( $att, '_fnc_ds_logo', $legacy );
	}
	set_post_thumbnail( $post_id, $att );
	return true;
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

$ed_map    = array();
$sp_map    = array();
$ed_map_en = array(); // legacyId -> ID de la traduction EN (relations EN)
$sp_map_en = array();

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

	// Traduction EN : le thème (themeEn) est traduit ; statut/année/dates/lieu/
	// actif sont partagés. Titre EN : on remplace la partie « thème » quand un
	// themeEn existe (l'édition 2027 a un titre déjà neutre).
	$theme_en    = ! empty( $e['themeEn'] ) ? $e['themeEn'] : $e['theme'];
	$title_en    = $e['title'];
	if ( ! empty( $e['themeEn'] ) && ! empty( $e['theme'] ) && false !== strpos( $e['title'], $e['theme'] ) ) {
		$title_en = str_replace( $e['theme'], $e['themeEn'], $e['title'] );
	}
	$en = fnc_ds_upsert_en( 'fnc_edition', $id, $e['legacyId'], $title_en, '', $e['slug'] );
	if ( $en ) {
		fnc_ds_copy_meta( $id, $en, array( '_fnc_edition_year', '_fnc_edition_status', '_fnc_edition_start_date', '_fnc_edition_end_date', '_fnc_edition_location', '_fnc_edition_active' ) );
		update_post_meta( $en, '_fnc_edition_theme', $theme_en );
		$ed_map_en[ $e['legacyId'] ] = $en;
	}
	fnc_ds_log( "  ✔ {$e['year']} (#$id" . ( $e['active'] ? ', active' : '' ) . ( $en ? " / EN #$en" : '' ) . ")" );
}

/* ---- 2. Intervenants ---- */
fnc_ds_log( 'Intervenants :' );
foreach ( $data['speakers'] as $s ) {
	// Le rôle (fonction) est aussi placé en contenu : il alimente la fiche détail
	// et reste disponible même si le gabarit change.
	$id = fnc_ds_upsert( 'fnc_intervenant', $s['legacyId'], $s['name'], $s['roleFr'] ? wpautop( $s['roleFr'] ) : '', $s['slug'] );
	// Civilité forcée (même vide) : c'est le préfixe du nom ; ne jamais y laisser
	// une ancienne valeur (sinon le rôle se retrouve collé au nom).
	update_post_meta( $id, '_fnc_speaker_title', $s['title'] );
	fnc_ds_meta( $id, array(
		'_fnc_speaker_role'           => $s['roleFr'],  // Fonction — affichée par « Les voix » et la fiche.
		'_fnc_speaker_org'            => $s['org'],
		'_fnc_speaker_country'        => $s['country'],
		'_fnc_speaker_protocol_order' => (int) $s['protocolOrder'],
	) );
	// Photo officielle (déjà publiée sur le site public) : import + droit exercé.
	// Sinon droit fermé par défaut → monogramme (« Photo à venir »).
	if ( fnc_ds_speaker_photo( $id, $s['legacyId'] ) ) {
		update_post_meta( $id, '_fnc_speaker_image_right', 'obtenu' );
	} elseif ( '' === get_post_meta( $id, '_fnc_speaker_image_right', true ) ) {
		update_post_meta( $id, '_fnc_speaker_image_right', 'non_verifie' );
	}
	$term = fnc_ds_term( 'fnc_profil', $s['kind'], isset( $PROFILS[ $s['kind'] ] ) ? $PROFILS[ $s['kind'] ] : ucfirst( $s['kind'] ) );
	if ( $term ) {
		wp_set_object_terms( $id, array( $term ), 'fnc_profil', false );
	}
	$sp_map[ $s['legacyId'] ] = $id;

	// Traduction EN : nom identique (patronyme), rôle traduit (roleEn) en contenu
	// ET en méta. Civilité, organisation, pays, ordres, droit à l'image et photo
	// (miniature) sont partagés. Profil (taxo) recopié.
	$en = fnc_ds_upsert_en( 'fnc_intervenant', $id, $s['legacyId'], $s['name'], ! empty( $s['roleEn'] ) ? wpautop( $s['roleEn'] ) : '', $s['slug'] );
	if ( $en ) {
		update_post_meta( $en, '_fnc_speaker_title', $s['title'] );
		update_post_meta( $en, '_fnc_speaker_role', ! empty( $s['roleEn'] ) ? $s['roleEn'] : '' );
		fnc_ds_copy_meta( $id, $en, array( '_fnc_speaker_org', '_fnc_speaker_country', '_fnc_speaker_protocol_order', '_fnc_speaker_sort_index', '_fnc_speaker_home_featured', '_fnc_speaker_home_featured_order', '_fnc_speaker_image_right', '_fnc_speaker_image_expires', '_thumbnail_id' ) );
		fnc_ds_copy_terms( $id, $en, 'fnc_profil' );
		$sp_map_en[ $s['legacyId'] ] = $en;
	}
}
fnc_ds_log( '  ✔ ' . count( $sp_map ) . ' intervenants (' . count( $sp_map_en ) . ' traductions EN ; droit à l’image fermé par défaut → monogrammes)' );

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
		'_fnc_session_jour'  => (int) $s['day'], // Jour 1/2/3 (entier) — le libellé « Jour N » est composé à l'affichage.
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

	// Traduction EN : titre traduit (titleEn) ; type/jour/horaires/note partagés ;
	// RELATIONS remappées vers les pendants EN (édition, modérateur, intervenants)
	// pour que le programme EN reste cohérent (participants, comptages).
	$en = fnc_ds_upsert_en( 'fnc_session', $id, $s['legacyId'], ! empty( $s['titleEn'] ) ? $s['titleEn'] : $s['titleFr'], '', $s['slug'] );
	if ( $en ) {
		fnc_ds_copy_meta( $id, $en, array( '_fnc_session_type', '_fnc_session_jour', '_fnc_session_start', '_fnc_session_end', '_fnc_session_time', '_fnc_session_note' ) );
		if ( isset( $ed_map_en[ $s['editionLegacy'] ] ) ) {
			update_post_meta( $en, '_fnc_session_edition', $ed_map_en[ $s['editionLegacy'] ] );
		}
		if ( $s['moderatorLegacy'] && isset( $sp_map_en[ $s['moderatorLegacy'] ] ) ) {
			update_post_meta( $en, '_fnc_session_moderator', $sp_map_en[ $s['moderatorLegacy'] ] );
		}
		$en_speaker_ids = array();
		foreach ( $s['speakerLegacyIds'] as $lg ) {
			if ( isset( $sp_map_en[ $lg ] ) ) {
				$en_speaker_ids[] = $sp_map_en[ $lg ];
			}
		}
		update_post_meta( $en, '_fnc_session_speakers', $en_speaker_ids );
	}
}
fnc_ds_log( '  ✔ ' . count( $data['sessions'] ) . ' sessions reliées (édition + modérateur + intervenants), traductions EN incluses' );

/* ---- 4. Partenaires ---- */
fnc_ds_log( 'Partenaires :' );
// Ordre d'affichage des logos (accueil M6 + page Partenaires). Décision MOA :
// les 3 logos de tête = 2 organisateurs (GUOT, Grinso) + le sponsor officiel
// (SNPC), donc sortIndex 0/1/2. Le reste suit l'ordre du dataset. Éditable
// ensuite dans l'admin (champ « Ordre d'affichage » de la fiche partenaire).
$PARTNER_SORT = array( 'guot' => 0, 'grinso' => 1, 'snpc' => 2 );
$fnc_p_next   = count( $PARTNER_SORT );
foreach ( $data['partners'] as $p ) {
	$id = fnc_ds_upsert( 'fnc_partenaire', $p['legacyId'], $p['name'], $p['description'] ? wpautop( $p['description'] ) : '', $p['legacyId'] );
	fnc_ds_partner_logo( $id, $p['legacyId'] ); // Logo officiel (si disponible dans le thème).
	if ( $p['website'] ) {
		update_post_meta( $id, '_fnc_partenaire_site', esc_url_raw( $p['website'] ) );
	}
	$fnc_sort = isset( $PARTNER_SORT[ $p['legacyId'] ] ) ? $PARTNER_SORT[ $p['legacyId'] ] : $fnc_p_next++;
	update_post_meta( $id, '_fnc_partenaire_sort_index', (int) $fnc_sort );
	$slug = sanitize_title( $p['type'] );
	$term = fnc_ds_term( 'fnc_niveau_partenariat', $slug, isset( $NIVEAUX[ $slug ] ) ? $NIVEAUX[ $slug ] : ucfirst( $p['type'] ) );
	if ( $term ) {
		wp_set_object_terms( $id, array( $term ), 'fnc_niveau_partenariat', false );
	}

	// Traduction EN : le dataset n'a pas de nom/description EN de partenaire (nom
	// propre + logo partagés). On crée le pendant EN pour que le bloc « communauté »
	// et la page Partenaires s'affichent en EN ; site, ordre, logo et niveau copiés.
	$en = fnc_ds_upsert_en( 'fnc_partenaire', $id, $p['legacyId'], $p['name'], $p['description'] ? wpautop( $p['description'] ) : '' );
	if ( $en ) {
		fnc_ds_copy_meta( $id, $en, array( '_fnc_partenaire_site', '_fnc_partenaire_sort_index', '_thumbnail_id' ) );
		fnc_ds_copy_terms( $id, $en, 'fnc_niveau_partenariat' );
	}
}
fnc_ds_log( '  ✔ ' . count( $data['partners'] ) . ' partenaires (traductions EN incluses)' );

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

	// Traduction EN : le dataset n'a pas de titre EN de publication ; on crée le
	// pendant EN (titre identique) pour l'affichage EN ; type/média copiés, édition
	// remappée vers son pendant EN.
	$en = fnc_ds_upsert_en( 'fnc_publication', $id, $p['legacyId'], $p['title'], $p['description'] ? wpautop( $p['description'] ) : '' );
	if ( $en ) {
		fnc_ds_copy_meta( $id, $en, array( '_fnc_publication_type', '_fnc_publication_media_url' ) );
		if ( $p['editionLegacy'] && isset( $ed_map_en[ $p['editionLegacy'] ] ) ) {
			update_post_meta( $en, '_fnc_publication_edition', $ed_map_en[ $p['editionLegacy'] ] );
		}
	}
}
fnc_ds_log( '  ✔ ' . count( $data['publications'] ) . ' publications (traductions EN incluses)' );

fnc_ds_log( 'Terminé.' );
