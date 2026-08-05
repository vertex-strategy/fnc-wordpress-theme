<?php
/**
 * Forum Numérique Congo — réglages d’apparence (identité, coordonnées, réseaux).
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
 * Lecture d'un réglage global, avec repli.
 *
 * Tous les réglages sont stockés en theme_mod préfixés `fnc_`.
 *
 * @param string $key     Clé sans préfixe (ex. « email »).
 * @param mixed  $default Valeur de repli si le réglage est vide/absent.
 * @return mixed
 */
/*
 * Le plugin FNC Core (réglages du site) fournit la version faisant autorite de
 * fnc_get_setting() / fnc_social_links() (source unique : option `fnc_settings`,
 * cles camelCase). Le plugin se chargeant AVANT le theme, sa version gagne ; les
 * definitions ci-dessous sont guardees et ne servent que de repli si le plugin
 * est absent (reglages alors vides, sans fatal). Les cles du theme (snake_case)
 * sont traduites vers le schema du plugin par fnc_map_setting_key().
 */
if ( ! function_exists( 'fnc_get_setting' ) ) {
	function fnc_get_setting( $key, $default = '' ) {
		$value = get_theme_mod( 'fnc_' . $key, $default );
		return ( '' === $value || null === $value ) ? $default : $value;
	}
}

/**
 * Traduit une cle de reglage historique du theme (snake_case) vers la cle du
 * plugin FNC Core (camelCase). Cle inconnue/identique renvoyee telle quelle
 * (les cles sans equivalent — country_order, press_contacts — restent non
 * mappees et retombent proprement sur le defaut).
 *
 * @param string $key
 * @return string
 */
function fnc_map_setting_key( $key ) {
	static $map = array(
		'official_name'           => 'officialName',
		'short_intro'             => 'shortIntro',
		'footer_text'             => 'footerText',
		'footer_copyright'        => 'copyright',
		'seo_default_title'       => 'seoDefaultTitle',
		'seo_default_description' => 'seoDefaultDescription',
		'twitter_card'            => 'twitterCard',
		'og_default_image'        => 'ogDefaultImage',
		'logo_principal'          => 'logoPrincipal',
		'logo_light'              => 'logoLight',
		'logo_dark'               => 'logoDark',
	);
	return isset( $map[ $key ] ) ? $map[ $key ] : $key;
}

/**
 * Champs de réglages localisables (equivalents des champs `localized` du Global
 * les réglages de référence). Cle => [libelle, multiligne].
 *
 * @return array<string,array{0:string,1:bool}>
 */
function fnc_i18n_settings() {
	return array(
		'slogan'                  => array( __( 'Slogan', 'fnc-wordpress-theme' ), false ),
		'subtitle'                => array( __( 'Sous-titre', 'fnc-wordpress-theme' ), false ),
		'description'             => array( __( 'Description', 'fnc-wordpress-theme' ), true ),
		'short_intro'             => array( __( 'Présentation courte', 'fnc-wordpress-theme' ), true ),
		'address'                 => array( __( 'Adresse', 'fnc-wordpress-theme' ), true ),
		'footer_text'             => array( __( 'Texte du footer', 'fnc-wordpress-theme' ), true ),
		'footer_copyright'        => array( __( 'Mention de copyright', 'fnc-wordpress-theme' ), false ),
		'seo_default_title'       => array( __( 'Titre SEO par défaut', 'fnc-wordpress-theme' ), false ),
		'seo_default_description' => array( __( 'Description SEO par défaut', 'fnc-wordpress-theme' ), true ),
	);
}

/**
 * Lecture d'un réglage global TRADUIT selon la langue courante (Polylang).
 *
 * Les réglages du Customizer sont des valeurs uniques (un seul theme_mod, pas
 * une valeur par langue). Pour les champs de contenu localisables, on passe la
 * valeur par le module « Traductions des chaînes » de Polylang (pll__), qui
 * renvoie la version dans la langue courante si elle a été saisie, sinon la
 * valeur source. Sans Polylang, se comporte comme fnc_get_setting().
 *
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function fnc_get_setting_i18n( $key, $default = '' ) {
	$value = fnc_get_setting( fnc_map_setting_key( $key ), $default );
	return fnc_pll( $value );
}

/**
 * Traduit une chaîne via le module « Traductions des chaînes » de Polylang,
 * si actif et si la chaîne a ete enregistree (pll_register_string) puis
 * traduite. Sans Polylang, renvoie la chaîne inchangee.
 *
 * @param mixed $string
 * @return mixed
 */
function fnc_pll( $string ) {
	if ( '' !== $string && is_string( $string ) && function_exists( 'pll__' ) ) {
		return pll__( $string );
	}
	return $string;
}

/**
 * Enregistre les réglages localisables comme chaînes traduisibles Polylang.
 *
 * Chaque valeur reellement renseignee devient traduisible dans l'admin
 * (Langues → Traductions des chaînes, groupe « Forum Numérique Congo »). Sans
 * Polylang, ne fait rien. Appele sur `init` (front et admin) pour que la page
 * de traduction liste les chaînes et que pll__ puisse les resoudre.
 */
function fnc_register_pll_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$group = 'Forum Numérique Congo';
	foreach ( fnc_i18n_settings() as $key => $conf ) {
		$value = fnc_get_setting( fnc_map_setting_key( $key ), '' );
		if ( '' === $value || ! is_string( $value ) ) {
			continue;
		}
		pll_register_string( 'fnc_' . $key, $value, $group, $conf[1] );
	}

	// Contacts presse : chaque valeur textuelle (role, organisation) peut varier
	// par langue. On enregistre les valeurs non vides, ligne par ligne.
	foreach ( fnc_parse_press_contacts() as $index => $contact ) {
		foreach ( array( 'role', 'organization' ) as $field ) {
			if ( ! empty( $contact[ $field ] ) ) {
				pll_register_string( 'fnc_press_' . $index . '_' . $field, $contact[ $field ], $group, false );
			}
		}
	}

	// Groupes de liens du pied de page saisis : titres et libellés (et aria)
	// deviennent traduisibles. Les colonnes PAR DÉFAUT ne sont pas enregistrées
	// ici (déjà traduites via le domaine du thème).
	foreach ( fnc_footer_groups_source() as $gi => $fgroup ) {
		if ( '' !== $fgroup['title'] ) {
			pll_register_string( 'fnc_footer_group_' . $gi . '_title', $fgroup['title'], $group, false );
		}
		foreach ( $fgroup['links'] as $li => $flink ) {
			if ( ! empty( $flink['label'] ) ) {
				pll_register_string( 'fnc_footer_group_' . $gi . '_link_' . $li . '_label', $flink['label'], $group, false );
			}
			if ( ! empty( $flink['ariaLabel'] ) ) {
				pll_register_string( 'fnc_footer_group_' . $gi . '_link_' . $li . '_aria', $flink['ariaLabel'], $group, false );
			}
		}
	}
}
add_action( 'init', 'fnc_register_pll_strings' );

/**
 * URL d'un média enregistré comme ID de pièce jointe (logos, image OG).
 *
 * @param string $key  Clé du réglage (ex. « logo_principal »).
 * @param string $size Taille d'image WordPress.
 * @return string URL, ou chaîne vide si non défini.
 */
function fnc_get_setting_image_url( $key, $size = 'full' ) {
	$attachment_id = (int) fnc_get_setting( fnc_map_setting_key( $key ), 0 );
	if ( $attachment_id <= 0 ) {
		return '';
	}
	$src = wp_get_attachment_image_src( $attachment_id, $size );
	return $src ? $src[0] : '';
}

/**
 * Réseaux sociaux renseignés, sous forme [plateforme => url].
 * N'inclut que les plateformes dont l'URL est réellement saisie (RÈGLE 4 :
 * aucun lien fictif). Ordre stable, aligné sur l’énumération de référence.
 *
 * @return array<string,string>
 */
if ( ! function_exists( 'fnc_social_links' ) ) {
	// Repli (plugin FNC Core absent) : meme STRUCTURE que le plugin — une liste
	// d'entrees [ ['platform','label','url'], … ] — pour que l'iteration du theme
	// soit identique quelle que soit la source.
	function fnc_social_links() {
		$platforms = array( 'linkedin', 'x', 'facebook', 'instagram', 'youtube' );
		$links     = array();
		foreach ( $platforms as $platform ) {
			$url = fnc_get_setting( 'social_' . $platform, '' );
			if ( $url ) {
				$links[] = array( 'platform' => $platform, 'label' => '', 'url' => $url );
			}
		}
		return $links;
	}
}

/**
 * Libellé lisible d'une plateforme sociale.
 *
 * @param string $platform Clé de plateforme.
 * @return string
 */
function fnc_social_label( $platform ) {
	$labels = array(
		'linkedin'  => 'LinkedIn',
		'x'         => 'X',
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
	);
	return isset( $labels[ $platform ] ) ? $labels[ $platform ] : ucfirst( $platform );
}

/**
 * Icône SVG inline d'une plateforme sociale — port fidèle du composant
 * SocialIcon du site du Forum (le site du Forum Chaque icône fait
 * 16×16 dans un viewBox 0 0 24 24, en currentColor (le CSS .social-chip du kit
 * gère la couleur selon le contexte). Repli neutre (maillon) si la plateforme
 * n'est pas reconnue. Retourne du markup SVG destiné à être échappé au sortir.
 *
 * @param string $platform Clé de plateforme (linkedin, x, facebook, …).
 * @return string SVG inline.
 */
function fnc_social_icon( $platform ) {
	$common = 'width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"';
	switch ( $platform ) {
		case 'linkedin':
			return '<svg ' . $common . ' fill="currentColor"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05C20.5 8.65 22 10.9 22 14.3V21h-4v-5.9c0-1.4-.03-3.2-1.95-3.2-1.95 0-2.25 1.52-2.25 3.1V21H9z"/></svg>';
		case 'x':
			return '<svg ' . $common . ' fill="currentColor"><path d="M18.24 2H21l-6.56 7.5L22 22h-6.4l-4.7-6.16L5.5 22H2.74l7.02-8.02L2 2h6.56l4.25 5.62zm-1.12 18h1.53L7.02 3.9H5.38z"/></svg>';
		case 'facebook':
			return '<svg ' . $common . ' fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg>';
		case 'instagram':
			return '<svg ' . $common . ' fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>';
		case 'youtube':
			return '<svg ' . $common . ' fill="currentColor"><path d="M23 12s0-3.2-.4-4.73a2.5 2.5 0 0 0-1.76-1.77C19.28 5.1 12 5.1 12 5.1s-7.28 0-8.84.4A2.5 2.5 0 0 0 1.4 7.27C1 8.8 1 12 1 12s0 3.2.4 4.73a2.5 2.5 0 0 0 1.76 1.77c1.56.4 8.84.4 8.84.4s7.28 0 8.84-.4a2.5 2.5 0 0 0 1.76-1.77C23 15.2 23 12 23 12zM9.75 15.02V8.98L15.5 12z"/></svg>';
		default:
			return '<svg ' . $common . ' fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>';
	}
}

/**
 * Analyse le champ « Contacts presse » (une ligne par contact, champs séparés
 * par « | » dans l'ordre : Nom | Rôle | Organisation | Email | Téléphone).
 *
 * Choix zéro-dépendance : le Customizer natif ne fournit pas de répéteur ; ce
 * format texte reste administrable sans plugin de champs et sans JavaScript.
 * Les lignes vides sont ignorées ; un contact sans aucune valeur est écarté.
 *
 * @param string|null $raw Contenu brut du réglage.
 * @return array<int,array{name:string,role:string,organization:string,email:string,phone:string}>
 */
function fnc_parse_press_contacts( $raw = null ) {
	if ( null === $raw ) {
		$raw = fnc_get_setting( 'press_contacts', '' );
	}
	$contacts = array();
	if ( ! $raw ) {
		return $contacts;
	}
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts   = array_map( 'trim', explode( '|', $line ) );
		$contact = array(
			'name'         => $parts[0] ?? '',
			'role'         => $parts[1] ?? '',
			'organization' => $parts[2] ?? '',
			'email'        => $parts[3] ?? '',
			'phone'        => $parts[4] ?? '',
		);
		if ( '' === implode( '', $contact ) ) {
			continue;
		}
		$contacts[] = $contact;
	}
	return $contacts;
}

/**
 * Clé de comparaison d'un nom de pays : insensible à la casse, aux espaces et
 * aux accents (même tolérance que le site du Forum : « Congo » ≡ « congo »).
 *
 * @param string $name
 * @return string
 */
function fnc_country_key( $name ) {
	return remove_accents( mb_strtolower( trim( (string) $name ) ) );
}

/**
 * Analyse le réglage « Ordre des pays » (une ligne par pays, format
 * « Pays | URL du drapeau », la partie drapeau étant optionnelle). L'ordre de
 * saisie est conservé.
 *
 * @param string|null $raw
 * @return array<int,array{name:string,flag:string}>
 */
function fnc_parse_country_order( $raw = null ) {
	if ( null === $raw ) {
		$raw = fnc_get_setting( 'country_order', '' );
	}
	$entries = array();
	if ( ! $raw ) {
		return $entries;
	}
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' === $parts[0] ) {
			continue;
		}
		$entries[] = array(
			'name' => $parts[0],
			'flag' => isset( $parts[1] ) ? esc_url_raw( $parts[1] ) : '',
		);
	}
	return $entries;
}

/**
 * Table drapeau par pays (clé normalisée → URL), issue du réglage d'ordre.
 *
 * @return array<string,string>
 */
function fnc_country_flag_map() {
	$map = array();
	foreach ( fnc_parse_country_order() as $entry ) {
		if ( $entry['flag'] ) {
			$map[ fnc_country_key( $entry['name'] ) ] = $entry['flag'];
		}
	}
	return $map;
}

/**
 * Analyse le réglage « Groupes de liens du pied de page ».
 *
 * Modèle Payload répliqué (footerLinkGroups) : une liste ORDONNÉE de groupes,
 * chacun portant un titre, un « genre » (kind) et une liste de liens ordonnés.
 * Choix zéro-dépendance (comme « Ordre des pays » et « Contacts presse ») : un
 * format texte administrable sans plugin de champs ni JavaScript.
 *
 * Syntaxe (une entité par ligne) :
 *   - Ligne d'en-tête de groupe : « Titre | kind »  (kind optionnel → « custom »).
 *   - Ligne de lien (préfixe « - ») : « - Libellé | href | drapeaux | aria »
 *       · drapeaux : liste séparée par des virgules parmi « newtab » (ouvre dans
 *         un nouvel onglet) et « off » (lien désactivé, masqué au rendu).
 *       · « external » est déduit automatiquement d'une URL absolue (http/https),
 *         mais peut être forcé via le drapeau « external ».
 *       · aria : libellé accessible optionnel (4ᵉ champ).
 *   - Ligne « // … » : note interne (sourceNote), ignorée au rendu.
 *   - Ligne vide : séparateur visuel, ignorée.
 *
 * Réglage vide → le pied de page retombe sur ses groupes par défaut (jamais vide).
 *
 * @param string|null $raw
 * @return array<int,array{title:string,kind:string,links:array<int,array<string,mixed>>}>
 */
function fnc_parse_footer_groups( $raw = null ) {
	if ( null === $raw ) {
		$raw = fnc_footer_groups_raw();
	}
	$groups  = array();
	$current = null;
	if ( ! $raw ) {
		return $groups;
	}

	$flush = static function () use ( &$groups, &$current ) {
		if ( is_array( $current ) && ( '' !== $current['title'] || ! empty( $current['links'] ) ) ) {
			$groups[] = $current;
		}
		$current = null;
	};

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line || 0 === strpos( $line, '//' ) ) {
			continue; // Séparateur ou note interne.
		}

		if ( '-' === $line[0] ) {
			// Ligne de lien.
			if ( null === $current ) {
				$current = array( 'title' => '', 'kind' => 'custom', 'links' => array() );
			}
			$parts = array_map( 'trim', explode( '|', ltrim( $line, "- \t" ) ) );
			$label = $parts[0] ?? '';
			$href  = $parts[1] ?? '';
			if ( '' === $label || '' === $href ) {
				continue;
			}
			$flags    = isset( $parts[2] ) ? array_map( 'trim', explode( ',', strtolower( $parts[2] ) ) ) : array();
			$aria     = $parts[3] ?? '';
			$is_abs   = (bool) preg_match( '#^https?://#i', $href );
			$external = in_array( 'external', $flags, true ) || $is_abs;
			$current['links'][] = array(
				'label'        => $label,
				'href'         => $is_abs ? esc_url_raw( $href ) : $href,
				'external'     => $external,
				'openInNewTab' => in_array( 'newtab', $flags, true ),
				'ariaLabel'    => $aria,
				'enabled'      => ! in_array( 'off', $flags, true ) && ! in_array( 'disabled', $flags, true ),
			);
			continue;
		}

		// Ligne d'en-tête de groupe : on clôt le groupe précédent.
		$flush();
		$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
		$current = array(
			'title' => $parts[0],
			'kind'  => isset( $parts[1] ) && '' !== $parts[1] ? sanitize_key( $parts[1] ) : 'custom',
			'links' => array(),
		);
	}
	$flush();

	return $groups;
}

/**
 * Groupes de liens du pied de page par défaut — reproduction fidèle des colonnes
 * historiques du thème (libellés traduits, URLs résolues dynamiquement). Utilisés
 * quand le réglage « Groupes de liens du pied de page » est vide, pour ne jamais
 * afficher un pied de page sans navigation.
 *
 * @return array<int,array{title:string,kind:string,links:array<int,array<string,mixed>>}>
 */
function fnc_footer_default_columns() {
	$link = static function ( $label, $href ) {
		return array(
			'label'        => $label,
			'href'         => $href,
			'external'     => false,
			'openInNewTab' => false,
			'ariaLabel'    => '',
			'enabled'      => true,
		);
	};

	return array(
		array(
			'title' => __( 'Le Forum', 'fnc-wordpress-theme' ),
			'kind'  => 'main',
			'links' => array(
				$link( __( 'Présentation', 'fnc-wordpress-theme' ), fnc_page_url( 'le-forum' ) ),
				$link( __( 'Édition en cours', 'fnc-wordpress-theme' ), fnc_page_url( 'edition-en-cours' ) ),
				$link( __( 'Programme', 'fnc-wordpress-theme' ), fnc_archive_url( 'fnc_session' ) ),
				$link( __( 'Intervenants', 'fnc-wordpress-theme' ), fnc_archive_url( 'fnc_intervenant' ) ),
			),
		),
		array(
			'title' => __( 'Ressources', 'fnc-wordpress-theme' ),
			'kind'  => 'resources',
			'links' => array(
				$link( __( 'Éditions', 'fnc-wordpress-theme' ), fnc_archive_url( 'fnc_edition' ) ),
				$link( __( 'Ressources', 'fnc-wordpress-theme' ), fnc_archive_url( 'fnc_publication' ) ),
				$link( __( 'Informations pratiques', 'fnc-wordpress-theme' ), fnc_page_url( 'informations-pratiques' ) ),
				$link( __( 'Dossier presse', 'fnc-wordpress-theme' ), fnc_page_url( 'espace-presse' ) ),
			),
		),
		array(
			'title' => __( 'Contact', 'fnc-wordpress-theme' ),
			'kind'  => 'useful',
			'links' => array(
				$link( __( 'Contact', 'fnc-wordpress-theme' ), fnc_page_url( 'contact' ) ),
				$link( __( 'Devenir partenaire', 'fnc-wordpress-theme' ), fnc_page_url( 'partenaires' ) ),
				$link( __( 'S’inscrire', 'fnc-wordpress-theme' ), fnc_page_url( 'inscription' ) ),
			),
		),
	);
}

/**
 * Texte brut des groupes de liens du pied de page, quelle que soit l'installation.
 *
 * Deux surfaces d'édition coexistent selon la présence de l'extension FNC Core :
 *   - Installation complète (FNC Core actif) : page « Réglages → FNC », clé
 *     d'option `footerLinkGroupsText` (lue par l'accesseur du plugin).
 *   - Thème seul (sans FNC Core) : Personnaliser → FNC → Footer, theme_mod
 *     `fnc_footer_link_groups`.
 * On lit la première renseignée. Vide dans les deux cas → chaîne vide.
 *
 * @return string
 */
function fnc_footer_groups_raw() {
	$raw = (string) fnc_get_setting( 'footerLinkGroupsText', '' );
	if ( '' === trim( $raw ) && function_exists( 'get_theme_mod' ) ) {
		$raw = (string) get_theme_mod( 'fnc_footer_link_groups', '' );
	}
	return $raw;
}

/**
 * Source normalisée des groupes de liens du pied de page (avant traduction) :
 *   1. le texte saisi par l'éditeur (surface d'admin), s'il produit des groupes ;
 *   2. sinon un tableau structuré `footerLinkGroups` fourni par l'extension FNC
 *      Core (seed, filtre ou champs) ;
 *   3. sinon un tableau vide (le rendu retombera sur les colonnes par défaut).
 *
 * Le texte l'emporte pour que l'édition manuelle soit toujours prioritaire.
 *
 * @return array<int,array{title:string,kind:string,links:array<int,array<string,mixed>>}>
 */
function fnc_footer_groups_source() {
	$parsed = fnc_parse_footer_groups( fnc_footer_groups_raw() );
	if ( ! empty( $parsed ) ) {
		return $parsed;
	}

	// Repli : tableau structuré exposé par l'extension (déjà nettoyé côté plugin).
	if ( function_exists( 'fnc_footer_link_groups' ) ) {
		$structured = fnc_footer_link_groups();
		if ( ! empty( $structured ) ) {
			$groups = array();
			foreach ( $structured as $g ) {
				$links = array();
				foreach ( ( isset( $g['links'] ) ? $g['links'] : array() ) as $l ) {
					$href    = isset( $l['href'] ) ? (string) $l['href'] : '';
					$links[] = array(
						'label'        => isset( $l['label'] ) ? (string) $l['label'] : '',
						'href'         => $href,
						'external'     => ! empty( $l['external'] ) || (bool) preg_match( '#^https?://#i', $href ),
						'openInNewTab' => ! empty( $l['openInNewTab'] ),
						'ariaLabel'    => isset( $l['ariaLabel'] ) ? (string) $l['ariaLabel'] : '',
						'enabled'      => true,
					);
				}
				$groups[] = array(
					'title' => isset( $g['title'] ) ? (string) $g['title'] : '',
					'kind'  => isset( $g['kind'] ) ? (string) $g['kind'] : 'custom',
					'links' => $links,
				);
			}
			return $groups;
		}
	}

	return array();
}

/**
 * Colonnes de liens du pied de page effectivement rendues : les groupes de la
 * source normalisée (liens désactivés et groupes vides écartés), ou, à défaut,
 * les colonnes par défaut. Les libellés passent par le module de traduction
 * Polylang (chaînes enregistrées via fnc_register_pll_strings()).
 *
 * @return array<int,array{title:string,kind:string,links:array<int,array<string,mixed>>}>
 */
function fnc_footer_columns() {
	$columns = array();

	foreach ( fnc_footer_groups_source() as $group ) {
		$links = array();
		foreach ( $group['links'] as $l ) {
			if ( empty( $l['enabled'] ) ) {
				continue;
			}
			$l['label']     = fnc_pll( $l['label'] );
			$l['ariaLabel'] = '' !== $l['ariaLabel'] ? fnc_pll( $l['ariaLabel'] ) : '';
			$links[]        = $l;
		}
		if ( '' === $group['title'] && empty( $links ) ) {
			continue;
		}
		$columns[] = array(
			'title' => fnc_pll( $group['title'] ),
			'kind'  => $group['kind'],
			'links' => $links,
		);
	}

	return ! empty( $columns ) ? $columns : fnc_footer_default_columns();
}

/**
 * Réordonne une liste de pays selon l'ordre éditorial : d'abord les pays
 * listés dans le réglage (dans l'ordre de saisie, s'ils sont présents), puis
 * les pays restants par ordre alphabétique. Réglage vide → tri alphabétique.
 *
 * @param array<int,string> $countries Pays effectivement présents.
 * @return array<int,string>
 */
function fnc_order_countries( array $countries ) {
	$order = fnc_parse_country_order();
	if ( empty( $order ) ) {
		sort( $countries );
		return $countries;
	}

	// Index des pays présents par clé normalisée (garde le libellé d'origine).
	$present = array();
	foreach ( $countries as $country ) {
		$present[ fnc_country_key( $country ) ] = $country;
	}

	$ordered = array();
	foreach ( $order as $entry ) {
		$key = fnc_country_key( $entry['name'] );
		if ( isset( $present[ $key ] ) ) {
			$ordered[] = $present[ $key ];
			unset( $present[ $key ] );
		}
	}

	$remaining = array_values( $present );
	sort( $remaining );

	return array_merge( $ordered, $remaining );
}

/**
 * Sanitize d'un <select> restreint à une liste de valeurs autorisées.
 */
function fnc_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	if ( $control && isset( $control->choices[ $value ] ) ) {
		return $value;
	}
	return $setting->default;
}

/**
 * Enregistrement des réglages dans le Customizer.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function fnc_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'fnc_settings',
		array(
			'title'       => __( 'Réglages FNC', 'fnc-wordpress-theme' ),
			'description' => __( 'Identité, logos, coordonnées, footer et valeurs SEO par défaut du portail. Administrable sans développeur.', 'fnc-wordpress-theme' ),
			'priority'    => 20,
		)
	);

	/* -------------------------------------------------- Identité */
	$wp_customize->add_section(
		'fnc_identity',
		array(
			'title' => __( 'Identité', 'fnc-wordpress-theme' ),
			'panel' => 'fnc_settings',
		)
	);

	$identity_fields = array(
		'official_name' => array( __( 'Nom officiel', 'fnc-wordpress-theme' ), 'text', 'Forum Numérique Congo' ),
		'slogan'        => array( __( 'Slogan', 'fnc-wordpress-theme' ), 'text', '' ),
		'subtitle'      => array( __( 'Sous-titre', 'fnc-wordpress-theme' ), 'text', '' ),
		'description'   => array( __( 'Description', 'fnc-wordpress-theme' ), 'textarea', '' ),
		'short_intro'   => array( __( 'Présentation courte', 'fnc-wordpress-theme' ), 'textarea', '' ),
	);
	foreach ( $identity_fields as $key => $conf ) {
		list( $label, $type, $default ) = $conf;
		$sanitize = 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field';
		$wp_customize->add_setting(
			'fnc_' . $key,
			array( 'default' => $default, 'sanitize_callback' => $sanitize, 'transport' => 'refresh' )
		);
		$wp_customize->add_control(
			'fnc_' . $key,
			array( 'label' => $label, 'section' => 'fnc_identity', 'type' => $type )
		);
	}

	/* -------------------------------------------------- Logos & icônes */
	$wp_customize->add_section(
		'fnc_logos',
		array(
			'title'       => __( 'Logos & icônes', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_settings',
			'description' => __( 'Le favicon se règle via Réglages généraux → Icône du site (fonction WordPress native).', 'fnc-wordpress-theme' ),
		)
	);

	$logo_fields = array(
		'logo_principal' => __( 'Logo principal', 'fnc-wordpress-theme' ),
		'logo_light'     => __( 'Logo clair', 'fnc-wordpress-theme' ),
		'logo_dark'      => __( 'Logo sombre', 'fnc-wordpress-theme' ),
	);
	foreach ( $logo_fields as $key => $label ) {
		$wp_customize->add_setting(
			'fnc_' . $key,
			array( 'default' => 0, 'sanitize_callback' => 'absint', 'transport' => 'refresh' )
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'fnc_' . $key,
				array( 'label' => $label, 'section' => 'fnc_logos', 'mime_type' => 'image' )
			)
		);
	}

	/* -------------------------------------------------- Communication */
	$wp_customize->add_section(
		'fnc_communication',
		array(
			'title' => __( 'Communication', 'fnc-wordpress-theme' ),
			'panel' => 'fnc_settings',
		)
	);

	$wp_customize->add_setting( 'fnc_email', array( 'default' => '', 'sanitize_callback' => 'sanitize_email', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'fnc_email', array( 'label' => __( 'Email de contact', 'fnc-wordpress-theme' ), 'section' => 'fnc_communication', 'type' => 'email' ) );

	$wp_customize->add_setting( 'fnc_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'fnc_phone', array( 'label' => __( 'Téléphone', 'fnc-wordpress-theme' ), 'section' => 'fnc_communication', 'type' => 'text' ) );

	$wp_customize->add_setting( 'fnc_address', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'fnc_address', array( 'label' => __( 'Adresse', 'fnc-wordpress-theme' ), 'section' => 'fnc_communication', 'type' => 'textarea' ) );

	$social_fields = array(
		'social_linkedin'  => 'LinkedIn',
		'social_x'         => 'X (Twitter)',
		'social_facebook'  => 'Facebook',
		'social_instagram' => 'Instagram',
		'social_youtube'   => 'YouTube',
	);
	foreach ( $social_fields as $key => $label ) {
		$wp_customize->add_setting(
			'fnc_' . $key,
			array( 'default' => '', 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' )
		);
		$wp_customize->add_control(
			'fnc_' . $key,
			array(
				/* translators: %s: nom de la plateforme sociale. */
				'label'   => sprintf( __( 'URL %s', 'fnc-wordpress-theme' ), $label ),
				'section' => 'fnc_communication',
				'type'    => 'url',
			)
		);
	}

	/* -------------------------------------------------- Contacts presse */
	$wp_customize->add_section(
		'fnc_press',
		array(
			'title'       => __( 'Contacts presse', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_settings',
			'description' => __( 'Un contact par ligne, champs séparés par « | » dans l’ordre : Nom | Rôle | Organisation | Email | Téléphone. Affichés uniquement lorsqu’ils sont renseignés — ne jamais saisir de données fictives.', 'fnc-wordpress-theme' ),
		)
	);
	$wp_customize->add_setting( 'fnc_press_contacts', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_press_contacts',
		array(
			'label'       => __( 'Contacts presse', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_press',
			'type'        => 'textarea',
			'input_attrs' => array( 'placeholder' => "Nom | Rôle | Organisation | email@exemple.org | +242 …", 'rows' => 5 ),
		)
	);

	/* -------------------------------------------------- Footer */
	$wp_customize->add_section(
		'fnc_footer',
		array(
			'title'       => __( 'Footer', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_settings',
			'description' => __( 'L’année courante est ajoutée automatiquement à la mention de copyright.', 'fnc-wordpress-theme' ),
		)
	);
	$wp_customize->add_setting(
		'fnc_footer_text',
		array(
			'default'           => __( 'Institution permanente de réflexion sur l’avenir numérique de l’Afrique centrale. Brazzaville.', 'fnc-wordpress-theme' ),
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control( 'fnc_footer_text', array( 'label' => __( 'Texte du footer', 'fnc-wordpress-theme' ), 'section' => 'fnc_footer', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'fnc_footer_copyright', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_footer_copyright',
		array(
			'label'       => __( 'Mention de copyright', 'fnc-wordpress-theme' ),
			'description' => __( 'Sans l’année (ajoutée automatiquement). Vide → nom officiel utilisé.', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_footer',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting( 'fnc_footer_link_groups', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_footer_link_groups',
		array(
			'label'       => __( 'Groupes de liens du pied de page', 'fnc-wordpress-theme' ),
			'description' => __( 'Une ligne d’en-tête « Titre | genre » ouvre un groupe (genre optionnel : main, resources, press, legal, useful, institutional, custom). Les lignes « - Libellé | lien » qui suivent sont ses liens, dans l’ordre. Options en 3ᵉ champ : « newtab » (nouvel onglet), « off » (masqué). Une ligne « // … » est une note interne. Vide → colonnes par défaut du thème (jamais de pied de page vide).', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_footer',
			'type'        => 'textarea',
			'input_attrs' => array(
				'placeholder' => "Le Forum | main\n- Présentation | /le-forum\n- Programme | /programme\n\nRessources | resources\n- Éditions | /editions\n- Dossier presse | https://… | newtab",
				'rows'        => 10,
			),
		)
	);

	/* -------------------------------------------------- Intervenants */
	$wp_customize->add_section(
		'fnc_speakers',
		array(
			'title'       => __( 'Intervenants', 'fnc-wordpress-theme' ),
			'panel'       => 'fnc_settings',
			'description' => __( 'Ordonne la frise « Pays représentés » de la page Intervenants. Un pays par ligne, dans l’ordre voulu, avec un drapeau optionnel : « Pays | URL du drapeau ». Les pays non listés apparaissent ensuite, par ordre alphabétique. Vide → tri alphabétique. Sans URL de drapeau, le drapeau intégré est utilisé si le pays est reconnu.', 'fnc-wordpress-theme' ),
		)
	);
	$wp_customize->add_setting( 'fnc_country_order', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_country_order',
		array(
			'label'       => __( 'Ordre des pays représentés', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_speakers',
			'type'        => 'textarea',
			'input_attrs' => array( 'placeholder' => "Congo\nFrance | https://…/drapeau-fr.svg\nCameroun", 'rows' => 6 ),
		)
	);

	/* -------------------------------------------------- SEO par défaut */
	$wp_customize->add_section(
		'fnc_seo',
		array(
			'title' => __( 'SEO par défaut', 'fnc-wordpress-theme' ),
			'panel' => 'fnc_settings',
		)
	);
	$wp_customize->add_setting( 'fnc_seo_default_title', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_seo_default_title',
		array(
			'label'       => __( 'Titre par défaut', 'fnc-wordpress-theme' ),
			'description' => __( 'Recommandé : 50–60 caractères.', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_seo',
			'type'        => 'text',
		)
	);
	$wp_customize->add_setting( 'fnc_seo_default_description', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_seo_default_description',
		array(
			'label'       => __( 'Description par défaut', 'fnc-wordpress-theme' ),
			'description' => __( 'Recommandé : 150–160 caractères.', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_seo',
			'type'        => 'textarea',
		)
	);
	$wp_customize->add_setting( 'fnc_og_default_image', array( 'default' => 0, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'fnc_og_default_image',
			array(
				'label'       => __( 'Image OpenGraph par défaut', 'fnc-wordpress-theme' ),
				'description' => __( 'Visuel de partage social (1200×630 recommandé).', 'fnc-wordpress-theme' ),
				'section'     => 'fnc_seo',
				'mime_type'   => 'image',
			)
		)
	);
	$wp_customize->add_setting( 'fnc_twitter_card', array( 'default' => 'summary_large_image', 'sanitize_callback' => 'fnc_sanitize_choice', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_twitter_card',
		array(
			'label'   => __( 'Type de Twitter Card', 'fnc-wordpress-theme' ),
			'section' => 'fnc_seo',
			'type'    => 'select',
			'choices' => array(
				'summary_large_image' => __( 'Grande image', 'fnc-wordpress-theme' ),
				'summary'             => __( 'Résumé', 'fnc-wordpress-theme' ),
			),
		)
	);
	$wp_customize->add_setting( 'fnc_robots', array( 'default' => 'index, follow', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control(
		'fnc_robots',
		array(
			'label'       => __( 'Directive robots par défaut', 'fnc-wordpress-theme' ),
			'description' => __( 'Ex. « index, follow » ou « noindex, nofollow ».', 'fnc-wordpress-theme' ),
			'section'     => 'fnc_seo',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'fnc_customize_register' );
