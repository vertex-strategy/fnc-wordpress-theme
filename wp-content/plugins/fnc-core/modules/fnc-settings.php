<?php
/**
 * Plugin Name: FNC Core — Réglages du site
 * Description: Surface unique de gouvernance transverse (identité, logos, coordonnées,
 *              réseaux, menu principal, ordre des pays, pied de page, SEO par défaut) et
 *              les accesseurs `fnc_get_setting()` / `fnc_social_links()` utilisés par le
 *              thème et les autres composants de l'extension.
 * Version: 0.1.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INTÉGRATION : autonome OU à fusionner dans FNC Core. Page « Réglages → FNC ».
 * Amorcé partiellement dans le thème → À DÉPLACER ici (retirer les doublons du thème
 * pour éviter une redéclaration ; toutes les fonctions sont function_exists-guardées).
 *
 * SOURCE DE VÉRITÉ : une seule option `fnc_settings` (tableau). Frontière DA maintenue :
 * on gère le CONTENU (textes, fichiers, liens), jamais la mise en forme.
 *
 * PÉRIMÈTRE DU SCAFFOLD : la page d'admin couvre les champs SCALAIRES (identité,
 * communication, SEO, logos par ID) + les réseaux sociaux (une ligne par réseau). Les
 * répéteurs riches — `mainNav`, `countryOrder`, `footerLinkGroups` — partagent les mêmes
 * clés d'option et sont destinés à être alimentés par ACF, un menu WP, ou le filtre
 * `fnc_settings` ; leurs ACCESSEURS sont fournis ci-dessous quelle que soit la source.
 *
 * LOCALISATION : les champs localisés (slogan, description, libellés…) sont stockés en
 * une valeur ; déléguer la traduction à Polylang (`pll_register_string` / `pll__`).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * Stockage + valeurs par défaut des réglages
 * ======================================================================== */

if ( ! function_exists( 'fnc_settings_defaults' ) ) {
	function fnc_settings_defaults() {
		return array(
			'officialName'          => 'Forum Numérique Congo',
			'slogan'                => '',
			'subtitle'              => '',
			'description'           => '',
			'shortIntro'            => '',
			'email'                 => '',
			'phone'                 => '',
			'address'               => '',
			'social'                => array(), // [ ['platform','label','url'], … ]
			'mainNav'               => array(), // [ ['label','linkType','internalRoute'|'externalUrl','openInNewTab','visible'], … ]
			'countryOrder'          => array(), // [ ['name','flag'=>attachment_id], … ]
			'footerLinkGroups'      => array(), // [ ['title','kind','links'=>[ ['label','href','external','openInNewTab','ariaLabel','enabled'] ]], … ]
			'footerText'            => '',
			'copyright'             => '',
			'seoDefaultTitle'       => '',
			'seoDefaultDescription' => '',
			'ogDefaultImage'        => 0,
			'twitterCard'           => 'summary_large_image',
			'robots'                => 'index, follow',
			'logoPrincipal'         => 0,
			'logoLight'             => 0,
			'logoDark'              => 0,
			'favicon'               => 0,
		);
	}
}

if ( ! function_exists( 'fnc_get_settings' ) ) {
	/** Tous les réglages : defaults ⊕ option stockée, filtrables. */
	function fnc_get_settings() {
		$stored = get_option( 'fnc_settings', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return apply_filters( 'fnc_settings', array_merge( fnc_settings_defaults(), $stored ) );
	}
}

if ( ! function_exists( 'fnc_get_setting' ) ) {
	/** Accesseur d'un réglage. Renvoie $default si vide/absent. */
	function fnc_get_setting( $key, $default = '' ) {
		$all = fnc_get_settings();
		if ( ! array_key_exists( $key, $all ) ) {
			return $default;
		}
		$v = $all[ $key ];
		if ( '' === $v || null === $v ) {
			return $default;
		}
		return $v;
	}
}

/* ==========================================================================
 * Filtrage des coordonnées de démonstration (placeholders)
 * ======================================================================== */

if ( ! function_exists( 'fnc_is_placeholder_phone' ) ) {
	/**
	 * Téléphone fictif « À confirmer » → à ne pas publier. Reprend le motif du
	 * site du Forum (Footer.tsx) : indicatif 242 suivi uniquement de 0/6 de
	 * remplissage (ex. +24206000000, +242060000000). Motif : ^\+?2420?60+$.
	 */
	function fnc_is_placeholder_phone( $value ) {
		$n = preg_replace( '/[^\d+]/', '', (string) $value );
		return 1 === preg_match( '/^\+?2420?60+$/', $n );
	}
}

if ( ! function_exists( 'fnc_is_usable_social' ) ) {
	/** URL de réseau exploitable : URL valide ET pas le placeholder LinkedIn nu. */
	function fnc_is_usable_social( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}
		return rtrim( $url, '/' ) !== 'https://www.linkedin.com';
	}
}

/* ==========================================================================
 * Accesseurs publics (consommés par les modules + le thème)
 * ======================================================================== */

if ( ! function_exists( 'fnc_public_phone' ) ) {
	function fnc_public_phone() {
		$p = (string) fnc_get_setting( 'phone', '' );
		return fnc_is_placeholder_phone( $p ) ? '' : $p;
	}
}

if ( ! function_exists( 'fnc_public_email' ) ) {
	function fnc_public_email() {
		$e = (string) fnc_get_setting( 'email', '' );
		return is_email( $e ) ? $e : '';
	}
}

if ( ! function_exists( 'fnc_public_address' ) ) {
	function fnc_public_address() {
		return (string) fnc_get_setting( 'address', '' );
	}
}

if ( ! function_exists( 'fnc_social_links' ) ) {
	/** Réseaux exploitables, normalisés : [ ['platform','label','url'], … ]. */
	function fnc_social_links() {
		$out = array();
		foreach ( (array) fnc_get_setting( 'social', array() ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$url = isset( $item['url'] ) ? trim( (string) $item['url'] ) : '';
			if ( ! fnc_is_usable_social( $url ) ) {
				continue;
			}
			$out[] = array(
				'platform' => isset( $item['platform'] ) ? sanitize_key( $item['platform'] ) : 'other',
				'label'    => isset( $item['label'] ) ? (string) $item['label'] : '',
				'url'      => $url,
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'fnc_main_nav' ) ) {
	/** Entrées du menu principal (visibles, résolues) : [ ['label','type','url','openInNewTab'], … ]. */
	function fnc_main_nav() {
		$out = array();
		foreach ( (array) fnc_get_setting( 'mainNav', array() ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			if ( array_key_exists( 'visible', $row ) && ! $row['visible'] ) {
				continue;
			}
			$label = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
			if ( '' === $label ) {
				continue;
			}
			$type = ( isset( $row['linkType'] ) && 'external' === $row['linkType'] ) ? 'external' : 'internal';
			$url  = 'external' === $type
				? ( isset( $row['externalUrl'] ) ? trim( (string) $row['externalUrl'] ) : '' )
				: ( isset( $row['internalRoute'] ) ? trim( (string) $row['internalRoute'] ) : '' );
			if ( '' === $url ) {
				continue;
			}
			$out[] = array(
				'label'        => $label,
				'type'         => $type,
				'url'          => $url, // interne = chemin (« /le-forum ») ; le thème résout le permalien.
				'openInNewTab' => ! empty( $row['openInNewTab'] ),
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'fnc_normalize_country' ) ) {
	function fnc_normalize_country( $name ) {
		$name = strtolower( trim( (string) $name ) );
		if ( function_exists( 'remove_accents' ) ) {
			$name = remove_accents( $name );
		}
		return preg_replace( '/\s+/', ' ', $name );
	}
}

if ( ! function_exists( 'fnc_country_order' ) ) {
	/** Ordre des pays : [ ['name','flag'=>url|''], … ]. */
	function fnc_country_order() {
		$out = array();
		foreach ( (array) fnc_get_setting( 'countryOrder', array() ) as $row ) {
			if ( is_array( $row ) ) {
				$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
				$flag = isset( $row['flag'] ) ? (int) $row['flag'] : 0;
			} else {
				$name = trim( (string) $row );
				$flag = 0;
			}
			if ( '' === $name ) {
				continue;
			}
			$out[] = array(
				'name' => $name,
				'flag' => $flag ? (string) wp_get_attachment_image_url( $flag, 'full' ) : '',
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'fnc_country_flag' ) ) {
	/** URL du drapeau téléversé pour un pays donné (comparaison insensible accents/casse). */
	function fnc_country_flag( $name ) {
		$target = fnc_normalize_country( $name );
		foreach ( fnc_country_order() as $c ) {
			if ( '' !== $c['flag'] && fnc_normalize_country( $c['name'] ) === $target ) {
				return $c['flag'];
			}
		}
		return '';
	}
}

if ( ! function_exists( 'fnc_footer_link_groups' ) ) {
	/** Groupes de liens du footer, nettoyés : [ ['title','kind','links'=>[…]], … ]. */
	function fnc_footer_link_groups() {
		$out = array();
		foreach ( (array) fnc_get_setting( 'footerLinkGroups', array() ) as $g ) {
			if ( ! is_array( $g ) ) {
				continue;
			}
			$title = isset( $g['title'] ) ? trim( (string) $g['title'] ) : '';
			$links = array();
			foreach ( (array) ( isset( $g['links'] ) ? $g['links'] : array() ) as $l ) {
				if ( ! is_array( $l ) ) {
					continue;
				}
				if ( array_key_exists( 'enabled', $l ) && ! $l['enabled'] ) {
					continue;
				}
				$label = isset( $l['label'] ) ? trim( (string) $l['label'] ) : '';
				$href  = isset( $l['href'] ) ? trim( (string) $l['href'] ) : '';
				if ( '' === $label || '' === $href ) {
					continue;
				}
				$links[] = array(
					'label'        => $label,
					'href'         => $href,
					'external'     => ! empty( $l['external'] ),
					'openInNewTab' => ! empty( $l['openInNewTab'] ),
					'ariaLabel'    => isset( $l['ariaLabel'] ) ? (string) $l['ariaLabel'] : '',
				);
			}
			if ( '' === $title && empty( $links ) ) {
				continue;
			}
			$out[] = array(
				'title' => $title,
				'kind'  => isset( $g['kind'] ) ? sanitize_key( $g['kind'] ) : 'custom',
				'links' => $links,
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'fnc_seo_default' ) ) {
	/** Valeur SEO par défaut : title | description | ogImage | twitterCard | robots. */
	function fnc_seo_default( $key ) {
		switch ( $key ) {
			case 'title':
				return (string) fnc_get_setting( 'seoDefaultTitle', '' );
			case 'description':
				return (string) fnc_get_setting( 'seoDefaultDescription', '' );
			case 'twitterCard':
				return (string) fnc_get_setting( 'twitterCard', 'summary_large_image' );
			case 'robots':
				return (string) fnc_get_setting( 'robots', 'index, follow' );
			case 'ogImage':
				$id = (int) fnc_get_setting( 'ogDefaultImage', 0 );
				return $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
			default:
				return '';
		}
	}
}

if ( ! function_exists( 'fnc_logo_url' ) ) {
	/** URL d'un logo : principal | light | dark | favicon. Repli sur le logo natif du thème. */
	function fnc_logo_url( $which = 'principal' ) {
		$map = array(
			'principal' => 'logoPrincipal',
			'light'     => 'logoLight',
			'dark'      => 'logoDark',
			'favicon'   => 'favicon',
		);
		$key = isset( $map[ $which ] ) ? $map[ $which ] : 'logoPrincipal';
		$id  = (int) fnc_get_setting( $key, 0 );
		if ( $id ) {
			$u = wp_get_attachment_image_url( $id, 'full' );
			if ( $u ) {
				return $u;
			}
		}
		if ( in_array( $which, array( 'principal', 'light' ), true ) ) {
			$cid = (int) get_theme_mod( 'custom_logo' );
			if ( $cid ) {
				$u = wp_get_attachment_image_url( $cid, 'full' );
				if ( $u ) {
					return $u;
				}
			}
		}
		return '';
	}
}

/* ==========================================================================
 * Réseaux sociaux : (dé)sérialisation « une ligne par réseau »
 * Format : platform | label | url   (ou « platform | url », ou « url » seul)
 * ======================================================================== */

if ( ! function_exists( 'fnc_social_to_text' ) ) {
	function fnc_social_to_text( $social ) {
		$lines = array();
		foreach ( (array) $social as $s ) {
			if ( ! is_array( $s ) ) {
				continue;
			}
			$lines[] = trim(
				( isset( $s['platform'] ) ? $s['platform'] : 'other' ) . ' | ' .
				( isset( $s['label'] ) ? $s['label'] : '' ) . ' | ' .
				( isset( $s['url'] ) ? $s['url'] : '' )
			);
		}
		return implode( "\n", $lines );
	}
}

if ( ! function_exists( 'fnc_social_from_text' ) ) {
	function fnc_social_from_text( $text ) {
		$rows = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$p = array_map( 'trim', explode( '|', $line ) );
			$n = count( $p );
			if ( 1 === $n ) {
				$platform = 'other';
				$label    = '';
				$url      = $p[0];
			} elseif ( 2 === $n ) {
				$platform = $p[0];
				$label    = '';
				$url      = $p[1];
			} else {
				$platform = $p[0];
				$label    = $p[1];
				$url      = $p[ $n - 1 ];
			}
			$url = esc_url_raw( $url );
			if ( '' === $url ) {
				continue;
			}
			$rows[] = array(
				'platform' => sanitize_key( $platform ) ? sanitize_key( $platform ) : 'other',
				'label'    => sanitize_text_field( $label ),
				'url'      => $url,
			);
		}
		return $rows;
	}
}

/* ==========================================================================
 * Admin — page « Réglages → FNC » (Settings API)
 * ======================================================================== */

if ( ! function_exists( 'fnc_settings_register' ) ) {
	function fnc_settings_register() {
		register_setting(
			'fnc_settings_group',
			'fnc_settings',
			array(
				'sanitize_callback' => 'fnc_settings_sanitize',
				'default'           => array(),
			)
		);
	}
	add_action( 'admin_init', 'fnc_settings_register' );
}

if ( ! function_exists( 'fnc_settings_sanitize' ) ) {
	/**
	 * Fusionne l'entrée du formulaire dans l'option existante : les clés-répéteurs non
	 * présentes dans le formulaire (mainNav/countryOrder/footerLinkGroups) sont PRÉSERVÉES.
	 */
	function fnc_settings_sanitize( $input ) {
		$out = get_option( 'fnc_settings', array() );
		if ( ! is_array( $out ) ) {
			$out = array();
		}
		if ( ! is_array( $input ) ) {
			return $out;
		}

		$text_keys = array( 'officialName', 'slogan', 'subtitle', 'phone', 'seoDefaultTitle', 'twitterCard', 'robots', 'copyright' );
		foreach ( $text_keys as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_text_field( $input[ $k ] );
			}
		}
		$area_keys = array( 'description', 'shortIntro', 'address', 'footerText', 'seoDefaultDescription' );
		foreach ( $area_keys as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = sanitize_textarea_field( $input[ $k ] );
			}
		}
		$int_keys = array( 'logoPrincipal', 'logoLight', 'logoDark', 'favicon', 'ogDefaultImage' );
		foreach ( $int_keys as $k ) {
			if ( isset( $input[ $k ] ) ) {
				$out[ $k ] = (int) $input[ $k ];
			}
		}
		if ( isset( $input['email'] ) ) {
			$out['email'] = sanitize_email( $input['email'] );
		}
		if ( isset( $input['social_text'] ) ) {
			$out['social'] = fnc_social_from_text( $input['social_text'] );
		}
		if ( isset( $out['twitterCard'] ) && ! in_array( $out['twitterCard'], array( 'summary_large_image', 'summary' ), true ) ) {
			$out['twitterCard'] = 'summary_large_image';
		}
		return $out;
	}
}

if ( ! function_exists( 'fnc_settings_menu' ) ) {
	function fnc_settings_menu() {
		add_options_page(
			'FNC — Réglages du site',
			'FNC (réglages)',
			'manage_options',
			'fnc-settings',
			'fnc_settings_page'
		);
	}
	add_action( 'admin_menu', 'fnc_settings_menu' );
}

if ( ! function_exists( 'fnc_settings_field_text' ) ) {
	/** Rend une ligne « label + input ». $type = text|email|number|url. */
	function fnc_settings_field_text( $key, $label, $type = 'text', $desc = '' ) {
		$all = fnc_get_settings();
		$val = isset( $all[ $key ] ) ? $all[ $key ] : '';
		printf(
			'<tr><th scope="row"><label for="fnc-%1$s">%2$s</label></th><td>' .
			'<input type="%3$s" id="fnc-%1$s" name="fnc_settings[%1$s]" value="%4$s" class="regular-text" />' .
			( $desc ? '<p class="description">%5$s</p>' : '%5$s' ) .
			'</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( $val ),
			$desc ? esc_html( $desc ) : ''
		);
	}
}

if ( ! function_exists( 'fnc_settings_field_area' ) ) {
	function fnc_settings_field_area( $key, $label, $value = null ) {
		$all = fnc_get_settings();
		$val = null !== $value ? $value : ( isset( $all[ $key ] ) ? $all[ $key ] : '' );
		printf(
			'<tr><th scope="row"><label for="fnc-%1$s">%2$s</label></th><td>' .
			'<textarea id="fnc-%1$s" name="fnc_settings[%1$s]" rows="4" class="large-text">%3$s</textarea></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_textarea( $val )
		);
	}
}

if ( ! function_exists( 'fnc_settings_page' ) ) {
	function fnc_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$all = fnc_get_settings();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'FNC — Réglages du site', 'fnc' ); ?></h1>
			<p class="description">
				Contenu transverse (identité, coordonnées, réseaux, SEO). Répéteurs riches
				(menu, pays, footer) : via ACF / menu WP / filtre <code>fnc_settings</code>.
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'fnc_settings_group' ); ?>

				<h2><?php echo esc_html__( 'Identité', 'fnc' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					fnc_settings_field_text( 'officialName', 'Nom officiel' );
					fnc_settings_field_text( 'slogan', 'Slogan' );
					fnc_settings_field_text( 'subtitle', 'Sous-titre' );
					fnc_settings_field_area( 'description', 'Description' );
					fnc_settings_field_area( 'shortIntro', 'Présentation courte' );
					?>
				</table>

				<h2><?php echo esc_html__( 'Communication', 'fnc' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					fnc_settings_field_text( 'email', 'E-mail de contact', 'email' );
					fnc_settings_field_text( 'phone', 'Téléphone' );
					fnc_settings_field_area( 'address', 'Adresse' );
					?>
					<tr>
						<th scope="row"><label for="fnc-social_text">Réseaux sociaux</label></th>
						<td>
							<textarea id="fnc-social_text" name="fnc_settings[social_text]" rows="5" class="large-text" placeholder="linkedin | LinkedIn | https://www.linkedin.com/company/…"><?php echo esc_textarea( fnc_social_to_text( isset( $all['social'] ) ? $all['social'] : array() ) ); ?></textarea>
							<p class="description">Une ligne par réseau : <code>plateforme | libellé | url</code>. Un LinkedIn nu ou une URL invalide est ignoré.</p>
						</td>
					</tr>
				</table>

				<h2><?php echo esc_html__( 'Logos & médias (ID de pièce jointe)', 'fnc' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					fnc_settings_field_text( 'logoPrincipal', 'Logo principal (ID)', 'number', 'ID de la médiathèque. Vide → logo natif du thème.' );
					fnc_settings_field_text( 'logoLight', 'Logo clair (ID)', 'number' );
					fnc_settings_field_text( 'logoDark', 'Logo sombre (ID)', 'number' );
					fnc_settings_field_text( 'favicon', 'Favicon (ID)', 'number' );
					?>
				</table>

				<h2><?php echo esc_html__( 'Pied de page', 'fnc' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					fnc_settings_field_area( 'footerText', 'Texte du footer' );
					fnc_settings_field_text( 'copyright', 'Mention de copyright', 'text', 'L’année courante est ajoutée par le thème.' );
					?>
				</table>

				<h2><?php echo esc_html__( 'SEO par défaut', 'fnc' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					fnc_settings_field_text( 'seoDefaultTitle', 'Titre par défaut' );
					fnc_settings_field_area( 'seoDefaultDescription', 'Description par défaut' );
					fnc_settings_field_text( 'ogDefaultImage', 'Image OpenGraph (ID)', 'number', '1200×630 recommandé.' );
					fnc_settings_field_text( 'robots', 'Directive robots', 'text', 'Ex. « index, follow » ou « noindex, nofollow ».' );
					?>
					<tr>
						<th scope="row"><label for="fnc-twitterCard">Type de Twitter Card</label></th>
						<td>
							<select id="fnc-twitterCard" name="fnc_settings[twitterCard]">
								<?php
								$tc = isset( $all['twitterCard'] ) ? $all['twitterCard'] : 'summary_large_image';
								foreach ( array( 'summary_large_image' => 'Grande image', 'summary' => 'Résumé' ) as $v => $lab ) {
									printf(
										'<option value="%s"%s>%s</option>',
										esc_attr( $v ),
										selected( $tc, $v, false ),
										esc_html( $lab )
									);
								}
								?>
							</select>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
