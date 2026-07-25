<?php
/**
 * Forum Numérique Congo — rendu des sections composées des pages.
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'fnc_acf_img_id' ) ) {
	/**
	 * ID d'attachement depuis un champ image ACF, quel que soit son format
	 * de retour (array | ID | URL numérique).
	 *
	 * @param mixed $value Valeur brute du champ image ACF.
	 * @return int
	 */
	function fnc_acf_img_id( $value ) {
		if ( is_array( $value ) ) {
			if ( isset( $value['ID'] ) ) {
				return (int) $value['ID'];
			}
			return isset( $value['id'] ) ? (int) $value['id'] : 0;
		}
		return is_numeric( $value ) ? (int) $value : 0;
	}
}

if ( ! function_exists( 'fnc_page_has_sections' ) ) {
	/**
	 * Vrai si la page porte au moins une section composée (édition des pages).
	 *
	 * @param int    $post_id Identifiant de page (0 = page courante).
	 * @param string $kind    'institutional' | 'generic'.
	 * @return bool
	 */
	function fnc_page_has_sections( $post_id = 0, $kind = 'institutional' ) {
		if ( ! function_exists( 'fnc_page_sections' ) ) {
			return false;
		}
		$rows = fnc_page_sections( $post_id, $kind );
		return ! empty( $rows );
	}
}

if ( ! function_exists( 'fnc_render_section_row' ) ) {
	/**
	 * Adapte une ligne de Flexible Content ACF vers la fonction de rendu de
	 * bloc correspondante. Les lectures passent par fnc_attr() (même contrat
	 * que les blocs Gutenberg) ; les images ACF sont normalisées en ID.
	 *
	 * @param string $type Slug de layout ACF (acf_fc_layout).
	 * @param array  $r    Ligne de données.
	 * @return string HTML.
	 */
	function fnc_render_section_row( $type, $r ) {
		switch ( $type ) {

			/* ---------------- Palette institutionnelle ---------------- */

			case 'instHero':
				return fnc_render_block_inst_hero(
					array(
						'image'      => fnc_acf_img_id( fnc_attr( $r, 'image', 0 ) ),
						'breadcrumb' => fnc_attr( $r, 'breadcrumb' ),
						'eyebrow'    => fnc_attr( $r, 'eyebrow' ),
						'titleA'     => fnc_attr( $r, 'title_a' ),
						'titleB'     => fnc_attr( $r, 'title_b' ),
						'intro'      => fnc_attr( $r, 'intro' ),
					)
				);

			case 'instPresident':
				$pres_href = fnc_attr( $r, 'cta_href' );
				if ( '' === $pres_href && function_exists( 'fnc_page_url' ) ) {
					$pres_href = fnc_page_url( 'le-forum/mot-du-president' );
				}
				return fnc_render_block_inst_president(
					array(
						'eyebrow'  => fnc_attr( $r, 'eyebrow' ),
						'photo'    => fnc_acf_img_id( fnc_attr( $r, 'photo', 0 ) ),
						'name'     => fnc_attr( $r, 'name' ),
						'role'     => fnc_attr( $r, 'role' ),
						'excerpt'  => fnc_attr( $r, 'excerpt' ),
						'message'  => fnc_attr( $r, 'message' ),
						'ctaLabel' => fnc_attr( $r, 'cta_label' ),
						'ctaHref'  => $pres_href,
					)
				);

			case 'instSplit':
				return fnc_render_block_inst_split(
					array(
						'eyebrow' => fnc_attr( $r, 'eyebrow' ),
						'l1'      => fnc_attr( $r, 'l1' ),
						'l2'      => fnc_attr( $r, 'l2' ),
						'l3'      => fnc_attr( $r, 'l3' ),
						'body'    => fnc_attr( $r, 'body' ),
						'image'   => fnc_acf_img_id( fnc_attr( $r, 'image', 0 ) ),
					)
				);

			case 'instObjectives':
				return fnc_render_block_inst_objectives(
					array(
						'eyebrow' => fnc_attr( $r, 'eyebrow' ),
						'title'   => fnc_attr( $r, 'title' ),
						'items'   => is_array( fnc_attr( $r, 'items', array() ) ) ? fnc_attr( $r, 'items', array() ) : array(),
					)
				);

			case 'instFaq':
				return fnc_render_block_inst_faq(
					array(
						'eyebrow' => fnc_attr( $r, 'eyebrow' ),
						'title'   => fnc_attr( $r, 'title' ),
						'intro'   => fnc_attr( $r, 'intro' ),
						'items'   => is_array( fnc_attr( $r, 'items', array() ) ) ? fnc_attr( $r, 'items', array() ) : array(),
					)
				);

			case 'instManifesto':
				return fnc_render_block_inst_manifesto(
					array(
						'image'     => fnc_acf_img_id( fnc_attr( $r, 'image', 0 ) ),
						'eyebrow'   => fnc_attr( $r, 'eyebrow' ),
						'manifestA' => fnc_attr( $r, 'manifest_a' ),
						'manifestB' => fnc_attr( $r, 'manifest_b' ),
						'sub'       => fnc_attr( $r, 'sub' ),
					)
				);

			case 'instCallout':
				return fnc_render_block_inst_callout(
					array(
						'title'    => fnc_attr( $r, 'title' ),
						'desc'     => fnc_attr( $r, 'desc' ),
						'ctaLabel' => fnc_attr( $r, 'cta_label' ),
						'ctaHref'  => fnc_attr( $r, 'cta_href' ),
					)
				);

			/* ---------------- Palette générique (composable) ---------------- */

			case 'hero':
				return fnc_render_block_hero(
					array(
						'eyebrow'  => fnc_attr( $r, 'eyebrow' ),
						'title'    => fnc_attr( $r, 'title' ),
						'subtitle' => fnc_attr( $r, 'subtitle' ),
						'body'     => fnc_attr( $r, 'body' ),
						'image'    => fnc_acf_img_id( fnc_attr( $r, 'image', 0 ) ),
						'ctaLabel' => fnc_attr( $r, 'cta_label' ),
						'ctaHref'  => fnc_attr( $r, 'cta_href' ),
					)
				);

			case 'richText':
				return fnc_render_block_richtext(
					array(
						'eyebrow' => fnc_attr( $r, 'eyebrow' ),
						'title'   => fnc_attr( $r, 'title' ),
						'content' => fnc_attr( $r, 'content' ),
					)
				);

			case 'split':
				return fnc_render_block_split(
					array(
						'eyebrow'  => fnc_attr( $r, 'eyebrow' ),
						'title'    => fnc_attr( $r, 'title' ),
						'body'     => fnc_attr( $r, 'body' ),
						'image'    => fnc_acf_img_id( fnc_attr( $r, 'image', 0 ) ),
						'mediaSide' => fnc_attr( $r, 'media_side', 'right' ),
						'ctaLabel' => fnc_attr( $r, 'cta_label' ),
						'ctaHref'  => fnc_attr( $r, 'cta_href' ),
					)
				);

			case 'stats':
				return fnc_render_block_stats(
					array(
						'title' => fnc_attr( $r, 'title' ),
						'items' => is_array( fnc_attr( $r, 'items', array() ) ) ? fnc_attr( $r, 'items', array() ) : array(),
					)
				);

			case 'cta':
				return fnc_render_block_cta(
					array(
						'title'    => fnc_attr( $r, 'title' ),
						'text'     => fnc_attr( $r, 'text' ),
						'ctaLabel' => fnc_attr( $r, 'cta_label' ),
						'ctaHref'  => fnc_attr( $r, 'cta_href' ),
					)
				);

			case 'faq':
				return fnc_render_block_faq(
					array(
						'title' => fnc_attr( $r, 'title' ),
						'items' => is_array( fnc_attr( $r, 'items', array() ) ) ? fnc_attr( $r, 'items', array() ) : array(),
					)
				);

			case 'documents':
				return fnc_render_block_documents(
					array(
						'title' => fnc_attr( $r, 'title' ),
						'items' => is_array( fnc_attr( $r, 'items', array() ) ) ? fnc_attr( $r, 'items', array() ) : array(),
					)
				);

			default:
				return '';
		}
	}
}

if ( ! function_exists( 'fnc_render_page_sections' ) ) {
	/**
	 * Rend en HTML DA toutes les sections composées d'une page (édition des pages).
	 *
	 * @param int    $post_id Identifiant de page (0 = page courante).
	 * @param string $kind    'institutional' | 'generic'.
	 * @return string HTML (chaîne vide si aucune section).
	 */
	function fnc_render_page_sections( $post_id = 0, $kind = 'institutional' ) {
		if ( ! function_exists( 'fnc_page_sections' ) ) {
			return '';
		}
		$rows = fnc_page_sections( $post_id, $kind );
		if ( empty( $rows ) ) {
			return '';
		}
		$out = '';
		foreach ( $rows as $r ) {
			$type = isset( $r['type'] ) ? $r['type'] : '';
			$out .= fnc_render_section_row( $type, $r );
		}
		return $out;
	}
}
