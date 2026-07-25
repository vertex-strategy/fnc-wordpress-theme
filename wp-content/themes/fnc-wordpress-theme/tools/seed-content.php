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
 * Couvre les pages composables en blocs, y compris via les blocs fonctionnels
 * fnc/form et fnc/coordonnees (contact, inscription). Les pages a LISTE
 * dynamique (mur de partenaires, ressources presse, rubriques pratiques de
 * l'edition, agenda live) ne sont volontairement PAS semees : leur liste
 * disparaitrait. Elles relevent de l'approche « hero editable + liste auto »
 * (options), comme les archives.
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
 * Ecrit une composition de blocs dans une Page (par chemin de slug) et fixe son
 * archetype (qui verrouille la palette de blocs de l'editeur, cf. le filtre
 * fnc_restrict_page_blocks).
 *
 * @param string   $path      Slug (ex. « le-forum » ou « le-forum/mot-du-president »).
 * @param string[] $blocks
 * @param bool     $force     Recomposer meme si la page est deja en blocs.
 * @param string   $archetype Archetype a poser (verrouille la palette si
 *                            « institutional » ou « generic »).
 */
function fnc_seed_page( $path, array $blocks, $force, $archetype = 'generic' ) {
	$page = get_page_by_path( $path );
	if ( ! $page ) {
		fnc_seed_log( "  ⚠ page introuvable : $path" );
		return;
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( 'fnc_archetype', $archetype, $page->ID );
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
	fnc_seed_log( "  ✔ $path composee en blocs (#{$page->ID}, archetype=$archetype)" );
}

/* ------------------------------------------------------------------ */

$fnc_seed_force = isset( $args ) && is_array( $args ) && in_array( 'force', $args, true );

fnc_seed_log( 'Seed des pages editoriales' . ( $fnc_seed_force ? ' (force)' : '' ) . ' :' );

/* ---- Le Forum (page institutionnelle → sections ACF, Module G) ---- */
fnc_seed_acf_page(
	'le-forum',
	'institutional',
	array(
		array(
			'acf_fc_layout' => 'instHero',
			'image'         => fnc_seed_image( 'le-forum.png' ),
			'breadcrumb'    => 'Le Forum',
			'eyebrow'       => 'Ce que nous sommes',
			'title_a'       => 'Une institution,',
			'title_b'       => 'pas un évènement.',
			'intro'         => '« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. »',
		),
		array(
			'acf_fc_layout' => 'instPresident',
			'eyebrow'       => 'Le mot du Président',
			'photo'         => fnc_seed_image( 'le-portrait.png' ),
			'name'          => 'Eugène Rufin BOUYA',
			'role'          => 'Président du Forum Numérique Congo',
			'excerpt'       => 'Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. Entre les éditions, nous transformons une ambition nationale en trajectoire concrète, au service des Congolais.',
			'cta_label'     => 'Lire le mot complet',
		),
		array(
			'acf_fc_layout' => 'instSplit',
			'eyebrow'       => 'Notre mission',
			'l1'            => 'Comprendre.',
			'l2'            => 'Rassembler.',
			'l3'            => 'Décider ensemble.',
			'body'          => 'Le Forum réunit institutions, entreprises, chercheurs et société civile pour transformer une ambition nationale en trajectoire numérique lisible. Un rendez-vous majeur tous les deux ans, un travail continu entre les éditions.',
			'image'         => fnc_seed_image( 'le-panel.png' ),
		),
		array(
			'acf_fc_layout' => 'instObjectives',
			'eyebrow'       => 'Nos objectifs',
			'title'         => 'Ce que le Forum rend possible',
			'items'         => array(
				array( 't' => 'Comprendre', 'd' => 'Éclairer les enjeux du numérique pour l’Afrique centrale, sans jargon ni importation de modèles.' ),
				array( 't' => 'Dialoguer', 'd' => 'Créer un espace de parole partagé entre décideurs publics, acteurs privés et citoyens.' ),
				array( 't' => 'Coopérer', 'd' => 'Faire émerger des coopérations concrètes, régionales et durables.' ),
				array( 't' => 'Décider', 'd' => 'Transformer les échanges en décisions et en engagements traçables.' ),
			),
		),
		array(
			'acf_fc_layout' => 'instManifesto',
			'image'         => fnc_seed_image( 'le-territoire-brazzaville.png' ),
			'eyebrow'       => 'Notre ancrage',
			'manifest_a'    => 'Depuis Brazzaville,',
			'manifest_b'    => 'au cœur du Bassin du Congo.',
			'sub'           => 'Une parole ancrée dans son territoire, tournée vers toute l’Afrique centrale.',
		),
		array(
			'acf_fc_layout' => 'instCallout',
			'title'         => 'Participer au Forum',
			'desc'          => 'Rejoignez la prochaine édition, du 25 au 27 mars 2027, à l’Hôtel Hilton de Brazzaville.',
			'cta_label'     => 'Réserver votre place',
			'cta_href'      => '/inscription',
		),
	),
	$fnc_seed_force
);

/* ---- Le mot du Président (page institutionnelle → sections ACF, Module G) ---- */
fnc_seed_acf_page(
	'le-forum/mot-du-president',
	'institutional',
	array(
		array(
			'acf_fc_layout' => 'instHero',
			'image'         => fnc_seed_image( 'le-pupitre.png' ),
			'breadcrumb'    => 'Le mot du Président',
			'eyebrow'       => 'Le Président du Forum',
			'title_a'       => 'Le mot du Président',
			'intro'         => '« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. Entre les éditions, nous transformons une ambition nationale en trajectoire concrète, au service des Congolais. »',
		),
		array(
			'acf_fc_layout' => 'instPresident',
			'photo'         => fnc_seed_image( 'le-portrait.png' ),
			'name'          => 'Eugène Rufin BOUYA',
			'role'          => 'Président du Forum Numérique Congo',
			'message'       => '<p>Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale.</p>'
				. '<p>Entre les éditions, nous poursuivons un travail continu : réunir les institutions, les entreprises, les chercheurs et la société civile pour transformer une ambition nationale en trajectoire numérique lisible et partagée.</p>'
				. '<p>Notre conviction est simple : le numérique n’est pas une fin, mais un moyen — celui de rendre nos services publics plus accessibles, nos entreprises plus compétitives et notre jeunesse mieux outillée.</p>'
				. '<p>Je vous invite à rejoindre cette dynamique, depuis Brazzaville, au cœur du Bassin du Congo, et tournée vers toute l’Afrique centrale.</p>',
		),
		array(
			'acf_fc_layout' => 'instCallout',
			'title'         => 'Rejoignez le Forum',
			'desc'          => 'La prochaine édition réunit décideurs, experts et société civile autour du numérique. Prenez part au dialogue.',
			'cta_label'     => 'S’inscrire',
			'cta_href'      => '/inscription',
		),
	),
	$fnc_seed_force
);

/* ---- Contact (héros + orientation + coordonnées + formulaire) ---- */
fnc_seed_page(
	'contact',
	array(
		fnc_seed_block(
			'inst-hero',
			array(
				'image'      => fnc_seed_image( 'contact.png' ),
				'breadcrumb' => 'Contact',
				'eyebrow'    => 'Contact',
				'titleA'     => 'Une porte d’entrée claire,',
				'titleB'     => 'pour chaque demande.',
				'intro'      => 'Partenariat, presse, intervention ou simple question : dites-nous ce qui vous amène, nous orientons votre demande vers le bon interlocuteur.',
			)
		),
		fnc_seed_block(
			'inst-split',
			array(
				'eyebrow' => 'Orientation',
				'l1'      => 'Vous orienter,',
				'l2'      => 'sans rien promettre',
				'l3'      => 'à la légère.',
				'body'    => 'Un bon contact inspire confiance avant de demander quoi que ce soit. Vous trouverez ici les coordonnées officielles et un formulaire pour préciser votre demande — nous l’orientons vers le bon interlocuteur.',
				'image'   => fnc_seed_image( 'le-pupitre.png' ),
			)
		),
		fnc_seed_block(
			'coordonnees',
			array(
				'eyebrow' => 'Coordonnées',
				'intro'   => 'Chaque emplacement attend son information validée. Rien n’est complété au hasard.',
			)
		),
		fnc_seed_block(
			'form',
			array( 'formType' => 'contact' )
		),
	),
	$fnc_seed_force,
	'institutional'
);

/* ---- Inscription (héros + formulaire) ---- */
fnc_seed_page(
	'inscription',
	array(
		fnc_seed_block(
			'inst-hero',
			array(
				'image'      => fnc_seed_image( 'le-badge.png' ),
				'breadcrumb' => 'Inscription',
				'eyebrow'    => 'Participer',
				'titleA'     => 'Demander une inscription',
				'intro'      => 'Adressez votre demande de participation. Notre équipe l’examine et revient vers vous — cette demande ne vaut pas confirmation.',
			)
		),
		fnc_seed_block(
			'form',
			array( 'formType' => 'inscription', 'linen' => '1' )
		),
	),
	$fnc_seed_force,
	'generic'
);

/**
 * Sème les sections ACF (Module G / Flexible Content) d'une page
 * institutionnelle ou composable. Le plugin fournit les CHAMPS ; le thème
 * fournit le rendu (inc/page-sections.php). Une seule source de vérité : on
 * vide le contenu bloc residuel pour eviter deux representations concurrentes.
 * Idempotent : ignore la page si elle a deja des sections, sauf « force ».
 *
 * @param string $path      Slug de la page.
 * @param string $archetype 'institutional' | 'generic'.
 * @param array  $sections  Lignes de Flexible Content (avec « acf_fc_layout »).
 * @param bool   $force
 */
function fnc_seed_acf_page( $path, $archetype, array $sections, $force ) {
	if ( ! function_exists( 'update_field' ) ) {
		fnc_seed_log( "  ⚠ ACF/SCF absent : $path (sections non semees ; installez « secure-custom-fields »)" );
		return;
	}
	do_action( 'acf/init' );
	$page = get_page_by_path( $path );
	if ( ! $page ) {
		fnc_seed_log( "  ⚠ page introuvable : $path" );
		return;
	}
	$field   = ( 'generic' === $archetype ) ? 'sections' : 'institutional_sections';
	$existing = get_field( $field, $page->ID );
	if ( ! $force && ! empty( $existing ) ) {
		fnc_seed_log( "  = $path a deja des sections ACF (skip ; « force » pour ecraser)" );
		return;
	}
	update_field( 'fnc_archetype', $archetype, $page->ID );
	update_field( $field, $sections, $page->ID );
	// Une seule source : effacer le contenu bloc residuel (le rendu ACF prime).
	if ( '' !== trim( (string) $page->post_content ) ) {
		wp_update_post( array( 'ID' => $page->ID, 'post_content' => '' ) );
	}
	fnc_seed_log( "  ✔ $path : " . count( $sections ) . " sections ACF semees (#{$page->ID})" );
}

/**
 * Sème le contenu CLASSIQUE (blocs core) d'une page — pour les pages legales,
 * editees dans l'editeur standard (pas de blocs FNC). Idempotent : ignore la
 * page si elle a deja du contenu, sauf « force ».
 */
function fnc_seed_classic_page( $path, $content, $force ) {
	$page = get_page_by_path( $path );
	if ( ! $page ) {
		fnc_seed_log( "  ⚠ page introuvable : $path" );
		return;
	}
	if ( ! $force && '' !== trim( (string) $page->post_content ) ) {
		fnc_seed_log( "  = $path a deja du contenu (skip ; « force » pour ecraser)" );
		return;
	}
	wp_update_post( array( 'ID' => $page->ID, 'post_content' => $content ) );
	fnc_seed_log( "  ✔ $path contenu legal seme (#{$page->ID})" );
}

/** Paragraphe core Gutenberg. */
function fnc_seed_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->";
}
/** Titre core Gutenberg (h2). */
function fnc_seed_h2( $text ) {
	return "<!-- wp:heading -->\n<h2>" . esc_html( $text ) . "</h2>\n<!-- /wp:heading -->";
}

/* ---- Pages legales (corps editable dans l'editeur standard) ---- */
fnc_seed_classic_page(
	'mentions-legales',
	implode(
		"\n\n",
		array(
			fnc_seed_p( '[DÉMO] Éditeur du site : nom de l’organisation éditrice — à confirmer.' ),
			fnc_seed_p( '[DÉMO] Directeur de la publication, hébergeur et coordonnées légales : à confirmer et valider avant toute publication réelle.' ),
		)
	),
	$fnc_seed_force
);
fnc_seed_classic_page(
	'politique-confidentialite',
	implode(
		"\n\n",
		array(
			fnc_seed_p( '[DÉMO] Traitement des données personnelles conforme au cadre applicable — à confirmer.' ),
			fnc_seed_p( '[DÉMO] Finalités, durée de conservation et droits des personnes : à confirmer et valider avant toute publication réelle.' ),
			fnc_seed_h2( 'Mesure d’audience' ),
			fnc_seed_p( '[DÉMO] Cette section décrit, à titre d’exemple, une mesure d’audience respectueuse de la vie privée. Ce thème de démonstration n’intègre aucune solution de mesure réelle : à adapter selon l’outil effectivement utilisé avant toute publication.' ),
		)
	),
	$fnc_seed_force
);
fnc_seed_classic_page(
	'conditions-generales-utilisation',
	implode(
		"\n\n",
		array(
			fnc_seed_p( '[DÉMO] Conditions d’usage du site — à confirmer et valider avant toute publication réelle.' ),
			fnc_seed_p( '[DÉMO] Page structurelle, à maintenir non publiée tant que le texte n’est pas validé.' ),
		)
	),
	$fnc_seed_force
);
fnc_seed_classic_page(
	'declaration-accessibilite',
	implode(
		"\n\n",
		array(
			fnc_seed_p( '[DÉMO] Cette déclaration ne certifie aucune conformité : elle sera rédigée après audit d’accessibilité réel, jamais présentée comme conforme sans validation explicite.' ),
			fnc_seed_p( '[DÉMO] Référentiel visé, résultats d’audit, contenus non accessibles et voies de recours : à confirmer et valider avant toute publication réelle.' ),
		)
	),
	$fnc_seed_force
);

fnc_seed_log( 'Terminé.' );
