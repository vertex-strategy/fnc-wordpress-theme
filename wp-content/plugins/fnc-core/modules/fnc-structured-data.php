<?php
/**
 * Plugin Name: FNC Core — Données structurées schema.org (Module D)
 * Description: Émet Organization + WebSite sur toutes les pages, et Event
 *              (édition en cours : dates, lieu, intervenants) sur l'accueil.
 *              Portage fidèle de src/lib/seo/structuredData.ts (C4).
 * Version: 0.1.0
 * Author: FNC
 *
 * INTÉGRATION : autonome OU à fusionner dans FNC Core. S'accroche à wp_head.
 * DÉPEND (souplement) du Module C pour l'édition active + participants
 * (function_exists ; sans lui, l'Event est simplement omis).
 *
 * PRINCIPE : jamais de balisage PARTIEL. L'Event n'est émis que si nom + date de
 * début sont présents (sinon pénalisant plutôt qu'utile).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * Helpers
 * ======================================================================== */

if ( ! function_exists( 'fnc_sd_locale_bcp47' ) ) {
	function fnc_sd_locale_bcp47() {
		$en = function_exists( 'pll_current_language' )
			? ( 'en' === pll_current_language() )
			: ( 0 === strpos( get_locale(), 'en' ) );
		return $en ? 'en-US' : 'fr-CG';
	}
}

if ( ! function_exists( 'fnc_sd_abs' ) ) {
	/** Rend une URL absolue (préfixe le site si chemin relatif). */
	function fnc_sd_abs( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		if ( preg_match( '#^https?://#i', $url ) ) {
			return $url;
		}
		return untrailingslashit( home_url() ) . '/' . ltrim( $url, '/' );
	}
}

if ( ! function_exists( 'fnc_sd_site_name' ) ) {
	function fnc_sd_site_name() {
		if ( function_exists( 'fnc_get_setting' ) ) {
			$n = trim( (string) fnc_get_setting( 'officialName' ) );
			if ( '' !== $n ) {
				return $n;
			}
		}
		return get_bloginfo( 'name' );
	}
}

if ( ! function_exists( 'fnc_sd_logo_url' ) ) {
	function fnc_sd_logo_url() {
		// Logo personnalisé du thème, sinon rien.
		$id = (int) get_theme_mod( 'custom_logo' );
		if ( $id ) {
			$src = wp_get_attachment_image_url( $id, 'full' );
			if ( $src ) {
				return $src;
			}
		}
		return '';
	}
}

if ( ! function_exists( 'fnc_sd_same_as' ) ) {
	/** URLs de réseaux sociaux (depuis les réglages, si disponibles). */
	function fnc_sd_same_as() {
		$out = array();
		if ( function_exists( 'fnc_social_links' ) ) {
			foreach ( (array) fnc_social_links() as $url ) {
				$u = is_array( $url ) ? ( $url['url'] ?? '' ) : $url;
				$u = trim( (string) $u );
				if ( '' !== $u ) {
					$out[] = $u;
				}
			}
		}
		return array_values( array_unique( $out ) );
	}
}

/* ==========================================================================
 * Constructeurs (miroir de structuredData.ts)
 * ======================================================================== */

if ( ! function_exists( 'fnc_sd_organization' ) ) {
	function fnc_sd_organization() {
		$site = untrailingslashit( home_url() );
		$org  = array(
			'@type' => 'Organization',
			'@id'   => $site . '#organization',
			'name'  => fnc_sd_site_name(),
			'url'   => $site,
		);
		$logo = fnc_sd_logo_url();
		if ( $logo ) {
			$org['logo'] = fnc_sd_abs( $logo );
		}
		$same = fnc_sd_same_as();
		if ( ! empty( $same ) ) {
			$org['sameAs'] = $same;
		}
		return $org;
	}
}

if ( ! function_exists( 'fnc_sd_website' ) ) {
	function fnc_sd_website() {
		$site = untrailingslashit( home_url() );
		return array(
			'@type'      => 'WebSite',
			'@id'        => $site . '#website',
			'name'       => fnc_sd_site_name(),
			'url'        => $site,
			'inLanguage' => fnc_sd_locale_bcp47(),
			'publisher'  => array( '@id' => $site . '#organization' ),
		);
	}
}

if ( ! function_exists( 'fnc_sd_identity_graph' ) ) {
	/** Organization + WebSite, un seul bloc, pour toutes les pages. */
	function fnc_sd_identity_graph() {
		return array(
			'@context' => 'https://schema.org',
			'@graph'   => array( fnc_sd_organization(), fnc_sd_website() ),
		);
	}
}

if ( ! function_exists( 'fnc_sd_event' ) ) {
	/**
	 * schema.org/Event de l'édition en cours. Renvoie null si nom ou date de début
	 * manquent (jamais de balisage partiel).
	 *
	 * @return array|null
	 */
	function fnc_sd_event() {
		if ( ! function_exists( 'fnc_resolve_active_edition' ) ) {
			return null; // Module C requis pour l'édition active.
		}
		$ed = fnc_resolve_active_edition();
		if ( ! $ed ) {
			return null;
		}

		$start_raw = (string) get_post_meta( $ed->ID, '_fnc_edition_start_date', true );
		$name      = trim( get_the_title( $ed ) );
		if ( '' === $start_raw || '' === $name ) {
			return null;
		}
		$start_ts = strtotime( $start_raw );
		if ( ! $start_ts ) {
			return null;
		}

		$site = untrailingslashit( home_url() );

		$address = array( '@type' => 'PostalAddress', 'addressLocality' => 'Brazzaville', 'addressCountry' => 'CG' );
		$location = array( '@type' => 'Place', 'address' => $address );
		$loc_name = trim( (string) get_post_meta( $ed->ID, '_fnc_edition_location', true ) );
		if ( '' !== $loc_name ) {
			$location['name'] = $loc_name;
		}

		$event = array(
			'@context'             => 'https://schema.org',
			'@type'                => 'Event',
			'name'                 => $name,
			'startDate'            => gmdate( 'Y-m-d', $start_ts ),
			'eventStatus'          => 'https://schema.org/EventScheduled',
			'eventAttendanceMode'  => 'https://schema.org/OfflineEventAttendanceMode',
			'location'             => $location,
			'url'                  => $site . '/',
			'inLanguage'           => fnc_sd_locale_bcp47(),
			'organizer'            => array( '@type' => 'Organization', 'name' => fnc_sd_site_name(), 'url' => $site ),
		);

		$end_raw = (string) get_post_meta( $ed->ID, '_fnc_edition_end_date', true );
		$end_ts  = $end_raw ? strtotime( $end_raw ) : false;
		if ( $end_ts ) {
			$event['endDate'] = gmdate( 'Y-m-d', $end_ts );
		}

		$theme = trim( (string) get_post_meta( $ed->ID, '_fnc_edition_theme', true ) );
		if ( '' !== $theme ) {
			$event['description'] = $theme;
		}

		$img = get_the_post_thumbnail_url( $ed->ID, 'full' );
		if ( $img ) {
			$event['image'] = fnc_sd_abs( $img );
		}

		// Intervenants (Module C). Person[] { name, jobTitle }.
		if ( function_exists( 'fnc_edition_participants' ) ) {
			$performers = array();
			foreach ( fnc_edition_participants( $ed->ID ) as $sid ) {
				$pname = trim( get_the_title( $sid ) );
				if ( '' === $pname ) {
					continue;
				}
				$person = array( '@type' => 'Person', 'name' => $pname );
				$role   = trim( (string) get_post_meta( $sid, '_fnc_speaker_title', true ) );
				if ( '' !== $role ) {
					$person['jobTitle'] = $role;
				}
				$performers[] = $person;
			}
			if ( ! empty( $performers ) ) {
				$event['performer'] = $performers;
			}
		}

		return $event;
	}
}

/* ==========================================================================
 * Rendu — wp_head
 * ======================================================================== */

if ( ! function_exists( 'fnc_sd_render' ) ) {
	function fnc_sd_render( $data ) {
		if ( empty( $data ) ) {
			return;
		}
		// wp_json_encode échappe les « / » (\/) → pas de rupture </script>. Unicode
		// échappé en \uXXXX → sûr dans un <script>.
		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
	}
}

if ( ! function_exists( 'fnc_sd_output' ) ) {
	function fnc_sd_output() {
		// Identité de site — partout.
		fnc_sd_render( fnc_sd_identity_graph() );

		// Event — sur l'accueil (comme le site réel). Décommenter is_page pour l'ajouter
		// aussi au hub « édition en cours ».
		if ( is_front_page() /* || is_page( 'edition-en-cours' ) */ ) {
			$event = fnc_sd_event();
			if ( $event ) {
				fnc_sd_render( $event );
			}
		}
	}
	add_action( 'wp_head', 'fnc_sd_output', 20 );
}
