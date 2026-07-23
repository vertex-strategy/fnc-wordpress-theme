<?php
/**
 * Page d'accueil éditable — storyboard M1→M8 (Lot 3).
 *
 * Pendant WordPress natif de l'archétype « Homepage » de Payload
 * (src/payload/blocks/homepageFields.ts). Comme sur le vrai site, le
 * storyboard est **figé** : on n'expose pas une liste de blocs réordonnables
 * mais un groupe de champs fixe par moment. L'éditeur administre uniquement le
 * contenu (textes, médias, libellés de CTA) ; il ne peut ni changer l'ordre,
 * ni supprimer un moment, ni toucher à la DA.
 *
 * C'est pourquoi ces réglages passent par le Customizer et non par des blocs :
 * il ne s'agit pas de composition (cf. inc/blocks.php) mais de paramétrage
 * d'une structure figée — et le Customizer offre en prime l'aperçu en direct.
 *
 * Extension par rapport au vrai site (demande du Décideur) : le média du héros
 * M1 est configurable en **image fixe, vidéo ou slider**. Le vrai site ne
 * propose qu'une image de fond.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lecture d'un réglage de la page d'accueil.
 *
 * @param string $key     Clé sans préfixe (ex. « m1_tagline »).
 * @param mixed  $default Valeur de repli.
 * @return mixed
 */
function fnc_home_setting( $key, $default = '' ) {
	$value = get_theme_mod( 'fnc_home_' . $key, $default );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * URL d'un média de la page d'accueil (stocké comme ID de pièce jointe).
 *
 * @param string $key      Clé du réglage.
 * @param string $fallback URL de repli (image livrée avec le thème).
 * @return string
 */
function fnc_home_media_url( $key, $fallback = '' ) {
	$id = (int) fnc_home_setting( $key, 0 );
	if ( $id > 0 ) {
		$url = wp_get_attachment_url( $id );
		if ( $url ) {
			return $url;
		}
	}
	return $fallback;
}

/**
 * Mode d'affichage du média du héros : image | video | slider.
 *
 * @return string
 */
function fnc_home_hero_mode() {
	$mode = fnc_home_setting( 'm1_media_type', 'image' );
	return in_array( $mode, array( 'image', 'video', 'slider' ), true ) ? $mode : 'image';
}

/**
 * Images du slider du héros réellement renseignées (max 5 emplacements).
 *
 * @return array<int,string> URLs
 */
function fnc_home_hero_slides() {
	$slides = array();
	for ( $i = 1; $i <= 5; $i++ ) {
		$url = fnc_home_media_url( 'm1_slide_' . $i );
		if ( $url ) {
			$slides[] = $url;
		}
	}
	return $slides;
}

/* ============================================================
   Enregistrement Customizer
   ============================================================ */

/**
 * Ajoute un champ simple au Customizer (helper de concision).
 *
 * @param WP_Customize_Manager $wp_customize
 * @param string               $section
 * @param string               $key
 * @param string               $label
 * @param string               $type     text|textarea|url|number
 * @param mixed                $default
 * @param array<string,mixed>  $args     Arguments additionnels du contrôle.
 */
function fnc_home_add_field( $wp_customize, $section, $key, $label, $type = 'text', $default = '', $args = array() ) {
	$sanitize = 'sanitize_text_field';
	if ( 'textarea' === $type ) {
		$sanitize = 'sanitize_textarea_field';
	} elseif ( 'url' === $type ) {
		$sanitize = 'esc_url_raw';
	} elseif ( 'number' === $type ) {
		$sanitize = 'absint';
	}

	$wp_customize->add_setting(
		'fnc_home_' . $key,
		array( 'default' => $default, 'sanitize_callback' => $sanitize, 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'fnc_home_' . $key,
		array_merge(
			array( 'label' => $label, 'section' => $section, 'type' => $type ),
			$args
		)
	);
}

/**
 * Ajoute un champ média (image ou vidéo) au Customizer.
 */
function fnc_home_add_media( $wp_customize, $section, $key, $label, $mime = 'image', $args = array() ) {
	$wp_customize->add_setting(
		'fnc_home_' . $key,
		array( 'default' => 0, 'sanitize_callback' => 'absint', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'fnc_home_' . $key,
			array_merge(
				array( 'label' => $label, 'section' => $section, 'mime_type' => $mime ),
				$args
			)
		)
	);
}

/**
 * Enregistrement des réglages de la page d'accueil.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function fnc_homepage_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'fnc_homepage',
		array(
			'title'       => __( 'Page d’accueil (M1 → M8)', 'fnc-wordpress-theme' ),
			'description' => __( 'Contenu éditorial des 8 moments. La composition et la direction artistique sont figées : vous administrez les textes, les médias et les libellés de boutons.', 'fnc-wordpress-theme' ),
			'priority'    => 21,
		)
	);

	/* -------------------------------------------------- M1 · Ouverture */
	$wp_customize->add_section(
		'fnc_home_m1',
		array(
			'title'       => __( 'M1 · Ouverture', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_homepage',
			'description' => __( 'Le média de fond peut être une image fixe, une vidéo ou un slider.', 'fnc-wordpress-theme' ),
		)
	);

	$wp_customize->add_setting(
		'fnc_home_m1_media_type',
		array( 'default' => 'image', 'sanitize_callback' => 'fnc_sanitize_choice', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'fnc_home_m1_media_type',
		array(
			'label'   => __( 'Type de média de fond', 'fnc-wordpress-theme' ),
			'section' => 'fnc_home_m1',
			'type'    => 'select',
			'choices' => array(
				'image'  => __( 'Image fixe', 'fnc-wordpress-theme' ),
				'video'  => __( 'Vidéo', 'fnc-wordpress-theme' ),
				'slider' => __( 'Slider (plusieurs images)', 'fnc-wordpress-theme' ),
			),
		)
	);

	// -- Mode image
	fnc_home_add_media(
		$wp_customize,
		'fnc_home_m1',
		'm1_image',
		__( 'Image de fond', 'fnc-wordpress-theme' ),
		'image',
		array(
			'active_callback' => static function () {
				return 'image' === fnc_home_hero_mode();
			},
		)
	);

	// -- Mode vidéo
	$video_active = static function () {
		return 'video' === fnc_home_hero_mode();
	};
	fnc_home_add_media(
		$wp_customize,
		'fnc_home_m1',
		'm1_video',
		__( 'Vidéo (fichier)', 'fnc-wordpress-theme' ),
		'video',
		array( 'active_callback' => $video_active )
	);
	fnc_home_add_field(
		$wp_customize,
		'fnc_home_m1',
		'm1_video_url',
		__( 'Vidéo (URL externe)', 'fnc-wordpress-theme' ),
		'url',
		'',
		array(
			'description'     => __( 'Utilisée si aucun fichier n’est choisi. Fichier vidéo direct (.mp4/.webm), pas une page YouTube.', 'fnc-wordpress-theme' ),
			'active_callback' => $video_active,
		)
	);
	fnc_home_add_media(
		$wp_customize,
		'fnc_home_m1',
		'm1_video_poster',
		__( 'Image d’attente (poster)', 'fnc-wordpress-theme' ),
		'image',
		array(
			'description'     => __( 'Affichée avant le chargement de la vidéo, et à la place de la vidéo si l’utilisateur a demandé de réduire les animations.', 'fnc-wordpress-theme' ),
			'active_callback' => $video_active,
		)
	);

	// -- Mode slider
	$slider_active = static function () {
		return 'slider' === fnc_home_hero_mode();
	};
	for ( $i = 1; $i <= 5; $i++ ) {
		fnc_home_add_media(
			$wp_customize,
			'fnc_home_m1',
			'm1_slide_' . $i,
			/* translators: %d: numéro de l'image du slider. */
			sprintf( __( 'Image %d du slider', 'fnc-wordpress-theme' ), $i ),
			'image',
			array( 'active_callback' => $slider_active )
		);
	}
	fnc_home_add_field(
		$wp_customize,
		'fnc_home_m1',
		'm1_slider_interval',
		__( 'Durée par image (secondes)', 'fnc-wordpress-theme' ),
		'number',
		6,
		array(
			'description'     => __( 'Le défilement est automatiquement désactivé si l’utilisateur a demandé de réduire les animations.', 'fnc-wordpress-theme' ),
			'input_attrs'     => array( 'min' => 3, 'max' => 20 ),
			'active_callback' => $slider_active,
		)
	);

	// -- Contenu éditorial M1
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_place', __( 'Lieu (kicker)', 'fnc-wordpress-theme' ), 'text', 'Brazzaville · République du Congo' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_dates', __( 'Dates (kicker)', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_title_line1', __( 'Titre — ligne 1', 'fnc-wordpress-theme' ), 'text', 'Forum' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_title_line2', __( 'Titre — ligne 2 (fine)', 'fnc-wordpress-theme' ), 'text', 'Numérique' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_title_line3', __( 'Titre — ligne 3', 'fnc-wordpress-theme' ), 'text', 'Congo' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_tagline', __( 'Accroche', 'fnc-wordpress-theme' ), 'textarea' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_cta_primary', __( 'CTA principal — libellé', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_cta_primary_url', __( 'CTA principal — lien', 'fnc-wordpress-theme' ), 'url' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_cta_secondary', __( 'CTA secondaire — libellé', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_cta_secondary_url', __( 'CTA secondaire — lien', 'fnc-wordpress-theme' ), 'url' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m1', 'm1_scroll', __( 'Indication de défilement', 'fnc-wordpress-theme' ), 'text', 'Défiler' );

	/* -------------------------------------------------- M2 */
	$wp_customize->add_section( 'fnc_home_m2', array( 'title' => __( 'M2 · Ce que nous sommes', 'fnc-wordpress-theme' ), 'panel' => 'fnc_homepage' ) );
	fnc_home_add_field( $wp_customize, 'fnc_home_m2', 'm2_eyebrow', __( 'Sur-titre', 'fnc-wordpress-theme' ), 'text', 'Ce que nous sommes' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m2', 'm2_line1', __( 'Ligne 1', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m2', 'm2_line2', __( 'Ligne 2', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m2', 'm2_line3', __( 'Ligne 3 (atténuée)', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m2', 'm2_link', __( 'Lien — libellé', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m2', 'm2_link_url', __( 'Lien — URL', 'fnc-wordpress-theme' ), 'url' );
	fnc_home_add_media( $wp_customize, 'fnc_home_m2', 'm2_image', __( 'Image', 'fnc-wordpress-theme' ) );

	/* -------------------------------------------------- M3 */
	$wp_customize->add_section(
		'fnc_home_m3',
		array(
			'title'       => __( 'M3 · Les voix', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_homepage',
			'description' => __( 'Les intervenants affichés proviennent des fiches Intervenants publiées. Tant qu’aucune n’existe, un état « à confirmer » est affiché.', 'fnc-wordpress-theme' ),
		)
	);
	fnc_home_add_field( $wp_customize, 'fnc_home_m3', 'm3_eyebrow', __( 'Sur-titre', 'fnc-wordpress-theme' ), 'text', 'Les voix' );
	fnc_home_add_field(
		$wp_customize,
		'fnc_home_m3',
		'm3_count',
		__( 'Nombre de vignettes', 'fnc-wordpress-theme' ),
		'number',
		5,
		array( 'input_attrs' => array( 'min' => 1, 'max' => 12 ) )
	);

	/* -------------------------------------------------- M4 */
	$wp_customize->add_section( 'fnc_home_m4', array( 'title' => __( 'M4 · Le territoire', 'fnc-wordpress-theme' ), 'panel' => 'fnc_homepage' ) );
	fnc_home_add_media( $wp_customize, 'fnc_home_m4', 'm4_image', __( 'Image de fond', 'fnc-wordpress-theme' ) );
	fnc_home_add_field( $wp_customize, 'fnc_home_m4', 'm4_eyebrow', __( 'Sur-titre', 'fnc-wordpress-theme' ), 'text', 'Le territoire' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m4', 'm4_manifest_strong', __( 'Manifeste — début', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m4', 'm4_manifest_light', __( 'Manifeste — accent', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m4', 'm4_sub', __( 'Sous-texte', 'fnc-wordpress-theme' ), 'textarea' );

	/* -------------------------------------------------- M5 */
	$wp_customize->add_section(
		'fnc_home_m5',
		array(
			'title'       => __( 'M5 · Programme', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_homepage',
			'description' => __( 'Les sessions affichées proviennent de l’édition en cours.', 'fnc-wordpress-theme' ),
		)
	);
	fnc_home_add_media( $wp_customize, 'fnc_home_m5', 'm5_image', __( 'Image', 'fnc-wordpress-theme' ) );
	fnc_home_add_field( $wp_customize, 'fnc_home_m5', 'm5_eyebrow', __( 'Sur-titre', 'fnc-wordpress-theme' ), 'text', 'Programme' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m5', 'm5_title', __( 'Titre', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m5', 'm5_date_label', __( 'Libellé de date', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m5', 'm5_link', __( 'Lien — libellé', 'fnc-wordpress-theme' ), 'text' );

	/* -------------------------------------------------- M6 */
	$wp_customize->add_section(
		'fnc_home_m6',
		array(
			'title'       => __( 'M6 · Partenaires', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_homepage',
			'description' => __( 'Les partenaires affichés proviennent des fiches Partenaires publiées.', 'fnc-wordpress-theme' ),
		)
	);
	fnc_home_add_field( $wp_customize, 'fnc_home_m6', 'm6_eyebrow', __( 'Sur-titre', 'fnc-wordpress-theme' ), 'text', 'La communauté' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m6', 'm6_title', __( 'Titre', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m6', 'm6_link', __( 'Lien — libellé', 'fnc-wordpress-theme' ), 'text' );

	/* -------------------------------------------------- M7 */
	$wp_customize->add_section(
		'fnc_home_m7',
		array(
			'title'       => __( 'M7 · Archives', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_homepage',
			'description' => __( 'Les éditions affichées proviennent des fiches Éditions publiées.', 'fnc-wordpress-theme' ),
		)
	);
	fnc_home_add_field( $wp_customize, 'fnc_home_m7', 'm7_eyebrow', __( 'Sur-titre', 'fnc-wordpress-theme' ), 'text', 'Les éditions' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m7', 'm7_title', __( 'Titre', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m7', 'm7_link', __( 'Lien — libellé', 'fnc-wordpress-theme' ), 'text' );

	/* -------------------------------------------------- M8 */
	$wp_customize->add_section( 'fnc_home_m8', array( 'title' => __( 'M8 · La décision', 'fnc-wordpress-theme' ), 'panel' => 'fnc_homepage' ) );
	fnc_home_add_media( $wp_customize, 'fnc_home_m8', 'm8_image', __( 'Image de fond', 'fnc-wordpress-theme' ) );
	fnc_home_add_field( $wp_customize, 'fnc_home_m8', 'm8_label', __( 'Label', 'fnc-wordpress-theme' ), 'text', 'La prochaine édition' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m8', 'm8_title_before', __( 'Titre — avant le compte', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m8', 'm8_title_after', __( 'Titre — après le compte', 'fnc-wordpress-theme' ), 'text', 'jours' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m8', 'm8_cta', __( 'CTA — libellé', 'fnc-wordpress-theme' ), 'text' );
	fnc_home_add_field( $wp_customize, 'fnc_home_m8', 'm8_cta_url', __( 'CTA — lien', 'fnc-wordpress-theme' ), 'url' );
}
add_action( 'customize_register', 'fnc_homepage_customize_register' );

/* ============================================================
   Rendu du média du héros (M1)
   ============================================================ */

/**
 * Rend le média de fond du héros selon le mode choisi.
 *
 * Accessibilité / performance :
 *  - la vidéo est muette, en boucle, `playsinline`, avec `poster` (évite le
 *    décalage de mise en page) ;
 *  - le slider et la vidéo sont neutralisés lorsque l'utilisateur a demandé de
 *    réduire les animations (`prefers-reduced-motion`) : seule la première
 *    image (ou le poster) reste affichée — voir assets/js/main.js et le CSS ;
 *  - le média étant décoratif (le texte porte le sens), `alt` reste vide et le
 *    conteneur est `aria-hidden`.
 */
function fnc_render_home_hero_media() {
	$mode     = fnc_home_hero_mode();
	$fallback = get_template_directory_uri() . '/assets/images/les-portes.png';

	if ( 'video' === $mode ) {
		$video  = fnc_home_media_url( 'm1_video' );
		$video  = $video ? $video : fnc_home_setting( 'm1_video_url', '' );
		$poster = fnc_home_media_url( 'm1_video_poster', $fallback );

		if ( $video ) {
			printf(
				'<video class="media-cover hero-video" autoplay muted loop playsinline preload="metadata" poster="%1$s" aria-hidden="true"><source src="%2$s" /></video>',
				esc_url( $poster ),
				esc_url( $video )
			);
			// Repli affiché si les animations sont réduites (CSS) : image seule.
			printf(
				'<img class="media-cover hero-video-fallback" src="%1$s" alt="" aria-hidden="true" />',
				esc_url( $poster )
			);
			return;
		}
		// Aucune vidéo renseignée : on retombe sur l'image.
		$mode = 'image';
	}

	if ( 'slider' === $mode ) {
		$slides = fnc_home_hero_slides();
		if ( count( $slides ) > 1 ) {
			$interval = (int) fnc_home_setting( 'm1_slider_interval', 6 );
			$interval = max( 3, min( 20, $interval ) );
			printf(
				'<div class="hero-slider" data-interval="%d" aria-hidden="true">',
				esc_attr( $interval * 1000 )
			);
			foreach ( $slides as $index => $url ) {
				printf(
					'<img class="media-cover hero-slide%1$s" src="%2$s" alt="" %3$s />',
					0 === $index ? ' is-active' : '',
					esc_url( $url ),
					0 === $index ? '' : 'loading="lazy"'
				);
			}
			echo '</div>';
			return;
		}
		// Moins de deux images : pas de slider, on affiche ce qui existe.
		if ( 1 === count( $slides ) ) {
			printf( '<img class="media-cover" src="%s" alt="" aria-hidden="true" />', esc_url( $slides[0] ) );
			return;
		}
		$mode = 'image';
	}

	// Mode image (ou repli).
	printf(
		'<img class="media-cover" src="%s" alt="" aria-hidden="true" />',
		esc_url( fnc_home_media_url( 'm1_image', $fallback ) )
	);
}
