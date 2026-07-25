<?php
/**
 * Forum Numérique Congo — titres de section éditables des pages à liste.
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
 * Registre : route => { label, sections: { cle => { label, eyebrow, title } } }.
 * Une valeur `eyebrow`/`title` absente = pas de champ (ex. CTA sans surtitre,
 * ou titre dynamique).
 */
function fnc_section_registry() {
	return array(
		'ressources'     => array(
			'label'    => __( 'Ressources', 'fnc-wordpress-theme' ),
			'sections' => array(
				'intro'  => array( 'label' => __( 'Introduction', 'fnc-wordpress-theme' ), 'eyebrow' => 'Lecture', 'title' => 'Lire, comprendre, prolonger.' ),
				'search' => array( 'label' => __( 'Recherche', 'fnc-wordpress-theme' ), 'eyebrow' => 'Bibliothèque', 'title' => 'Rechercher, filtrer, consulter.' ),
				'cta'    => array( 'label' => __( 'Appel final', 'fnc-wordpress-theme' ), 'eyebrow' => 'Suite', 'title' => 'Lire les traces, puis comprendre le cadre.' ),
			),
		),
		'editions'       => array(
			'label'    => __( 'Éditions', 'fnc-wordpress-theme' ),
			'sections' => array(
				'intro' => array( 'label' => __( 'Introduction', 'fnc-wordpress-theme' ), 'eyebrow' => 'Archives', 'title' => 'Une histoire qui s’écrit, édition après édition.' ),
				'list'  => array( 'label' => __( 'Liste', 'fnc-wordpress-theme' ), 'eyebrow' => 'La liste', 'title' => 'Les éditions publiées' ),
				'cta'   => array( 'label' => __( 'Appel final', 'fnc-wordpress-theme' ), 'eyebrow' => 'Suite', 'title' => 'De l’édition à la ressource.' ),
			),
		),
		'actualites'     => array(
			'label'    => __( 'Actualités', 'fnc-wordpress-theme' ),
			'sections' => array(
				'list' => array( 'label' => __( 'Liste', 'fnc-wordpress-theme' ), 'eyebrow' => 'Actualités', 'title' => 'Dernières publications.' ),
			),
		),
		'partenaires'    => array(
			'label'    => __( 'Partenaires', 'fnc-wordpress-theme' ),
			'sections' => array(
				'why'       => array( 'label' => __( 'Pourquoi', 'fnc-wordpress-theme' ), 'eyebrow' => 'S’associer', 'title' => 'Pourquoi devenir partenaire.' ),
				'types'     => array( 'label' => __( 'Types & niveaux', 'fnc-wordpress-theme' ), 'eyebrow' => 'Cadre', 'title' => 'Types et niveaux de partenariat.' ),
				'confirmed' => array( 'label' => __( 'Mur de partenaires', 'fnc-wordpress-theme' ), 'eyebrow' => 'Ils nous font confiance', 'title' => 'Partenaires confirmés.' ),
				'cta'       => array( 'label' => __( 'Échanger', 'fnc-wordpress-theme' ), 'eyebrow' => 'Échanger', 'title' => 'Discutons d’un partenariat.' ),
			),
		),
		'espace-presse'  => array(
			'label'    => __( 'Espace presse', 'fnc-wordpress-theme' ),
			'sections' => array(
				'intro'         => array( 'label' => __( 'Introduction', 'fnc-wordpress-theme' ), 'eyebrow' => 'Information', 'title' => 'Des ressources vérifiables, pas un dossier de façade.' ),
				'resources'     => array( 'label' => __( 'Ressources presse', 'fnc-wordpress-theme' ), 'eyebrow' => 'Communiqués', 'title' => 'Ressources presse publiées.' ),
				'contacts'      => array( 'label' => __( 'Contacts', 'fnc-wordpress-theme' ), 'eyebrow' => 'Contacts', 'title' => 'Contacts presse confirmés.' ),
				'accreditation' => array( 'label' => __( 'Accréditation', 'fnc-wordpress-theme' ), 'eyebrow' => 'Accréditation', 'title' => 'L’accréditation reste un échange direct.' ),
			),
		),
		'intervenants'   => array(
			'label'    => __( 'Intervenants', 'fnc-wordpress-theme' ),
			'sections' => array(
				'cta' => array( 'label' => __( 'Appel final', 'fnc-wordpress-theme' ), 'title' => 'Proposer une intervention' ),
			),
		),
		'programme'      => array(
			'label'    => __( 'Programme', 'fnc-wordpress-theme' ),
			'sections' => array(
				'cta' => array( 'label' => __( 'Appel final', 'fnc-wordpress-theme' ), 'title' => 'Réservez votre place' ),
			),
		),
		'infos-pratiques' => array(
			'label'    => __( 'Informations pratiques', 'fnc-wordpress-theme' ),
			'sections' => array(
				'onsite' => array( 'label' => __( 'Sur place', 'fnc-wordpress-theme' ), 'eyebrow' => 'Sur place' ),
				'cta'    => array( 'label' => __( 'Appel final', 'fnc-wordpress-theme' ), 'title' => 'Prêt à participer ?' ),
			),
		),
		'edition-en-cours' => array(
			'label'    => __( 'Édition en cours', 'fnc-wordpress-theme' ),
			'sections' => array(
				'programme'    => array( 'label' => __( 'Aperçu programme', 'fnc-wordpress-theme' ), 'eyebrow' => 'Le programme', 'title' => 'Un aperçu des prochaines sessions.' ),
				'intervenants' => array( 'label' => __( 'Aperçu intervenants', 'fnc-wordpress-theme' ), 'eyebrow' => 'Les intervenants', 'title' => 'Décideurs, experts et acteurs de la société civile.' ),
				'inscription'  => array( 'label' => __( 'Appel inscription', 'fnc-wordpress-theme' ), 'title' => 'Inscription' ),
			),
		),
	);
}

/**
 * Valeur d'un surtitre/titre de section : surcharge sinon defaut du registre.
 *
 * @param string $route
 * @param string $key   Cle de section.
 * @param string $field eyebrow|title
 * @return string
 */
function fnc_stitle( $route, $key, $field ) {
	$reg     = fnc_section_registry();
	$default = isset( $reg[ $route ]['sections'][ $key ][ $field ] ) ? $reg[ $route ]['sections'][ $key ][ $field ] : '';
	$value   = (string) get_theme_mod( "fnc_st_{$route}_{$key}_{$field}", '' );
	return '' !== trim( $value ) ? $value : $default;
}

/**
 * Customizer : panneau « Titres de section », une section par route, deux
 * controles (sur-titre, titre) par bande interne. Controles natifs.
 */
function fnc_section_titles_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'fnc_sections',
		array(
			'title'       => __( 'Titres de section', 'fnc-wordpress-theme' ),
			'description' => __( 'Sur-titre et titre des bandes internes des pages à liste (introduction, recherche, listes, appels à action). Laisser vide = valeur par défaut.', 'fnc-wordpress-theme' ),
			'priority'    => 31,
		)
	);

	foreach ( fnc_section_registry() as $route => $def ) {
		$section = "fnc_st_{$route}";
		$wp_customize->add_section(
			$section,
			array(
				'title' => $def['label'],
				'panel' => 'fnc_sections',
			)
		);

		foreach ( $def['sections'] as $key => $sdef ) {
			foreach ( array( 'eyebrow' => __( 'Sur-titre', 'fnc-wordpress-theme' ), 'title' => __( 'Titre', 'fnc-wordpress-theme' ) ) as $field => $field_label ) {
				if ( ! isset( $sdef[ $field ] ) ) {
					continue; // pas de surtitre (CTA) ou titre dynamique.
				}
				$setting = "fnc_st_{$route}_{$key}_{$field}";
				$wp_customize->add_setting(
					$setting,
					array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
						'transport'         => 'postMessage',
					)
				);
				$wp_customize->add_control(
					$setting,
					array(
						'section'     => $section,
						'label'       => $sdef['label'] . ' · ' . $field_label,
						'description' => sprintf(
							/* translators: %s: valeur par defaut. */
							__( 'Par défaut : « %s »', 'fnc-wordpress-theme' ),
							$sdef[ $field ]
						),
						'type'        => 'text',
					)
				);
				// Apercu en direct : le titre se met a jour sans rechargement
				// (attribut data-fnc-st ajoute sur chaque en-tete).
				if ( isset( $wp_customize->selective_refresh ) ) {
					$wp_customize->selective_refresh->add_partial(
						$setting,
						array(
							'selector'        => '[data-fnc-st="' . $route . '.' . $key . '.' . $field . '"]',
							'render_callback' => function () use ( $route, $key, $field ) {
								return esc_html( fnc_stitle( $route, $key, $field ) );
							},
						)
					);
				}
			}
		}
	}
}
add_action( 'customize_register', 'fnc_section_titles_customize_register' );
