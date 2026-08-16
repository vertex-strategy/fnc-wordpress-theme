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
	// Journal tamponné : consultable par l'importateur d'administration
	// (fnc_ds_run_seed le renvoie). En CLI on continue d'afficher en direct.
	if ( ! isset( $GLOBALS['fnc_ds_log_buffer'] ) || ! is_array( $GLOBALS['fnc_ds_log_buffer'] ) ) {
		$GLOBALS['fnc_ds_log_buffer'] = array();
	}
	$GLOBALS['fnc_ds_log_buffer'][] = $m;

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $m );
	} elseif ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! is_admin() ) {
		echo esc_html( $m ) . "\n";
	}
}

/** Langue par défaut (Polylang) posée sur un contenu créé par code. */
function fnc_ds_language( $post_id ) {
	if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post_language' ) && ! pll_get_post_language( $post_id ) ) {
		pll_set_post_language( $post_id, fnc_ds_def_lang() );
	}
}

/**
 * Langue PRIMAIRE du contenu de démonstration.
 *
 * Le jeu de démo est rédigé en français (les traductions EN sont dérivées) : on
 * ancre donc la langue primaire sur « fr » DÈS QU'elle existe dans Polylang, quelle
 * que soit la langue « par défaut » choisie à l'installation. Sans cet ancrage, si
 * l'admin fixe l'anglais par défaut, tout le contenu « FR » serait étiqueté « en » et
 * entrerait en collision avec ses propres traductions → pages vides selon la langue.
 * Repli : la vraie langue par défaut de Polylang (ou « fr » si Polylang est absent).
 */
function fnc_ds_def_lang() {
	if ( function_exists( 'pll_languages_list' ) ) {
		$langs = (array) pll_languages_list();
		if ( in_array( 'fr', $langs, true ) ) {
			return 'fr';
		}
	}
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

/**
 * Crée les PAGES éditoriales attendues par les gabarits et la navigation
 * (le-forum, contact, mot-du-président, pages légales…). Sans elles, sur une
 * installation neuve, les liens de menu retombent sur « # » (page absente) et
 * les gabarits page-{slug}.php ne s'appliquent pas.
 *
 * Idempotent : ADOPTE une page existante de même slug (pour ne pas dupliquer)
 * et la marque d'une clé de semis. Crée la traduction EN liée (même slug, requis
 * pour que WordPress applique le bon gabarit page-{slug}.php aussi en anglais).
 * Le gabarit se charge du contenu de démonstration ; les pages sont créées vides.
 */
function fnc_ds_ensure_pages() {
	// slug, titre FR, titre EN, slug du parent (pour /le-forum/mot-du-president).
	$defs = array(
		array( 'le-forum', 'Le Forum', 'The Forum', '' ),
		array( 'edition-en-cours', 'Édition en cours', 'Current edition', '' ),
		array( 'partenaires', 'Partenaires', 'Partners', '' ),
		array( 'contact', 'Contact', 'Contact', '' ),
		array( 'inscription', 'Inscription', 'Registration', '' ),
		array( 'informations-pratiques', 'Informations pratiques', 'Practical information', '' ),
		array( 'espace-presse', 'Espace presse', 'Press area', '' ),
		array( 'mot-du-president', 'Le mot du Président', 'The President’s address', 'le-forum' ),
		array( 'mentions-legales', 'Mentions légales', 'Legal notice', '' ),
		array( 'politique-confidentialite', 'Politique de confidentialité', 'Privacy policy', '' ),
		array( 'conditions-generales-utilisation', 'Conditions générales d’utilisation', 'Terms of use', '' ),
		array( 'declaration-accessibilite', 'Déclaration d’accessibilité', 'Accessibility statement', '' ),
	);
	$has_pll = function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' );

	// --- Passe 1 : pages FR (adoption des existantes par slug). ---
	$fr = array();
	foreach ( $defs as $d ) {
		list( $slug, $title_fr ) = $d;
		$legacy = 'page::' . $slug;
		$id     = fnc_ds_find( $legacy, 'page' );
		if ( ! $id ) {
			// Adopter une page existante de même slug (langue par défaut), quelle
			// que soit sa hiérarchie. get_page_by_path échouerait pour une page
			// ENFANT (ex. mot-du-président dont le chemin réel est
			// « le-forum/mot-du-president »), ce qui créerait un doublon « -2 ».
			$found = get_posts(
				array(
					'post_type'   => 'page',
					'name'        => $slug,
					'post_status' => 'any',
					'numberposts' => 1,
					'fields'      => 'ids',
					'lang'        => fnc_ds_def_lang(),
				)
			);
			if ( ! empty( $found ) ) {
				$id = (int) $found[0];
				update_post_meta( $id, '_fnc_seed_legacy', $legacy );
			}
		}
		if ( $id ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		} else {
			$id = (int) wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_title'  => $title_fr,
					'post_name'   => $slug,
					'post_status' => 'publish',
				)
			);
			update_post_meta( $id, '_fnc_seed_legacy', $legacy );
		}
		fnc_ds_language( $id ); // langue par défaut (fr).
		$fr[ $slug ] = $id;
	}
	// Parenté FR (mot-du-président sous le-forum -> /le-forum/mot-du-president/).
	foreach ( $defs as $d ) {
		if ( $d[3] && isset( $fr[ $d[3] ], $fr[ $d[0] ] ) ) {
			wp_update_post( array( 'ID' => $fr[ $d[0] ], 'post_parent' => $fr[ $d[3] ] ) );
		}
	}

	// --- Passe 2 : traductions EN (même slug, liées). ---
	if ( $has_pll ) {
		$en = array();
		foreach ( $defs as $d ) {
			list( $slug, , $title_en ) = $d;
			$legacy = 'page::' . $slug . '::en';
			$id     = fnc_ds_find( $legacy, 'page' );
			if ( $id ) {
				wp_update_post( array( 'ID' => $id, 'post_title' => $title_en, 'post_status' => 'publish' ) );
			} else {
				// Slug distinct « {slug}-en » : évite tout conflit Polylang (le même
				// slug dans deux langues déclenche une redirection 301 vers le FR).
				// Le bon gabarit est appliqué via le filtre template_include du thème
				// (page-{slug-FR}.php), donc le slug EN peut différer sans souci.
				$id = (int) wp_insert_post(
					array(
						'post_type'   => 'page',
						'post_title'  => $title_en,
						'post_name'   => $slug . '-en',
						'post_status' => 'publish',
					)
				);
				update_post_meta( $id, '_fnc_seed_legacy', $legacy );
			}
			pll_set_post_language( $id, 'en' );
			pll_set_post_language( $fr[ $slug ], fnc_ds_def_lang() );
			pll_save_post_translations( array( fnc_ds_def_lang() => $fr[ $slug ], 'en' => $id ) );
			$en[ $slug ] = $id;
		}
		foreach ( $defs as $d ) {
			if ( $d[3] && isset( $en[ $d[3] ], $en[ $d[0] ] ) ) {
				wp_update_post( array( 'ID' => $en[ $d[0] ], 'post_parent' => $en[ $d[3] ] ) );
			}
		}
	}

	fnc_ds_log( '  ✔ ' . count( $defs ) . ' pages éditoriales (FR' . ( $has_pll ? ' + EN' : '' ) . ')' );
}

/**
 * Réglages du site : nom officiel (title-tag/SEO), slogan, coordonnées, réseaux
 * de démonstration et valeurs SEO par défaut. Sans ça, le <title> reste le nom
 * WordPress d'installation (souvent un intitulé technique) et la meta description
 * est absente. Ne remplit que les champs vides (n'écrase pas l'existant).
 */
/**
 * Importe (idempotent) un visuel du thème comme image de partage OpenGraph par
 * défaut et renvoie son ID d'attachement (0 si indisponible). Marqué `_fnc_ds_og`.
 *
 * @return int
 */
function fnc_ds_import_og_image() {
	$src = get_template_directory() . '/assets/images/le-forum.png';
	if ( ! is_readable( $src ) ) {
		return 0;
	}
	$existing = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_fnc_ds_og',
		'meta_value'  => '1',
		'fields'      => 'ids',
		'numberposts' => 1,
	) );
	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$upload = wp_upload_bits( 'og-forum-numerique-congo.png', null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$type = wp_check_filetype( $upload['file'] );
	$att  = wp_insert_attachment( array(
		'post_mime_type' => $type['type'],
		'post_title'     => 'Forum Numérique Congo — image de partage',
		'post_status'    => 'inherit',
	), $upload['file'] );
	if ( is_wp_error( $att ) || ! $att ) {
		return 0;
	}
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $upload['file'] ) );
	update_post_meta( $att, '_fnc_ds_og', '1' );
	return (int) $att;
}

function fnc_ds_ensure_settings() {
	$name    = 'Forum Numérique Congo';
	$tagline = 'L’espace où l’Afrique centrale décide de son avenir numérique.';

	// Nom du site WordPress (utilisé par le <title> via title-tag, les e-mails…).
	if ( in_array( get_option( 'blogname' ), array( '', 'FNC WordPress Theme - Dev', 'FNC WordPress Theme – Dev' ), true ) ) {
		update_option( 'blogname', $name );
	}
	if ( '' === (string) get_option( 'blogdescription' ) || 'Just another WordPress site' === get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', $tagline );
	}

	$s = get_option( 'fnc_settings', array() );
	if ( ! is_array( $s ) ) {
		$s = array();
	}
	$set = function ( $key, $value ) use ( &$s ) {
		if ( empty( $s[ $key ] ) ) {
			$s[ $key ] = $value;
		}
	};
	$set( 'officialName', $name );
	$set( 'description', $tagline );
	$set( 'email', 'contact@forum-numerique-congo.cg' );
	$set( 'address', 'Brazzaville, République du Congo' );
	$set( 'phone', '+242 06 665 19 04' );
	$set( 'seo_default_description', 'Le Forum Numérique Congo réunit décideurs, experts et société civile autour de la souveraineté numérique de l’Afrique centrale, à Brazzaville.' );

	// Image de partage (OpenGraph) par défaut : importe un visuel du thème comme
	// média et le pose en réglage (clé interne « ogDefaultImage »). Sert au
	// standalone ET de repli avant configuration d'un plugin SEO. Idempotent.
	if ( empty( $s['ogDefaultImage'] ) ) {
		$fnc_og_id = fnc_ds_import_og_image();
		if ( $fnc_og_id ) {
			$s['ogDefaultImage'] = $fnc_og_id;
		}
	}
	$site = 'https://forumnumeriquecongo.net';
	$set(
		'social',
		array(
			array( 'platform' => 'linkedin', 'label' => 'LinkedIn', 'url' => $site ),
			array( 'platform' => 'facebook', 'label' => 'Facebook', 'url' => $site ),
			array( 'platform' => 'x', 'label' => 'X', 'url' => $site ),
			array( 'platform' => 'youtube', 'label' => 'YouTube', 'url' => $site ),
		)
	);
	update_option( 'fnc_settings', $s );
	fnc_ds_log( '  ✔ Réglages du site (nom « ' . $name .' », coordonnées, SEO par défaut)' );
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
	if ( $id ) {
		$arr['ID'] = $id;
		// post_name NON modifié à l'update : les slugs (URL) restent STABLES à
		// travers les re-seeds (#4c — pas de churn d'URL sur l'existant).
		wp_update_post( $arr );
	} else {
		if ( $slug ) {
			$arr['post_name'] = $slug;
		}
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

/**
 * Exécute le semis complet (idempotent) et renvoie le journal (tableau de
 * lignes). Appelable en CLI (wp eval-file) ET par l'importateur de démo
 * d'administration (inc/demo-import.php).
 *
 * @return array<int,string> Lignes de journal.
 */
function fnc_ds_run_seed() {
	$GLOBALS['fnc_ds_log_buffer'] = array();

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

	$fnc_ds_file = __DIR__ . '/dataset.json';
	if ( ! is_readable( $fnc_ds_file ) ) {
		fnc_ds_log( '⚠ dataset.json introuvable — lancez d’abord : npx tsx tools/build-dataset.mjs' );
		return $GLOBALS['fnc_ds_log_buffer'];
	}
	$data = json_decode( file_get_contents( $fnc_ds_file ), true );
	if ( ! is_array( $data ) ) {
		fnc_ds_log( '⚠ dataset.json illisible.' );
		return $GLOBALS['fnc_ds_log_buffer'];
	}

	$ed_map    = array();
$sp_map    = array();
$ed_map_en = array(); // legacyId -> ID de la traduction EN (relations EN)
$sp_map_en = array();

/* ---- 0. Réglages du site (nom officiel, coordonnées, SEO par défaut) ---- */
fnc_ds_log( 'Réglages du site :' );
fnc_ds_ensure_settings();

/* ---- 0b. Pages éditoriales (nav + gabarits page-{slug}.php) ---- */
fnc_ds_log( 'Pages éditoriales :' );
fnc_ds_ensure_pages();

/* ---- 1. Éditions ---- */
fnc_ds_log( 'Éditions :' );
foreach ( $data['editions'] as $e ) {
	// #4c : slug d'édition DÉRIVÉ DU TITRE (jamais préfixé par l'année ; parité
	// slugField('title') du Next). N'affecte que les NOUVELLES éditions (l'upsert
	// ne modifie plus le post_name des éditions déjà créées → URL stables).
	$id = fnc_ds_upsert( 'fnc_edition', $e['legacyId'], $e['title'], '', sanitize_title( $e['title'] ) );
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

/* ---- 3 bis. Voix mises en avant (carrousel « Les voix ») ----
 * On promeut quelques intervenants pour que la démo montre le carrousel « en
 * action ». Contrainte respectée par construction : on ne retient que des
 * PARTICIPANTS de l'édition ACTIVE (intervenant ou modérateur d'une de ses
 * sessions), sinon ils n'apparaîtraient pas. Ordre = rang protocolaire (les
 * plus hautes voix d'abord). Calculé depuis le dataset (aucune dépendance au
 * runtime), FR + traduction EN. Rejouable : on nettoie d'abord toute promotion. */
fnc_ds_log( 'Voix mises en avant :' );
$fnc_featured_max = 4;

// Édition active (legacyId) : celle marquée active dans le dataset.
$fnc_active_legacy = '';
foreach ( $data['editions'] as $e ) {
	if ( ! empty( $e['active'] ) ) {
		$fnc_active_legacy = $e['legacyId'];
		break;
	}
}

// Participants de l'édition active (legacyIds uniques), triés par rang protocolaire.
$fnc_proto = array();
foreach ( $data['speakers'] as $sp ) {
	$fnc_proto[ $sp['legacyId'] ] = isset( $sp['protocolOrder'] ) ? (int) $sp['protocolOrder'] : 9999;
}
$fnc_part_legacies = array();
if ( '' !== $fnc_active_legacy ) {
	foreach ( $data['sessions'] as $s ) {
		if ( ( $s['editionLegacy'] ?? '' ) !== $fnc_active_legacy ) {
			continue;
		}
		if ( ! empty( $s['moderatorLegacy'] ) ) {
			$fnc_part_legacies[] = $s['moderatorLegacy'];
		}
		foreach ( (array) ( $s['speakerLegacyIds'] ?? array() ) as $lg ) {
			$fnc_part_legacies[] = $lg;
		}
	}
}
$fnc_part_legacies = array_values( array_unique( $fnc_part_legacies ) );
usort( $fnc_part_legacies, static function ( $a, $b ) use ( $fnc_proto ) {
	return ( $fnc_proto[ $a ] ?? 9999 ) <=> ( $fnc_proto[ $b ] ?? 9999 );
} );

// Nettoyage (rejouable) : retirer toute promotion existante sur les intervenants seedés.
foreach ( $sp_map as $lg => $pid ) {
	delete_post_meta( $pid, '_fnc_speaker_home_featured' );
	delete_post_meta( $pid, '_fnc_speaker_home_featured_order' );
	if ( isset( $sp_map_en[ $lg ] ) ) {
		delete_post_meta( $sp_map_en[ $lg ], '_fnc_speaker_home_featured' );
		delete_post_meta( $sp_map_en[ $lg ], '_fnc_speaker_home_featured_order' );
	}
}

$fnc_featured_order = 1;
foreach ( array_slice( $fnc_part_legacies, 0, $fnc_featured_max ) as $lg ) {
	if ( isset( $sp_map[ $lg ] ) ) {
		update_post_meta( $sp_map[ $lg ], '_fnc_speaker_home_featured', 1 );
		update_post_meta( $sp_map[ $lg ], '_fnc_speaker_home_featured_order', $fnc_featured_order );
	}
	if ( isset( $sp_map_en[ $lg ] ) ) {
		update_post_meta( $sp_map_en[ $lg ], '_fnc_speaker_home_featured', 1 );
		update_post_meta( $sp_map_en[ $lg ], '_fnc_speaker_home_featured_order', $fnc_featured_order );
	}
	$fnc_featured_order++;
}
fnc_ds_log( '  ✔ ' . ( $fnc_featured_order - 1 ) . ' voix promues (participants de l’édition active, rang protocolaire).' );

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

	// #10 — rattachements partenaire↔édition PAR NIVEAU (participations). Chaque
	// entrée { edition: legacyId, niveau } est résolue vers l'ID d'édition FR via
	// $ed_map. On écrit TOUJOURS la méta (tableau vide si aucun rattachement) →
	// idempotent, et on retire un ancien rattachement supprimé du dataset. On tient
	// aussi à jour le repli plat _fnc_partenaire_editions (mêmes éditions).
	$fnc_part_fr = array();
	$fnc_eds_fr  = array();
	if ( ! empty( $p['participations'] ) && is_array( $p['participations'] ) ) {
		foreach ( $p['participations'] as $pp ) {
			$el = isset( $pp['edition'] ) ? (string) $pp['edition'] : '';
			if ( '' !== $el && isset( $ed_map[ $el ] ) ) {
				$fnc_part_fr[] = array( 'edition' => (int) $ed_map[ $el ], 'niveau' => sanitize_key( isset( $pp['niveau'] ) ? $pp['niveau'] : '' ) );
				$fnc_eds_fr[]  = (int) $ed_map[ $el ];
			}
		}
	}
	update_post_meta( $id, '_fnc_partenaire_participations', $fnc_part_fr );
	update_post_meta( $id, '_fnc_partenaire_editions', $fnc_eds_fr );

	// Traduction EN : le dataset n'a pas de nom/description EN de partenaire (nom
	// propre + logo partagés). On crée le pendant EN pour que le bloc « communauté »
	// et la page Partenaires s'affichent en EN ; site, ordre, logo et niveau copiés.
	$en = fnc_ds_upsert_en( 'fnc_partenaire', $id, $p['legacyId'], $p['name'], $p['description'] ? wpautop( $p['description'] ) : '' );
	if ( $en ) {
		fnc_ds_copy_meta( $id, $en, array( '_fnc_partenaire_site', '_fnc_partenaire_sort_index', '_thumbnail_id' ) );
		fnc_ds_copy_terms( $id, $en, 'fnc_niveau_partenariat' );
		// Participations EN : mêmes rattachements, mais pointant vers les ÉDITIONS EN
		// ($ed_map_en) — sinon la section « Partenaires de l'édition » de /en/ (qui
		// filtre sur l'ID de l'édition EN courante) ne les verrait pas.
		$fnc_part_en = array();
		$fnc_eds_en  = array();
		if ( ! empty( $p['participations'] ) && is_array( $p['participations'] ) ) {
			foreach ( $p['participations'] as $pp ) {
				$el = isset( $pp['edition'] ) ? (string) $pp['edition'] : '';
				if ( '' !== $el && isset( $ed_map_en[ $el ] ) ) {
					$fnc_part_en[] = array( 'edition' => (int) $ed_map_en[ $el ], 'niveau' => sanitize_key( isset( $pp['niveau'] ) ? $pp['niveau'] : '' ) );
					$fnc_eds_en[]  = (int) $ed_map_en[ $el ];
				}
			}
		}
		update_post_meta( $en, '_fnc_partenaire_participations', $fnc_part_en );
		update_post_meta( $en, '_fnc_partenaire_editions', $fnc_eds_en );
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

	/* ---- 6. Pages éditoriales composées en blocs (FR + EN) ---- */
	// Compose le-forum / mot-du-président / contact / inscription en blocs FNC, dans
	// LES DEUX langues (indépendamment de la langue par défaut). Sans cette étape,
	// l'importateur ne posait aucun contenu éditorial → pages « en préparation ».
	$fnc_ed_seed = __DIR__ . '/seed-content.php';
	if ( is_readable( $fnc_ed_seed ) ) {
		require_once $fnc_ed_seed;
		if ( function_exists( 'fnc_seed_run_editorial' ) ) {
			fnc_ds_log( 'Pages éditoriales (blocs, FR + EN) :' );
			foreach ( (array) fnc_seed_run_editorial( true ) as $line ) {
				fnc_ds_log( $line );
			}
		}
	} else {
		fnc_ds_log( '⚠ seed-content.php introuvable — pages éditoriales non composées.' );
	}

	fnc_ds_log( 'Terminé.' );

	return $GLOBALS['fnc_ds_log_buffer'];
}

/**
 * Retire tout le contenu de démonstration : les posts marqués par la clé de
 * semis (_fnc_seed_legacy), toutes langues confondues (FR + traductions EN), et
 * les médias importés par le semis (photos d'intervenants, logos de partenaires,
 * marqués _fnc_ds_photo / _fnc_ds_logo). Ne touche à AUCUN autre contenu ni
 * média téléversé par l'éditeur. Renvoie le journal.
 *
 * ⚠ Supprime aussi un contenu de démo qui aurait été édité, tant qu'il porte
 * encore la clé _fnc_seed_legacy. Opération définitive (suppression forcée).
 *
 * @return array<int,string> Lignes de journal.
 */
function fnc_ds_remove_seed() {
	$GLOBALS['fnc_ds_log_buffer'] = array();

	$types = array( 'fnc_edition', 'fnc_intervenant', 'fnc_session', 'fnc_partenaire', 'fnc_publication' );
	$posts = get_posts( array(
		'post_type'   => $types,
		'post_status' => 'any',
		'numberposts' => -1,
		'fields'      => 'ids',
		'lang'        => '', // toutes langues (Polylang)
		'meta_key'    => '_fnc_seed_legacy',
		'no_found_rows'=> true,
	) );
	$n = 0;
	foreach ( $posts as $pid ) {
		if ( wp_delete_post( $pid, true ) ) {
			$n++;
		}
	}
	fnc_ds_log( sprintf( '  ✔ %d contenus de démonstration supprimés (toutes langues)', $n ) );

	$atts = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'any',
		'numberposts' => -1,
		'fields'      => 'ids',
		'lang'        => '',
		'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'OR',
			array( 'key' => '_fnc_ds_photo', 'compare' => 'EXISTS' ),
			array( 'key' => '_fnc_ds_logo', 'compare' => 'EXISTS' ),
		),
		'no_found_rows'=> true,
	) );
	$m = 0;
	foreach ( $atts as $aid ) {
		if ( wp_delete_attachment( $aid, true ) ) {
			$m++;
		}
	}
	fnc_ds_log( sprintf( '  ✔ %d médias de démonstration supprimés', $m ) );
	fnc_ds_log( 'Nettoyage terminé.' );

	return $GLOBALS['fnc_ds_log_buffer'];
}

// Exécution directe en ligne de commande (wp eval-file), inchangée. En dehors
// de la CLI, l'inclusion ne fait que définir fnc_ds_run_seed() (appelée par
// l'importateur de démo de l'administration).
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	fnc_ds_run_seed();
}
