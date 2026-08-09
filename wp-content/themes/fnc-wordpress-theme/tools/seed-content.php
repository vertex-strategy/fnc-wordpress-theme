<?php
/**
 * Forum Numérique Congo — semis des pages ÉDITORIALES en blocs (reproductible, bilingue).
 *
 * Compose « Le Forum », « Le mot du Président », « Contact » et « Inscription » en blocs
 * FNC, DANS LES DEUX LANGUES (fr + en), indépendamment de la langue par défaut choisie
 * dans Polylang. Les pages sont résolues par leur clé de semis (`page::{slug}` /
 * `page::{slug}::en`, posée par tools/seed-dataset.php → fnc_ds_ensure_pages), jamais par
 * chemin (qui dépend de la langue par défaut). Idempotent : « force » recompose.
 *
 * Rendu : blocs uniquement. Les sections ACF résiduelles sont effacées pour garantir un
 * rendu identique FR/EN et sans dépendance à SCF/ACF (les blocs fnc/* rendent seuls).
 *
 * Exécution autonome :  wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-content.php [-- force]
 * Sinon appelé par tools/seed-dataset.php (fnc_ds_run_seed → fnc_seed_run_editorial()).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Journalisation compatible wp-cli ou navigateur. */
if ( ! function_exists( 'fnc_seed_log' ) ) {
	function fnc_seed_log( $msg ) {
		if ( isset( $GLOBALS['fnc_seed_log_buffer'] ) && is_array( $GLOBALS['fnc_seed_log_buffer'] ) ) {
			$GLOBALS['fnc_seed_log_buffer'][] = $msg;
		} elseif ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::log( $msg );
		} else {
			echo $msg . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}

/**
 * Importe une image des assets du thème dans la médiathèque, une seule fois.
 *
 * @param string $rel Chemin relatif dans assets/images/.
 * @return int  ID de la pièce jointe (0 si introuvable).
 */
if ( ! function_exists( 'fnc_seed_image' ) ) {
	function fnc_seed_image( $rel ) {
		$existing = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'meta_key'    => '_fnc_seed_src', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => $rel,            // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		if ( ! empty( $existing ) ) {
			return (int) $existing[0];
		}
		$src = get_template_directory() . '/assets/images/' . $rel;
		if ( ! file_exists( $src ) ) {
			fnc_seed_log( "  ⚠ image absente : $rel" );
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload = wp_upload_bits( basename( $rel ), null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! empty( $upload['error'] ) ) {
			fnc_seed_log( "  ⚠ import KO ($rel) : " . $upload['error'] );
			return 0;
		}
		$type = wp_check_filetype( $upload['file'] );
		$id   = wp_insert_attachment(
			array(
				'post_mime_type' => $type['type'],
				'post_title'     => sanitize_file_name( basename( $rel ) ),
				'post_status'    => 'inherit',
			),
			$upload['file']
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}
		$meta = wp_generate_attachment_metadata( $id, $upload['file'] );
		wp_update_attachment_metadata( $id, $meta );
		update_post_meta( $id, '_fnc_seed_src', $rel );
		return (int) $id;
	}
}

/** Sélecteur de langue : renvoie le FR ou l'EN selon $lang. */
function fnc_seed_t( $lang, $fr, $en ) {
	return ( 'en' === $lang ) ? $en : $fr;
}

/** Compose un commentaire de bloc dynamique FNC. */
function fnc_seed_block( $name, array $attrs ) {
	return '<!-- wp:fnc/' . $name . ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ' /-->';
}

/**
 * Résout l'ID d'une page de semis par sa clé stable et sa langue.
 * FR = « page::{slug} » ; EN = « page::{slug}::en » (posées par fnc_ds_ensure_pages).
 */
function fnc_seed_find_page( $slug, $lang ) {
	$legacy = ( 'en' === $lang ) ? "page::{$slug}::en" : "page::{$slug}";
	$q      = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_fnc_seed_legacy', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $legacy,            // phpcs:ignore WordPress.DB.SlowDBQuery
			'lang'        => '',                 // toutes langues (Polylang)
		)
	);
	return ! empty( $q ) ? (int) $q[0] : 0;
}

/**
 * Écrit une composition de blocs sur une page (déjà créée par le semis), fixe son
 * archétype et efface toute section ACF résiduelle (les blocs priment, rendu unifié).
 * Idempotent : ignore si la page a déjà des blocs, sauf « force ».
 */
function fnc_seed_compose( $page_id, array $blocks, $force, $archetype ) {
	$page = get_post( $page_id );
	if ( ! $page ) {
		return false;
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( 'fnc_archetype', $archetype, $page_id );
		// Rendu unifié blocs : on vide les sections ACF (sinon elles prendraient le pas).
		update_field( 'institutional_sections', array(), $page_id );
		update_field( 'sections', array(), $page_id );
	}
	if ( ! $force && function_exists( 'fnc_page_has_blocks' ) && fnc_page_has_blocks( $page ) ) {
		return 'skip';
	}
	wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => implode( "\n\n", $blocks ) . "\n",
		)
	);
	return true;
}

/* ============================================================================
 * CONTENU ÉDITORIAL — builders par page, bilingues (FR canon + EN fidèle).
 * Les images (IDs de médiathèque) sont neutres en langue.
 * ========================================================================== */

/** Blocs de la page « Le Forum ». */
function fnc_seed_blocks_le_forum( $lang ) {
	$t = function ( $fr, $en ) use ( $lang ) { return fnc_seed_t( $lang, $fr, $en ); };
	$reg = fnc_seed_t( $lang, '/inscription', '/en/inscription-en' );
	$pres = fnc_seed_t( $lang, '/le-forum/mot-du-president', '/en/le-forum-en/mot-du-president-en' );
	return array(
		fnc_seed_block( 'inst-hero', array(
			'image'      => fnc_seed_image( 'le-forum.png' ),
			'breadcrumb' => $t( 'Le Forum', 'The Forum' ),
			'eyebrow'    => $t( 'Ce que nous sommes', 'Who we are' ),
			'titleA'     => $t( 'Une institution,', 'An institution,' ),
			'titleB'     => $t( 'pas un évènement.', 'not an event.' ),
			'intro'      => $t(
				'« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. »',
				'“The Forum Numérique Congo establishes a permanent space for dialogue, cooperation and decision-making around digital technology in Central Africa.”'
			),
		) ),
		fnc_seed_block( 'inst-president', array(
			'eyebrow'  => $t( 'Le mot du Président', 'The President’s address' ),
			'photo'    => fnc_seed_image( 'eugene-rufin-bouya.jpeg' ),
			'name'     => 'Eugène Rufin BOUYA',
			'role'     => $t( 'Président du Forum Numérique Congo', 'President of the Forum Numérique Congo' ),
			'excerpt'  => $t(
				'Le numérique n’est plus seulement un outil d’innovation ; il est devenu un levier de puissance, de compétitivité, de souveraineté et de régulation.',
				'Digital technology is no longer merely a tool for innovation; it has become a lever of power, competitiveness, sovereignty and regulation.'
			),
			'ctaLabel' => $t( 'Lire le mot complet', 'Read the full address' ),
			'ctaHref'  => $pres,
		) ),
		fnc_seed_block( 'inst-split', array(
			'eyebrow' => $t( 'Notre mission', 'Our mission' ),
			'l1'      => $t( 'Comprendre.', 'Understand.' ),
			'l2'      => $t( 'Rassembler.', 'Bring together.' ),
			'l3'      => $t( 'Décider ensemble.', 'Decide together.' ),
			'body'    => $t(
				'Le Forum réunit institutions, entreprises, chercheurs et société civile pour transformer une ambition nationale en trajectoire numérique lisible. Un rendez-vous majeur tous les deux ans, un travail continu entre les éditions.',
				'The Forum brings together institutions, businesses, researchers and civil society to turn a national ambition into a clear digital trajectory. A major gathering every two years, and continuous work between editions.'
			),
			'image'   => fnc_seed_image( 'les-personnes.png' ),
		) ),
		fnc_seed_block( 'inst-objectives', array(
			'eyebrow' => $t( 'Nos objectifs', 'Our objectives' ),
			'title'   => $t( 'Ce que le Forum rend possible', 'What the Forum makes possible' ),
			'items'   => array(
				array( 't' => $t( 'Comprendre', 'Understand' ), 'd' => $t( 'Éclairer les enjeux du numérique pour l’Afrique centrale, sans jargon ni importation de modèles.', 'Shed light on the digital stakes for Central Africa, without jargon or imported models.' ) ),
				array( 't' => $t( 'Dialoguer', 'Engage in dialogue' ), 'd' => $t( 'Créer un espace de parole partagé entre décideurs publics, acteurs privés et citoyens.', 'Create a shared space for dialogue between public decision-makers, private stakeholders and citizens.' ) ),
				array( 't' => $t( 'Coopérer', 'Cooperate' ), 'd' => $t( 'Faire émerger des coopérations concrètes, régionales et durables.', 'Foster concrete, regional and lasting cooperation.' ) ),
				array( 't' => $t( 'Décider', 'Decide' ), 'd' => $t( 'Transformer les échanges en décisions et en engagements traçables.', 'Turn discussions into decisions and traceable commitments.' ) ),
			),
		) ),
		fnc_seed_block( 'inst-manifesto', array(
			'image'     => fnc_seed_image( 'le-fleuve.png' ),
			'eyebrow'   => $t( 'Notre ancrage', 'Our roots' ),
			'manifestA' => $t( 'Depuis Brazzaville,', 'From Brazzaville,' ),
			'manifestB' => $t( 'au cœur du Bassin du Congo.', 'at the heart of the Congo Basin.' ),
			'sub'       => $t( 'Une parole ancrée dans son territoire, tournée vers toute l’Afrique centrale.', 'A voice rooted in its territory, reaching out to all of Central Africa.' ),
		) ),
		fnc_seed_block( 'inst-callout', array(
			'title'    => $t( 'Participer au Forum', 'Take part in the Forum' ),
			'desc'     => $t( 'Rejoignez la prochaine édition du Forum, aux côtés des décideurs, experts et de la société civile.', 'Join the next edition of the Forum, alongside decision-makers, experts and civil society.' ),
			'ctaLabel' => $t( 'Réserver votre place', 'Reserve your seat' ),
			'ctaHref'  => $reg,
		) ),
	);
}

/** Blocs de la page « Le mot du Président ». */
function fnc_seed_blocks_president( $lang ) {
	$t = function ( $fr, $en ) use ( $lang ) { return fnc_seed_t( $lang, $fr, $en ); };
	$reg = fnc_seed_t( $lang, '/inscription', '/en/inscription-en' );
	return array(
		fnc_seed_block( 'inst-hero', array(
			'image'      => fnc_seed_image( 'le-pupitre.png' ),
			'breadcrumb' => $t( 'Le mot du Président', 'The President’s address' ),
			'eyebrow'    => $t( 'Le Président du Forum', 'The President of the Forum' ),
			'titleA'     => $t( 'Le mot du Président', 'The President’s address' ),
			'intro'      => $t(
				'« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. Entre les éditions, nous transformons une ambition nationale en trajectoire concrète, au service des Congolais. »',
				'“The Forum Numérique Congo establishes a permanent space for dialogue, cooperation and decision-making around digital technology in Central Africa. Between editions, we turn a national ambition into a concrete trajectory, in the service of the Congolese people.”'
			),
		) ),
		fnc_seed_block( 'inst-president', array(
			'photo'   => fnc_seed_image( 'eugene-rufin-bouya.jpeg' ),
			'name'    => 'Eugène Rufin BOUYA',
			'role'    => $t( 'Président du Forum Numérique Congo', 'President of the Forum Numérique Congo' ),
			'message' => $t(
				'<p>Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale.</p>'
					. '<p>Entre les éditions, nous poursuivons un travail continu : réunir les institutions, les entreprises, les chercheurs et la société civile pour transformer une ambition nationale en trajectoire numérique lisible et partagée.</p>'
					. '<p>Notre conviction est simple : le numérique n’est pas une fin, mais un moyen — celui de rendre nos services publics plus accessibles, nos entreprises plus compétitives et notre jeunesse mieux outillée.</p>'
					. '<p>Je vous invite à rejoindre cette dynamique, depuis Brazzaville, au cœur du Bassin du Congo, et tournée vers toute l’Afrique centrale.</p>',
				'<p>The Forum Numérique Congo establishes a permanent space for dialogue, cooperation and decision-making around digital technology in Central Africa.</p>'
					. '<p>Between editions, we pursue continuous work: bringing together institutions, businesses, researchers and civil society to turn a national ambition into a clear, shared digital trajectory.</p>'
					. '<p>Our conviction is simple: digital technology is not an end, but a means — a way to make our public services more accessible, our businesses more competitive and our youth better equipped.</p>'
					. '<p>I invite you to join this momentum, from Brazzaville, at the heart of the Congo Basin, and reaching out to all of Central Africa.</p>'
			),
		) ),
		fnc_seed_block( 'inst-callout', array(
			'title'    => $t( 'Rejoignez le Forum', 'Join the Forum' ),
			'desc'     => $t( 'La prochaine édition réunit décideurs, experts et société civile autour du numérique. Prenez part au dialogue.', 'The next edition brings together decision-makers, experts and civil society around digital technology. Take part in the dialogue.' ),
			'ctaLabel' => $t( 'S’inscrire', 'Register' ),
			'ctaHref'  => $reg,
		) ),
	);
}

/** Blocs de la page « Contact ». */
function fnc_seed_blocks_contact( $lang ) {
	$t = function ( $fr, $en ) use ( $lang ) { return fnc_seed_t( $lang, $fr, $en ); };
	return array(
		fnc_seed_block( 'inst-hero', array(
			'image'      => fnc_seed_image( 'contact.png' ),
			'breadcrumb' => $t( 'Contact', 'Contact' ),
			'eyebrow'    => $t( 'Contact', 'Contact' ),
			'titleA'     => $t( 'Une porte d’entrée claire,', 'A clear point of entry,' ),
			'titleB'     => $t( 'pour chaque demande.', 'for every request.' ),
			'intro'      => $t(
				'Partenariat, presse, intervention ou simple question : dites-nous ce qui vous amène, nous orientons votre demande vers le bon interlocuteur.',
				'Partnership, press, speaking or a simple question: tell us what brings you here, and we will direct your request to the right contact.'
			),
		) ),
		fnc_seed_block( 'inst-split', array(
			'eyebrow' => $t( 'Orientation', 'Guidance' ),
			'l1'      => $t( 'Vous orienter,', 'Guiding you,' ),
			'l2'      => $t( 'sans rien promettre', 'without promising anything' ),
			'l3'      => $t( 'à la légère.', 'lightly.' ),
			'body'    => $t(
				'Un bon contact inspire confiance avant de demander quoi que ce soit. Vous trouverez ici les coordonnées officielles et un formulaire pour préciser votre demande — nous l’orientons vers le bon interlocuteur.',
				'Good contact builds trust before anything is asked. Here you will find the official details and a form to specify your request — we direct it to the right contact.'
			),
			'image'   => fnc_seed_image( 'les-personnes.png' ),
		) ),
		fnc_seed_block( 'coordonnees', array(
			'eyebrow' => $t( 'Coordonnées', 'Contact details' ),
			'intro'   => $t( 'Chaque emplacement attend son information validée. Rien n’est complété au hasard.', 'Each field awaits its validated information. Nothing is filled in at random.' ),
		) ),
		fnc_seed_block( 'form', array( 'formType' => 'contact' ) ),
	);
}

/** Blocs de la page « Inscription ». */
function fnc_seed_blocks_inscription( $lang ) {
	$t = function ( $fr, $en ) use ( $lang ) { return fnc_seed_t( $lang, $fr, $en ); };
	return array(
		fnc_seed_block( 'inst-hero', array(
			'image'      => fnc_seed_image( 'le-badge.png' ),
			'breadcrumb' => $t( 'Inscription', 'Registration' ),
			'eyebrow'    => $t( 'Participer', 'Take part' ),
			'titleA'     => $t( 'Demander une inscription', 'Request a registration' ),
			'intro'      => $t(
				'Adressez votre demande de participation. Notre équipe l’examine et revient vers vous — cette demande ne vaut pas confirmation.',
				'Submit your request to attend. Our team reviews it and gets back to you — this request does not constitute confirmation.'
			),
		) ),
		fnc_seed_block( 'form', array( 'formType' => 'inscription', 'linen' => '1' ) ),
	);
}

/**
 * Compose toutes les pages éditoriales, dans toutes les langues Polylang présentes
 * (au moins fr + en). Renvoie le journal (tableau de lignes).
 *
 * @param bool $force Recomposer même si la page a déjà des blocs.
 * @return array<int,string>
 */
function fnc_seed_run_editorial( $force = false ) {
	$GLOBALS['fnc_seed_log_buffer'] = array();

	$langs = array( 'fr', 'en' );
	if ( function_exists( 'pll_languages_list' ) ) {
		$present = (array) pll_languages_list();
		$langs   = array_values( array_intersect( array( 'fr', 'en' ), $present ) );
		if ( empty( $langs ) ) {
			$langs = array( function_exists( 'pll_default_language' ) ? pll_default_language() : 'fr' );
		}
	}

	// slug de page  => [ builder, archetype ]
	$pages = array(
		'le-forum'         => array( 'fnc_seed_blocks_le_forum', 'institutional' ),
		'mot-du-president' => array( 'fnc_seed_blocks_president', 'institutional' ),
		'contact'          => array( 'fnc_seed_blocks_contact', 'institutional' ),
		'inscription'      => array( 'fnc_seed_blocks_inscription', 'generic' ),
	);

	foreach ( $pages as $slug => $def ) {
		list( $builder, $archetype ) = $def;
		foreach ( $langs as $lang ) {
			$page_id = fnc_seed_find_page( $slug, $lang );
			if ( ! $page_id ) {
				fnc_seed_log( "  ⚠ $slug [$lang] : page introuvable (lancer d'abord le semis des pages)." );
				continue;
			}
			$blocks = call_user_func( $builder, $lang );
			$res    = fnc_seed_compose( $page_id, $blocks, $force, $archetype );
			if ( 'skip' === $res ) {
				fnc_seed_log( "  = $slug [$lang] déjà composé (#$page_id ; « force » pour recomposer)" );
			} elseif ( $res ) {
				fnc_seed_log( "  ✔ $slug [$lang] composé en blocs (#$page_id, archetype=$archetype)" );
			} else {
				fnc_seed_log( "  ⚠ $slug [$lang] : échec (#$page_id)" );
			}
		}
	}

	$log = $GLOBALS['fnc_seed_log_buffer'];
	unset( $GLOBALS['fnc_seed_log_buffer'] );
	return $log;
}

// Exécution autonome en CLI (wp eval-file). Si seed-dataset.php est chargé, c'est lui
// qui orchestre (fnc_ds_run_seed appelle fnc_seed_run_editorial) → pas d'auto-exécution.
if ( defined( 'WP_CLI' ) && WP_CLI && ! function_exists( 'fnc_ds_run_seed' ) ) {
	$fnc_force = isset( $args ) && is_array( $args ) && in_array( 'force', $args, true );
	foreach ( fnc_seed_run_editorial( $fnc_force ) as $fnc_line ) {
		WP_CLI::log( $fnc_line );
	}
}
