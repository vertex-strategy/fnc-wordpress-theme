<?php
/**
 * Forum Numérique Congo — héros des pages : contenu et image éditables.
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registre des heros : route => { label, eyebrow, title, intro, image }.
 * `image` = nom de fichier dans assets/images/ (repli si aucune image choisie).
 */
function fnc_hero_registry() {
	return array(
		'intervenants'   => array(
			'label'   => __( 'Intervenants', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Intervenants', 'fnc-wordpress-theme' ),
			'title'   => __( 'Les voix du Forum', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Décideurs, chercheurs, entrepreneurs et acteurs de la société civile qui font avancer la réflexion sur le numérique en Afrique centrale.', 'fnc-wordpress-theme' ),
			'image'   => 'intervenants.png',
		),
		'programme'      => array(
			'label'   => __( 'Programme', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Programme', 'fnc-wordpress-theme' ),
			'title'   => __( 'Deux jours de dialogue', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Sessions plénières, tables rondes et ateliers. Le programme détaillé sera publié après validation des intervenants et des horaires.', 'fnc-wordpress-theme' ),
			'image'   => 'programme.png',
		),
		'ressources'     => array(
			'label'   => __( 'Ressources', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Ressources', 'fnc-wordpress-theme' ),
			'title'   => __( 'Une bibliothèque pour prolonger le dialogue.', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Rapports, actes, communiqués : les traces écrites du Forum se rassemblent ici, à mesure qu’elles sont validées.', 'fnc-wordpress-theme' ),
			'image'   => 'les-publications.png',
		),
		'editions'       => array(
			'label'   => __( 'Éditions', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Éditions', 'fnc-wordpress-theme' ),
			'title'   => __( 'Chaque édition laisse une trace.', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Rendez-vous après rendez-vous, le Forum construit une mémoire commune du numérique en Afrique centrale. Retrouvez ici les éditions passées, celle en cours et celles à venir.', 'fnc-wordpress-theme' ),
			'image'   => 'editions.png',
		),
		'actualites'     => array(
			'label'   => __( 'Actualités', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Actualités', 'fnc-wordpress-theme' ),
			'title'   => __( 'La vie du Forum, entre deux éditions.', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Les temps forts, annonces et rendez-vous de l’institution, au fil de l’eau.', 'fnc-wordpress-theme' ),
			'image'   => 'le-fleuve.png',
		),
		'partenaires'    => array(
			'label'   => __( 'Partenaires', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Partenaires', 'fnc-wordpress-theme' ),
			'title'   => __( 'S’associer au Forum, c’est engager l’avenir numérique du Congo.', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Le Forum réunit institutions, organisations et entreprises autour d’une même ambition. Découvrez pourquoi et comment s’y associer.', 'fnc-wordpress-theme' ),
			'image'   => 'la-main.png',
		),
		'espace-presse'  => array(
			'label'   => __( 'Espace presse', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Presse', 'fnc-wordpress-theme' ),
			'title'   => __( 'Un point d’accès clair pour couvrir le Forum.', 'fnc-wordpress-theme' ),
			'intro'   => __( 'L’Espace presse rassemble uniquement les ressources publiées et les contacts confirmés, afin de faciliter un traitement médiatique fiable.', 'fnc-wordpress-theme' ),
			'image'   => 'les-publications.png',
		),
		'infos-pratiques' => array(
			'label'   => __( 'Informations pratiques', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Préparez votre venue', 'fnc-wordpress-theme' ),
			'title'   => __( 'Informations pratiques', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Tout ce qu’il faut pour organiser votre participation — lieu, transport, hébergement, visa, badge et contacts utiles.', 'fnc-wordpress-theme' ),
			'image'   => 'le-badge.png',
		),
		'edition-en-cours' => array(
			'label'   => __( 'Édition en cours', 'fnc-wordpress-theme' ),
			'eyebrow' => __( 'Édition en cours', 'fnc-wordpress-theme' ),
			'title'   => __( 'Édition 2027', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Trois jours de travail collectif autour de la souveraineté numérique. Voici tout ce qu’il faut pour préparer votre participation.', 'fnc-wordpress-theme' ),
			'image'   => 'edition-en-cours.jpeg',
		),
	);
}

/**
 * Valeur d'un champ de hero : surcharge editoriale sinon defaut du registre.
 *
 * @param string $route Cle du registre (ex. « intervenants »).
 * @param string $key   eyebrow|title|intro
 * @return string
 */
function fnc_hero( $route, $key ) {
	$reg     = fnc_hero_registry();
	$default = isset( $reg[ $route ][ $key ] ) ? $reg[ $route ][ $key ] : '';
	$value   = (string) get_theme_mod( "fnc_hero_{$route}_{$key}", '' );
	return '' !== trim( $value ) ? $value : $default;
}

/**
 * URL de l'image de hero : image choisie (mediatheque) sinon l'asset par defaut.
 *
 * @param string $route
 * @return string
 */
function fnc_hero_image_url( $route ) {
	$id = (int) get_theme_mod( "fnc_hero_{$route}_image", 0 );
	if ( $id > 0 ) {
		$url = wp_get_attachment_url( $id );
		if ( $url ) {
			return $url;
		}
	}
	$reg  = fnc_hero_registry();
	$file = isset( $reg[ $route ]['image'] ) ? $reg[ $route ]['image'] : '';
	return $file ? get_template_directory_uri() . '/assets/images/' . $file : '';
}

/**
 * Enregistrement Customizer : un panneau « Héros des pages », une section par
 * route, 4 controles (sur-titre, titre, intro, image). Controles natifs — aucun
 * JavaScript specifique. Le defaut est rappele dans le libelle de chaque champ.
 */
function fnc_hero_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'fnc_heroes',
		array(
			'title'       => __( 'Héros des pages', 'fnc-wordpress-theme' ),
			'description' => __( 'Sur-titre, titre, introduction et image des pages à liste (annuaires, éditions, ressources, partenaires…). Laisser vide = valeur par défaut. La liste reste générée automatiquement.', 'fnc-wordpress-theme' ),
			'priority'    => 30,
		)
	);

	foreach ( fnc_hero_registry() as $route => $def ) {
		$section = "fnc_hero_{$route}";
		$wp_customize->add_section(
			$section,
			array(
				'title' => $def['label'],
				'panel' => 'fnc_heroes',
			)
		);

		$text_fields = array(
			'eyebrow' => __( 'Sur-titre', 'fnc-wordpress-theme' ),
			'title'   => __( 'Titre', 'fnc-wordpress-theme' ),
			'intro'   => __( 'Introduction', 'fnc-wordpress-theme' ),
		);
		// Selecteurs (uniques par page : un seul hero .opening) pour l'apercu en
		// direct (selective refresh) — le champ se met a jour sans rechargement.
		$hero_selectors = array(
			'eyebrow' => '.opening .eyebrow',
			'title'   => '.opening h1',
			'intro'   => '.opening .intro',
		);
		foreach ( $text_fields as $key => $label ) {
			$is_area = ( 'intro' === $key );
			$wp_customize->add_setting(
				"fnc_hero_{$route}_{$key}",
				array(
					'default'           => '',
					'sanitize_callback' => $is_area ? 'sanitize_textarea_field' : 'sanitize_text_field',
					'transport'         => 'postMessage',
				)
			);
			$wp_customize->add_control(
				"fnc_hero_{$route}_{$key}",
				array(
					'section'     => $section,
					'label'       => $label,
					'description' => sprintf(
						/* translators: %s: valeur par defaut. */
						__( 'Par défaut : « %s »', 'fnc-wordpress-theme' ),
						$def[ $key ]
					),
					'type'        => $is_area ? 'textarea' : 'text',
				)
			);
			if ( isset( $wp_customize->selective_refresh ) ) {
				$wp_customize->selective_refresh->add_partial(
					"fnc_hero_{$route}_{$key}",
					array(
						'selector'        => $hero_selectors[ $key ],
						'render_callback' => function () use ( $route, $key ) {
							return esc_html( fnc_hero( $route, $key ) );
						},
					)
				);
			}
		}

		$wp_customize->add_setting(
			"fnc_hero_{$route}_image",
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				"fnc_hero_{$route}_image",
				array(
					'section'   => $section,
					'label'     => __( 'Image du héros', 'fnc-wordpress-theme' ),
					'mime_type' => 'image',
				)
			)
		);
	}
}
add_action( 'customize_register', 'fnc_hero_customize_register' );

/**
 * Pont l’édition des pages (FNC Core) <-> heros editables du Customizer.
 *
 * l’édition des pages expose l'accesseur fnc_page_hero() qui applique : override ACF ->
 * defaut de route. On branche ici ses DEFAUTS (image/sur-titre/titre) sur les
 * valeurs editables du Customizer (fnc_hero). Resultat via fnc_page_hero() :
 * ACF (si installe) -> Customizer (editable sans ACF) -> defaut de route.
 * Aucune regression, pret pour ACF le jour ou il est actif.
 */
function fnc_bridge_hero_defaults( $out, $route, $en ) {
	$reg = fnc_hero_registry();
	if ( isset( $reg[ $route ] ) ) {
		$out['eyebrow'] = fnc_hero( $route, 'eyebrow' );
		$out['title']   = fnc_hero( $route, 'title' );
		$img            = fnc_hero_image_url( $route );
		if ( $img ) {
			$out['image'] = $img;
		}
	}
	return $out;
}
add_filter( 'fnc_page_hero_defaults', 'fnc_bridge_hero_defaults', 10, 3 );

/**
 * Hero resolu d'une route, pour les gabarits de liste. Passe par l'accesseur
 * l’édition des pages fnc_page_hero() (donc override ACF puis Customizer via le pont), et
 * complete l'intro — que l’édition des pages ne porte pas par defaut — avec la valeur
 * editable du Customizer. Degrade proprement si l’édition des pages est absent.
 *
 * @param string $route Slug de route (ex. « intervenants »).
 * @return array{image:string,eyebrow:string,title:string,intro:string}
 */
function fnc_route_hero( $route ) {
	if ( function_exists( 'fnc_page_hero' ) ) {
		$page = function_exists( 'fnc_page_for_route' ) ? fnc_page_for_route( $route ) : null;
		$h    = fnc_page_hero( $page ? $page->ID : 0, $route );
	} else {
		// l’édition des pages absent : on lit directement la couche Customizer.
		$h = array(
			'image'   => fnc_hero_image_url( $route ),
			'eyebrow' => fnc_hero( $route, 'eyebrow' ),
			'title'   => fnc_hero( $route, 'title' ),
			'intro'   => '',
		);
	}
	// Intro : override ACF (deja dans $h si present) SINON valeur Customizer.
	if ( empty( $h['intro'] ) ) {
		$reg = fnc_hero_registry();
		if ( isset( $reg[ $route ] ) ) {
			$h['intro'] = fnc_hero( $route, 'intro' );
		}
	}
	return array(
		'image'   => isset( $h['image'] ) ? (string) $h['image'] : '',
		'eyebrow' => isset( $h['eyebrow'] ) ? (string) $h['eyebrow'] : '',
		'title'   => isset( $h['title'] ) ? (string) $h['title'] : '',
		'intro'   => isset( $h['intro'] ) ? (string) $h['intro'] : '',
	);
}
