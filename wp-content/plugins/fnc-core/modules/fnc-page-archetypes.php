<?php
/**
 * Plugin Name: FNC Core — Édition des pages
 * Description: Système d'archétypes de page (légale / institutionnelle / liste / détail /
 *              composable / accueil). Rend éditable sans développeur le HERO de chaque
 *              page (image, sur-titre, titre, introduction), la composition des pages
 *              institutionnelles (blocs curatés) et composables, et les états vides.
 * Version: 0.1.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INTÉGRATION : autonome OU à fusionner dans FNC Core. Enregistre ses champs via ACF
 * PRO par code (aucun clic UI). Le RENDU (classes DA-D) reste au thème : ce module ne
 * fournit que les DONNÉES via des accesseurs normalisés.
 *
 * DÉGRADATION : sans ACF Pro, les accesseurs retombent sur post_content (legal) et sur
 * les DÉFAUTS de route (image + libellés i18n) → aucune page cassée, mais plus d'édition
 * fine tant qu'ACF n'est pas actif.
 *
 * FRONTIÈRE DA : les champs portent le CONTENU, les MÉDIAS, les CTA et l'ORDRE des blocs
 * curatés — jamais la structure, les couleurs ni les classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'FNC_ARCHETYPES' ) ) {
	define( 'FNC_ARCHETYPES', array( 'legal', 'institutional', 'list', 'detail', 'generic', 'homepage' ) );
}

/* ==========================================================================
 * Détection de langue (partagée ; guardée pour cohabiter avec les autres modules)
 * ======================================================================== */

if ( ! function_exists( 'fnc_is_en' ) ) {
	function fnc_is_en() {
		return function_exists( 'pll_current_language' )
			? ( 'en' === pll_current_language() )
			: ( 0 === strpos( get_locale(), 'en' ) );
	}
}

/* ==========================================================================
 * Défauts de HERO par route (image + eyebrow + titre) — miroir du site réel.
 * C'est ce qui rend le hero CORRECT même sans aucune saisie CMS.
 * ======================================================================== */

if ( ! function_exists( 'fnc_hero_image_base' ) ) {
	/** Préfixe d'URL des images par défaut. Le thème DOIT filtrer vers son dossier d'assets. */
	function fnc_hero_image_base() {
		return trailingslashit( (string) apply_filters( 'fnc_hero_image_base_url', '' ) );
	}
}

if ( ! function_exists( 'fnc_page_hero_defaults' ) ) {
	/**
	 * Table route → défauts hero. Retour : ['image'=>url, 'eyebrow'=>str, 'title'=>str]
	 * dans la langue courante. Filtrable via 'fnc_page_hero_defaults'.
	 */
	function fnc_page_hero_defaults( $route ) {
		$route = trim( (string) $route, '/' );
		$en    = fnc_is_en();

		// [ fichier, eyebrow_fr, eyebrow_en, titre_fr, titre_en ]
		$map = array(
			'intervenants'              => array( 'intervenants.png', 'Intervenants', 'Speakers', 'Les voix du Forum', 'The voices of the Forum' ),
			'programme'                 => array( 'programme.png', 'Programme', 'Programme', 'Deux jours de dialogue', 'Two days of dialogue' ),
			'ressources'                => array( 'les-publications.png', 'Ressources', 'Resources', 'Ressources', 'Resources' ),
			'partenaires'               => array( 'la-main.png', 'Partenaires', 'Partners', 'Partenaires', 'Partners' ),
			'editions'                  => array( 'editions.png', 'Éditions', 'Editions', 'Éditions', 'Editions' ),
			'edition-en-cours'          => array( 'edition-en-cours.jpeg', 'Édition en cours', 'Current edition', 'Édition 2027 — Forum Numérique Congo', '2027 Edition — Forum Numérique Congo' ),
			'actualites'                => array( 'le-fleuve.png', 'Actualités', 'News', 'Actualités', 'News' ),
			'espace-presse'             => array( 'les-publications.png', 'Presse', 'Press', 'Espace presse', 'Press area' ),
			'infos-pratiques'           => array( 'le-badge.png', 'Préparez votre venue', 'Prepare your visit', 'Informations pratiques', 'Practical information' ),
			'inscription'               => array( 'le-badge.png', 'Participer', 'Take part', 'Inscription', 'Registration' ),
			'le-forum/mot-du-president' => array( 'le-pupitre.png', '', '', 'Le mot du président', 'A word from the President' ),
		);

		$row = isset( $map[ $route ] ) ? $map[ $route ] : array( '', '', '', '', '' );
		$out = array(
			'image'   => $row[0] ? fnc_hero_image_base() . $row[0] : '',
			'eyebrow' => $en ? $row[2] : $row[1],
			'title'   => $en ? $row[4] : $row[3],
		);
		return apply_filters( 'fnc_page_hero_defaults', $out, $route, $en );
	}
}

/* ==========================================================================
 * Accès ACF sûr (dégrade proprement sans ACF)
 * ======================================================================== */

if ( ! function_exists( 'fnc_acf_get' ) ) {
	/** get_field() si ACF présent, sinon $default. */
	function fnc_acf_get( $selector, $post_id, $default = null ) {
		if ( function_exists( 'get_field' ) ) {
			$v = get_field( $selector, $post_id );
			return ( null === $v || false === $v ) ? $default : $v;
		}
		return $default;
	}
}

/* ==========================================================================
 * Accesseurs publics (API stable pour le thème)
 * ======================================================================== */

if ( ! function_exists( 'fnc_page_archetype' ) ) {
	/** Archétype de la page : legal|institutional|list|detail|generic|homepage. */
	function fnc_page_archetype( $post_id = 0 ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$a       = fnc_acf_get( 'fnc_archetype', $post_id, get_post_meta( $post_id, 'fnc_archetype', true ) );
		$a       = is_string( $a ) ? $a : '';
		return in_array( $a, FNC_ARCHETYPES, true ) ? $a : 'legal';
	}
}

if ( ! function_exists( 'fnc_page_for_route' ) ) {
	/** Page WP dont le slug = $route (Polylang-aware), ou null. */
	function fnc_page_for_route( $route ) {
		$page = get_page_by_path( trim( (string) $route, '/' ) );
		if ( $page && function_exists( 'pll_get_post' ) ) {
			$tr = pll_get_post( $page->ID );
			if ( $tr ) {
				$page = get_post( $tr );
			}
		}
		return $page ? $page : null;
	}
}

if ( ! function_exists( 'fnc_page_hero' ) ) {
	/**
	 * Hero normalisé : override CMS (archétype list) SINON défaut de route.
	 * @return array{image:string,eyebrow:string,title:string,intro:string}
	 */
	function fnc_page_hero( $post_id = 0, $route = '' ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$d       = fnc_page_hero_defaults( $route );
		$list    = (array) fnc_acf_get( 'list', $post_id, array() );

		// Image : override média → sinon défaut de route.
		$img = '';
		if ( isset( $list['hero_image'] ) ) {
			$hi = $list['hero_image'];
			if ( is_array( $hi ) && ! empty( $hi['url'] ) ) {
				$img = $hi['url'];
			} elseif ( is_numeric( $hi ) && (int) $hi ) {
				$img = (string) wp_get_attachment_image_url( (int) $hi, 'full' );
			}
		}
		if ( '' === $img ) {
			$img = $d['image'];
		}

		$pick = function ( $key, $default ) use ( $list ) {
			$v = isset( $list[ $key ] ) ? trim( (string) $list[ $key ] ) : '';
			return '' !== $v ? $v : $default;
		};

		return array(
			'image'   => $img,
			'eyebrow' => $pick( 'hero_eyebrow', $d['eyebrow'] ),
			'title'   => $pick( 'hero_title', $d['title'] ),
			'intro'   => $pick( 'hero_intro', '' ),
		);
	}
}

if ( ! function_exists( 'fnc_page_list_meta' ) ) {
	/** Contenu éditorial d'une page-liste : bloc intro + état vide. */
	function fnc_page_list_meta( $post_id = 0 ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$l       = (array) fnc_acf_get( 'list', $post_id, array() );
		$g       = function ( $k ) use ( $l ) {
			return isset( $l[ $k ] ) ? (string) $l[ $k ] : '';
		};
		return array(
			'intro' => array(
				'eyebrow' => $g( 'intro_eyebrow' ),
				'title'   => $g( 'intro_title' ),
				'body'    => $g( 'intro_body' ),
				'status'  => $g( 'intro_status' ),
			),
			'empty' => array(
				'eyebrow' => $g( 'empty_eyebrow' ),
				'title'   => $g( 'empty_title' ),
				'body'    => $g( 'empty_body' ),
			),
		);
	}
}

if ( ! function_exists( 'fnc_page_detail_meta' ) ) {
	/** Section complémentaire commune (archétype detail). */
	function fnc_page_detail_meta( $post_id = 0 ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$d       = (array) fnc_acf_get( 'detail', $post_id, array() );
		$g       = function ( $k ) use ( $d ) {
			return isset( $d[ $k ] ) ? (string) $d[ $k ] : '';
		};
		return array(
			'eyebrow'  => $g( 'complement_eyebrow' ),
			'title'    => $g( 'complement_title' ),
			'body'     => $g( 'complement_body' ),
			'ctaLabel' => $g( 'complement_cta_label' ),
			'ctaHref'  => $g( 'complement_cta_href' ),
		);
	}
}

if ( ! function_exists( 'fnc_page_sections' ) ) {
	/**
	 * Blocs d'une page composée : $kind = 'institutional' | 'generic'.
	 * Chaque bloc : ['type' => slug, …champs]. Vide si ACF absent.
	 * @return array[]
	 */
	function fnc_page_sections( $post_id = 0, $kind = 'institutional' ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$field   = ( 'generic' === $kind ) ? 'sections' : 'institutional_sections';
		$rows    = fnc_acf_get( $field, $post_id, array() );
		if ( ! is_array( $rows ) ) {
			return array();
		}
		$out = array();
		foreach ( $rows as $r ) {
			if ( ! is_array( $r ) || empty( $r['acf_fc_layout'] ) ) {
				continue;
			}
			$r['type'] = $r['acf_fc_layout'];
			unset( $r['acf_fc_layout'] );
			$out[] = $r;
		}
		return $out;
	}
}

/* ==========================================================================
 * Enregistrement des champs (par code)
 * ======================================================================== */

if ( ! function_exists( 'fnc_g_field' ) ) {
	/** Fabrique un champ ACF (clé scopée par $prefix → unicité globale). */
	function fnc_g_field( $prefix, $type, $name, $label, $extra = array() ) {
		return array_merge(
			array( 'key' => 'field_fncg_' . $prefix . '_' . $name, 'name' => $name, 'label' => $label, 'type' => $type ),
			$extra
		);
	}
}

if ( ! function_exists( 'fnc_g_text' ) ) {
	function fnc_g_text( $p, $n, $l, $req = 0 ) {
		return fnc_g_field( $p, 'text', $n, $l, array( 'required' => $req ) );
	}
	function fnc_g_area( $p, $n, $l ) {
		return fnc_g_field( $p, 'textarea', $n, $l, array( 'rows' => 3 ) );
	}
	function fnc_g_img( $p, $n, $l ) {
		return fnc_g_field( $p, 'image', $n, $l, array( 'return_format' => 'array', 'preview_size' => 'medium' ) );
	}
	function fnc_g_wysiwyg( $p, $n, $l ) {
		return fnc_g_field( $p, 'wysiwyg', $n, $l, array( 'media_upload' => 0, 'toolbar' => 'basic' ) );
	}
	/** Repeater simple. $subs = tableau de champs déjà fabriqués. */
	function fnc_g_repeater( $p, $n, $l, $subs ) {
		return fnc_g_field( $p, 'repeater', $n, $l, array( 'layout' => 'block', 'sub_fields' => $subs ) );
	}
	/** Layout de Flexible Content. */
	function fnc_g_layout( $slug, $label, $fields ) {
		return array( 'key' => 'layout_fncg_' . $slug, 'name' => $slug, 'label' => $label, 'display' => 'block', 'sub_fields' => $fields );
	}
	/** Condition « archétype == $value ». */
	function fnc_g_when( $value ) {
		return array( array( array( 'field' => 'field_fncg_archetype', 'operator' => '==', 'value' => $value ) ) );
	}
}

if ( ! function_exists( 'fnc_g_register' ) ) {
	function fnc_g_register() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return; // ACF absent → dégradation (voir en-tête).
		}

		$location = array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ) );

		/* --- 1. Sélecteur d'archétype (colonne latérale) --- */
		acf_add_local_field_group( array(
			'key'      => 'group_fncg_type',
			'title'    => 'FNC — Type de page',
			'location' => $location,
			'position' => 'side',
			'menu_order' => -10,
			'fields'   => array(
				array(
					'key'           => 'field_fncg_archetype',
					'name'          => 'fnc_archetype',
					'label'         => 'Archétype de page',
					'type'          => 'radio',
					'default_value' => 'legal',
					'instructions'  => 'Détermine les blocs éditoriaux disponibles. La mise en forme (DA) est figée.',
					'choices'       => array(
						'legal'         => 'Page légale (contenu simple)',
						'institutional' => 'Page institutionnelle',
						'list'          => 'Page liste (héros + intro + état vide)',
						'detail'        => 'Page détail (section complément)',
						'generic'       => 'Page composable (blocs bornés)',
						'homepage'      => 'Homepage (storyboard figé)',
					),
				),
			),
		) );

		/* --- 2. Champs conditionnels par archétype --- */
		$list_fields = array(
			fnc_g_img( 'list', 'hero_image', 'Héros — image (sinon défaut de la route)' ),
			fnc_g_text( 'list', 'hero_eyebrow', 'Héros — sur-titre' ),
			fnc_g_text( 'list', 'hero_title', 'Héros — titre' ),
			fnc_g_area( 'list', 'hero_intro', 'Héros — introduction' ),
			fnc_g_text( 'list', 'intro_eyebrow', 'Intro — sur-titre' ),
			fnc_g_text( 'list', 'intro_title', 'Intro — titre' ),
			fnc_g_area( 'list', 'intro_body', 'Intro — texte' ),
			fnc_g_area( 'list', 'intro_status', 'Intro — mention de statut' ),
			fnc_g_text( 'list', 'empty_eyebrow', 'État vide — sur-titre' ),
			fnc_g_text( 'list', 'empty_title', 'État vide — titre' ),
			fnc_g_area( 'list', 'empty_body', 'État vide — texte' ),
		);
		$detail_fields = array(
			fnc_g_text( 'detail', 'complement_eyebrow', 'Complément — sur-titre' ),
			fnc_g_text( 'detail', 'complement_title', 'Complément — titre' ),
			fnc_g_area( 'detail', 'complement_body', 'Complément — texte' ),
			fnc_g_text( 'detail', 'complement_cta_label', 'Complément — libellé du CTA' ),
			fnc_g_text( 'detail', 'complement_cta_href', 'Complément — lien du CTA' ),
		);

		// Palette institutionnelle (7 blocs curatés).
		$institutional = array(
			fnc_g_layout( 'instHero', 'Ouverture', array(
				fnc_g_img( 'ihero', 'image', 'Image d’ouverture' ),
				fnc_g_text( 'ihero', 'breadcrumb', 'Fil d’Ariane (libellé page)' ),
				fnc_g_text( 'ihero', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'ihero', 'title_a', 'Titre — ligne 1', 1 ),
				fnc_g_text( 'ihero', 'title_b', 'Titre — ligne 2' ),
				fnc_g_area( 'ihero', 'intro', 'Introduction' ),
			) ),
			fnc_g_layout( 'instPresident', 'Mot du Président', array(
				fnc_g_text( 'ipres', 'eyebrow', 'Sur-titre' ),
				fnc_g_img( 'ipres', 'photo', 'Portrait du Président' ),
				fnc_g_text( 'ipres', 'name', 'Nom' ),
				fnc_g_text( 'ipres', 'role', 'Fonction' ),
				fnc_g_area( 'ipres', 'excerpt', 'Extrait (page « Le Forum »)' ),
				fnc_g_wysiwyg( 'ipres', 'message', 'Mot complet (page de lecture)' ),
				fnc_g_text( 'ipres', 'cta_label', 'Libellé du lien « lire tout »' ),
			) ),
			fnc_g_layout( 'instSplit', 'Mission (média + texte)', array(
				fnc_g_text( 'isplit', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'isplit', 'l1', 'Titre — ligne 1' ),
				fnc_g_text( 'isplit', 'l2', 'Titre — ligne 2' ),
				fnc_g_text( 'isplit', 'l3', 'Titre — ligne 3 (atténuée)' ),
				fnc_g_area( 'isplit', 'body', 'Texte' ),
				fnc_g_img( 'isplit', 'image', 'Image' ),
			) ),
			fnc_g_layout( 'instObjectives', 'Objectifs (séquence numérotée)', array(
				fnc_g_text( 'iobj', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'iobj', 'title', 'Titre' ),
				fnc_g_repeater( 'iobj', 'items', 'Objectifs', array(
					fnc_g_text( 'iobji', 't', 'Titre', 1 ),
					fnc_g_area( 'iobji', 'd', 'Description' ),
				) ),
			) ),
			fnc_g_layout( 'instFaq', 'FAQ', array(
				fnc_g_text( 'ifaq', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'ifaq', 'title', 'Titre' ),
				fnc_g_area( 'ifaq', 'intro', 'Introduction' ),
				fnc_g_repeater( 'ifaq', 'items', 'Questions', array(
					fnc_g_text( 'ifaqi', 'question', 'Question', 1 ),
					fnc_g_area( 'ifaqi', 'answer', 'Réponse' ),
				) ),
			) ),
			fnc_g_layout( 'instManifesto', 'Ancrage (manifeste)', array(
				fnc_g_img( 'iman', 'image', 'Image pleine largeur' ),
				fnc_g_text( 'iman', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'iman', 'manifest_a', 'Manifeste — début' ),
				fnc_g_text( 'iman', 'manifest_b', 'Manifeste — accent' ),
				fnc_g_area( 'iman', 'sub', 'Sous-texte' ),
			) ),
			fnc_g_layout( 'instCallout', 'Callout (CTA)', array(
				fnc_g_text( 'ical', 'title', 'Titre' ),
				fnc_g_area( 'ical', 'desc', 'Texte' ),
				fnc_g_text( 'ical', 'cta_label', 'Libellé du bouton' ),
				fnc_g_text( 'ical', 'cta_href', 'Lien du bouton' ),
			) ),
		);

		// Palette générique (bornée) — phase 2.
		$generic = array(
			fnc_g_layout( 'hero', 'Héros', array(
				fnc_g_text( 'ghero', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'ghero', 'title', 'Titre', 1 ),
				fnc_g_text( 'ghero', 'subtitle', 'Sous-titre' ),
				fnc_g_area( 'ghero', 'body', 'Texte' ),
				fnc_g_img( 'ghero', 'image', 'Image de fond' ),
				fnc_g_text( 'ghero', 'video_url', 'URL vidéo (optionnel)' ),
				fnc_g_text( 'ghero', 'cta_label', 'Libellé du bouton' ),
				fnc_g_text( 'ghero', 'cta_href', 'Lien du bouton' ),
			) ),
			fnc_g_layout( 'richText', 'Texte riche', array(
				fnc_g_text( 'grt', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'grt', 'title', 'Titre' ),
				fnc_g_wysiwyg( 'grt', 'content', 'Contenu' ),
			) ),
			fnc_g_layout( 'split', 'Média + texte', array(
				fnc_g_text( 'gsplit', 'eyebrow', 'Sur-titre' ),
				fnc_g_text( 'gsplit', 'title', 'Titre' ),
				fnc_g_wysiwyg( 'gsplit', 'body', 'Texte' ),
				fnc_g_img( 'gsplit', 'image', 'Image' ),
				fnc_g_field( 'gsplit', 'select', 'media_side', 'Côté de l’image', array(
					'choices' => array( 'right' => 'Droite', 'left' => 'Gauche' ),
					'default_value' => 'right',
				) ),
				fnc_g_text( 'gsplit', 'cta_label', 'Libellé du bouton' ),
				fnc_g_text( 'gsplit', 'cta_href', 'Lien du bouton' ),
			) ),
			fnc_g_layout( 'stats', 'Chiffres clés', array(
				fnc_g_text( 'gstat', 'title', 'Titre' ),
				fnc_g_repeater( 'gstat', 'items', 'Chiffres', array(
					fnc_g_text( 'gstati', 'value', 'Valeur', 1 ),
					fnc_g_text( 'gstati', 'label', 'Libellé' ),
				) ),
			) ),
			fnc_g_layout( 'cta', 'Appel à action', array(
				fnc_g_text( 'gcta', 'title', 'Titre' ),
				fnc_g_area( 'gcta', 'text', 'Texte' ),
				fnc_g_text( 'gcta', 'cta_label', 'Libellé du bouton' ),
				fnc_g_text( 'gcta', 'cta_href', 'Lien du bouton' ),
			) ),
			fnc_g_layout( 'faq', 'FAQ', array(
				fnc_g_text( 'gfaq', 'title', 'Titre' ),
				fnc_g_repeater( 'gfaq', 'items', 'Questions', array(
					fnc_g_text( 'gfaqi', 'question', 'Question', 1 ),
					fnc_g_area( 'gfaqi', 'answer', 'Réponse' ),
				) ),
			) ),
			fnc_g_layout( 'documents', 'Documents', array(
				fnc_g_text( 'gdoc', 'title', 'Titre' ),
				fnc_g_repeater( 'gdoc', 'items', 'Fichiers', array(
					fnc_g_text( 'gdoci', 'label', 'Libellé', 1 ),
					fnc_g_field( 'gdoci', 'file', 'file', 'Fichier', array( 'return_format' => 'array' ) ),
				) ),
			) ),
		);

		acf_add_local_field_group( array(
			'key'      => 'group_fncg_content',
			'title'    => 'FNC — Contenu de page',
			'location' => $location,
			'fields'   => array(
				fnc_g_field( 'wrap', 'group', 'list', 'Page liste (contenu éditorial)', array(
					'layout' => 'block', 'conditional_logic' => fnc_g_when( 'list' ), 'sub_fields' => $list_fields,
				) ),
				fnc_g_field( 'wrap', 'group', 'detail', 'Page détail (section complément)', array(
					'layout' => 'block', 'conditional_logic' => fnc_g_when( 'detail' ), 'sub_fields' => $detail_fields,
				) ),
				fnc_g_field( 'wrap', 'flexible_content', 'institutional_sections', 'Sections (page institutionnelle)', array(
					'conditional_logic' => fnc_g_when( 'institutional' ), 'button_label' => 'Ajouter une section', 'layouts' => $institutional,
				) ),
				fnc_g_field( 'wrap', 'flexible_content', 'sections', 'Sections (page composable)', array(
					'conditional_logic' => fnc_g_when( 'generic' ), 'button_label' => 'Ajouter un bloc', 'layouts' => $generic,
				) ),
			),
		) );
	}
	add_action( 'acf/init', 'fnc_g_register' );
}

/* ==========================================================================
 * NOTES D'INTÉGRATION (thème) — le rendu reste au thème
 *
 *  • Base des images par défaut (une fois, dans le thème) :
 *      add_filter( 'fnc_hero_image_base_url', function () {
 *        return get_template_directory_uri() . '/assets/img/da';
 *      } );
 *
 *  • Hero d'une page-liste (template d'archive/route) :
 *      $page = fnc_page_for_route( 'intervenants' );
 *      $h = fnc_page_hero( $page ? $page->ID : 0, 'intervenants' );
 *      // <img class="media-cover" src="<?= esc_url($h['image']) ?>">
 *      // <span class="eyebrow"><?= esc_html($h['eyebrow']) ?></span>
 *      // <h1><?= esc_html($h['title']) ?></h1>
 *
 *  • Page institutionnelle (le-forum, contact) :
 *      foreach ( fnc_page_sections( get_the_ID(), 'institutional' ) as $b ) {
 *        get_template_part( 'template-parts/blocks/inst-' . $b['type'], null, $b );
 *      }
 *
 *  • États vides / intro : fnc_page_list_meta() ; complément détail : fnc_page_detail_meta().
 * ======================================================================== */
