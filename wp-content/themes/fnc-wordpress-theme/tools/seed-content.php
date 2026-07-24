<?php
/**
 * Seed reproductible des pages de contenu composees en blocs FNC.
 *
 * Transforme les pages EDITORIALES (contenu fige dans les gabarits) en pages
 * reellement administrables : chaque section devient un bloc FNC editable et
 * reagencable dans l'editeur WordPress. Rendu identique, mais tout devient
 * modifiable (texte, image, ordre des sections).
 *
 * Idempotent :
 *   - les images sont importees une seule fois (meta `_fnc_seed_src`) ;
 *   - une page deja composee de blocs est ignoree, sauf argument « force ».
 *
 * Usage (depuis le conteneur wp-cli) :
 *   wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-content.php
 *   wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-content.php force
 *
 * NB : ne concerne QUE les pages purement editoriales. Les pages a composant
 * fonctionnel (formulaires contact/inscription/partenariat, coordonnees issues
 * des Reglages, agenda live d'edition-en-cours, rubriques pratiques) gardent
 * leur gabarit : les composer en blocs ferait disparaitre ces parties dynamiques.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Journalisation compatible wp-cli ou navigateur. */
function fnc_seed_log( $msg ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $msg );
	} else {
		echo $msg . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Importe une image des assets du theme dans la mediatheque, une seule fois.
 *
 * @param string $rel Chemin relatif dans assets/images/.
 * @return int  ID de la piece jointe (0 si introuvable).
 */
function fnc_seed_image( $rel ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_fnc_seed_src',
			'meta_value'  => $rel,
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
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_fnc_seed_src', $rel );
	return (int) $id;
}

/** Compose un commentaire de bloc dynamique FNC. */
function fnc_seed_block( $name, array $attrs ) {
	return '<!-- wp:fnc/' . $name . ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ' /-->';
}

/**
 * Ecrit une composition de blocs dans une Page (par chemin de slug).
 *
 * @param string   $path  Slug (ex. « le-forum » ou « le-forum/mot-du-president »).
 * @param string[] $blocks
 * @param bool     $force Recomposer meme si la page est deja en blocs.
 */
function fnc_seed_page( $path, array $blocks, $force ) {
	$page = get_page_by_path( $path );
	if ( ! $page ) {
		fnc_seed_log( "  ⚠ page introuvable : $path" );
		return;
	}
	if ( ! $force && function_exists( 'fnc_page_has_blocks' ) && fnc_page_has_blocks( $page ) ) {
		fnc_seed_log( "  = $path deja compose (skip ; « force » pour recomposer)" );
		return;
	}
	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => implode( "\n\n", $blocks ) . "\n",
		)
	);
	fnc_seed_log( "  ✔ $path composee en blocs (#{$page->ID})" );
}

/* ------------------------------------------------------------------ */

$fnc_seed_force = isset( $args ) && is_array( $args ) && in_array( 'force', $args, true );

fnc_seed_log( 'Seed des pages editoriales' . ( $fnc_seed_force ? ' (force)' : '' ) . ' :' );

/* ---- Le Forum ---- */
fnc_seed_page(
	'le-forum',
	array(
		fnc_seed_block(
			'inst-hero',
			array(
				'image'      => fnc_seed_image( 'le-forum.png' ),
				'breadcrumb' => 'Le Forum',
				'eyebrow'    => 'Ce que nous sommes',
				'titleA'     => 'Une institution,',
				'titleB'     => 'pas un évènement.',
				'intro'      => '« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. »',
			)
		),
		fnc_seed_block(
			'inst-president',
			array(
				'eyebrow'  => 'Le mot du Président',
				'photo'    => fnc_seed_image( 'le-portrait.png' ),
				'name'     => 'Eugène Rufin BOUYA',
				'role'     => 'Président du Forum Numérique Congo',
				'excerpt'  => 'Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. Entre les éditions, nous transformons une ambition nationale en trajectoire concrète, au service des Congolais.',
				'ctaLabel' => 'Lire le mot complet',
				'ctaHref'  => '/le-forum/mot-du-president',
			)
		),
		fnc_seed_block(
			'inst-split',
			array(
				'eyebrow' => 'Notre mission',
				'l1'      => 'Comprendre.',
				'l2'      => 'Rassembler.',
				'l3'      => 'Décider ensemble.',
				'body'    => 'Le Forum réunit institutions, entreprises, chercheurs et société civile pour transformer une ambition nationale en trajectoire numérique lisible. Un rendez-vous majeur tous les deux ans, un travail continu entre les éditions.',
				'image'   => fnc_seed_image( 'le-panel.png' ),
			)
		),
		fnc_seed_block(
			'inst-objectives',
			array(
				'eyebrow' => 'Nos objectifs',
				'title'   => 'Ce que le Forum rend possible',
				'items'   => array(
					array( 't' => 'Comprendre', 'd' => 'Éclairer les enjeux du numérique pour l’Afrique centrale, sans jargon ni importation de modèles.' ),
					array( 't' => 'Dialoguer', 'd' => 'Créer un espace de parole partagé entre décideurs publics, acteurs privés et citoyens.' ),
					array( 't' => 'Coopérer', 'd' => 'Faire émerger des coopérations concrètes, régionales et durables.' ),
					array( 't' => 'Décider', 'd' => 'Transformer les échanges en décisions et en engagements traçables.' ),
				),
			)
		),
		fnc_seed_block(
			'inst-manifesto',
			array(
				'image'     => fnc_seed_image( 'le-territoire-brazzaville.png' ),
				'eyebrow'   => 'Notre ancrage',
				'manifestA' => 'Depuis Brazzaville,',
				'manifestB' => 'au cœur du Bassin du Congo.',
				'sub'       => 'Une parole ancrée dans son territoire, tournée vers toute l’Afrique centrale.',
			)
		),
		fnc_seed_block(
			'inst-callout',
			array(
				'title'    => 'Participer au Forum',
				'desc'     => 'Rejoignez la prochaine édition, du 25 au 27 mars 2027, à l’Hôtel Hilton de Brazzaville.',
				'ctaLabel' => 'Réserver votre place',
				'ctaHref'  => '/inscription',
			)
		),
	),
	$fnc_seed_force
);

/* ---- Le mot du Président ---- */
fnc_seed_page(
	'le-forum/mot-du-president',
	array(
		fnc_seed_block(
			'inst-hero',
			array(
				'image'      => fnc_seed_image( 'le-pupitre.png' ),
				'breadcrumb' => 'Le mot du Président',
				'eyebrow'    => 'Le Président du Forum',
				'titleA'     => 'Le mot du Président',
				'intro'      => '« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. Entre les éditions, nous transformons une ambition nationale en trajectoire concrète, au service des Congolais. »',
			)
		),
		fnc_seed_block(
			'inst-president',
			array(
				'photo'   => fnc_seed_image( 'le-portrait.png' ),
				'name'    => 'Eugène Rufin BOUYA',
				'role'    => 'Président du Forum Numérique Congo',
				'message' => '<p>Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale.</p>'
					. '<p>Entre les éditions, nous poursuivons un travail continu : réunir les institutions, les entreprises, les chercheurs et la société civile pour transformer une ambition nationale en trajectoire numérique lisible et partagée.</p>'
					. '<p>Notre conviction est simple : le numérique n’est pas une fin, mais un moyen — celui de rendre nos services publics plus accessibles, nos entreprises plus compétitives et notre jeunesse mieux outillée.</p>'
					. '<p>Je vous invite à rejoindre cette dynamique, depuis Brazzaville, au cœur du Bassin du Congo, et tournée vers toute l’Afrique centrale.</p>',
			)
		),
		fnc_seed_block(
			'inst-callout',
			array(
				'title'    => 'Rejoignez le Forum',
				'desc'     => 'La prochaine édition réunit décideurs, experts et société civile autour du numérique. Prenez part au dialogue.',
				'ctaLabel' => 'S’inscrire',
				'ctaHref'  => '/inscription',
			)
		),
	),
	$fnc_seed_force
);

fnc_seed_log( 'Terminé.' );
