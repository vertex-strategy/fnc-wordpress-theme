<?php
/**
 * FNC WordPress Theme — fonctions du theme.
 *
 * Voir ADR-007 (depot forum-numerique-congo) pour le contexte produit :
 * template vitrine parallele, derive de docs/mockups/homepage-v2.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_THEME_VERSION', '0.3.0' );

/**
 * Réglages globaux du site (WordPress Customizer) — pendant du Global
 * « Réglages du site » de Payload. Voir inc/customizer.php.
 */
require_once get_template_directory() . '/inc/customizer.php';

/**
 * Blocs éditoriaux (composition de pages) — pendant des palettes de blocs
 * Payload (institutionalSections / pageSections). Voir inc/blocks.php.
 */
require_once get_template_directory() . '/inc/blocks.php';

/**
 * Page d'accueil editable — storyboard fige M1 -> M8 (heros image/video/
 * slider inclus). Voir inc/homepage.php.
 */
require_once get_template_directory() . '/inc/homepage.php';

/**
 * Theme setup : support des fonctionnalites WordPress utilisees par les gabarits.
 */
function fnc_theme_setup() {
	load_theme_textdomain( 'fnc-wordpress-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus(
		array(
			'primary' => __( 'Navigation principale', 'fnc-wordpress-theme' ),
			'mobile'  => __( 'Navigation mobile', 'fnc-wordpress-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'fnc_theme_setup' );

/**
 * Enqueue des styles et scripts du theme.
 */
function fnc_theme_assets() {
	wp_enqueue_style( 'fnc-theme-style', get_stylesheet_uri(), array(), FNC_THEME_VERSION );
	wp_enqueue_script( 'fnc-theme-main', get_template_directory_uri() . '/assets/js/main.js', array(), FNC_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'fnc_theme_assets' );

/**
 * URL d'une Page WordPress publiee par son slug, avec repli sur "#" si
 * elle n'existe pas encore (installation fraiche du theme sans contenu).
 */
function fnc_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : '#';
}

/**
 * URL de l'archive d'un custom post type, avec le meme repli sur "#".
 */
function fnc_archive_url( $post_type ) {
	$url = get_post_type_archive_link( $post_type );
	return $url ? $url : '#';
}

/**
 * Items de navigation principale, alignes sur le site officiel reel
 * (localhost:3000/fr) suite a l'amendement de la Decision 1 de
 * l'ADR-007 : 6 items (Le Forum, Edition en cours, Editions,
 * Ressources, Partenaires, Contact), pas les ancres de la maquette
 * mono-page d'origine. Liens reels vers les Pages/archives creees
 * dans cette instance ; reste sur "#" pour celles qui n'existent pas
 * encore.
 *
 * @return array Liste de [url, libelle].
 */
function fnc_default_menu_items() {
	return array(
		array( fnc_page_url( 'le-forum' ), __( 'Le Forum', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'edition-en-cours' ), __( 'Édition en cours', 'fnc-wordpress-theme' ) ),
		array( fnc_archive_url( 'fnc_edition' ), __( 'Éditions', 'fnc-wordpress-theme' ) ),
		array( fnc_archive_url( 'fnc_publication' ), __( 'Ressources', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'partenaires' ), __( 'Partenaires', 'fnc-wordpress-theme' ) ),
		array( fnc_page_url( 'contact' ), __( 'Contact', 'fnc-wordpress-theme' ) ),
	);
}

/**
 * Menu de repli pour l'emplacement "primary", tant qu'aucun menu
 * WordPress n'est configure dans l'administration (Apparence > Menus).
 */
function fnc_default_primary_menu() {
	echo '<ul>';
	foreach ( fnc_default_menu_items() as $fnc_item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $fnc_item[0] ), esc_html( $fnc_item[1] ) );
	}
	echo '</ul>';
}

/**
 * Variante du menu de repli pour le panneau mobile : liens a plat, sans
 * <ul>/<li>, conformement a la structure de la maquette source (le CSS
 * `.mobile-panel a` cible des liens directs, pas une liste).
 */
function fnc_default_mobile_menu() {
	foreach ( fnc_default_menu_items() as $fnc_item ) {
		printf( '<a href="%s">%s</a>', esc_url( $fnc_item[0] ), esc_html( $fnc_item[1] ) );
	}
}

/**
 * Selecteur de langue.
 *
 * Si Polylang est actif (ADR-007, Decision 2 — dependance ciblee acceptee
 * uniquement pour le multilinguisme), affiche les langues reellement
 * configurees. Sinon, degrade gracieusement vers le rendu statique FR/EN
 * de la maquette source (pas de contenu invente, pas de lien casse).
 */
function fnc_language_switcher() {
	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages(
			array(
				'raw'               => true,
				'hide_if_no_translation' => 0,
			)
		);

		if ( ! empty( $languages ) ) {
			echo '<div class="lang" role="group" aria-label="' . esc_attr__( 'Langue', 'fnc-wordpress-theme' ) . '">';
			$count = count( $languages );
			$i     = 0;
			foreach ( $languages as $lang ) {
				++$i;
				printf(
					'<a href="%s" aria-current="%s">%s</a>',
					esc_url( $lang['url'] ),
					$lang['current_lang'] ? 'true' : 'false',
					esc_html( strtoupper( $lang['slug'] ) )
				);
				if ( $i < $count ) {
					echo '<span class="sep" aria-hidden="true">·</span>';
				}
			}
			echo '</div>';
			return;
		}
	}

	// Repli statique — decoratif tant que Polylang n'est pas configure.
	echo '<div class="lang" role="group" aria-label="' . esc_attr__( 'Langue', 'fnc-wordpress-theme' ) . '">';
	echo '<button aria-pressed="true">FR</button><span class="sep" aria-hidden="true">·</span><button aria-pressed="false">EN</button>';
	echo '</div>';
}

/**
 * Bloc "hero" secondaire partage par les pages interieures generees a
 * partir de docs/mockups/homepage-v2/site.js (contact, archives, etc.).
 * Distinct du bloc "opening" utilise par page-le-forum.php (maquette
 * interior/le-forum.html, systeme de gabarit different mais memes tokens
 * de couleur).
 *
 * @param array $args {
 *     @type string $eyebrow    Texte au-dessus du titre.
 *     @type string $title      Titre H1.
 *     @type string $lead       Texte d'introduction.
 *     @type string $image      URL de l'image de fond.
 *     @type string $image_alt  Texte alternatif de l'image.
 *     @type string $breadcrumb Libelle du fil d'ariane (optionnel).
 * }
 */
function fnc_render_hero( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow'    => '',
			'title'      => '',
			'lead'       => '',
			'image'      => '',
			'image_alt'  => '',
			'breadcrumb' => '',
		)
	);
	?>
	<section class="hero secondary">
		<img src="<?php echo esc_url( $args['image'] ); ?>" alt="<?php echo esc_attr( $args['image_alt'] ); ?>" />
		<div class="hero-inner">
			<?php if ( $args['breadcrumb'] ) : ?>
				<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> / <?php echo esc_html( $args['breadcrumb'] ); ?></p>
			<?php endif; ?>
			<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
			<h1><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['lead'] ) : ?>
				<p class="lead"><?php echo esc_html( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>
		<svg class="trace" viewBox="0 0 1200 96" preserveAspectRatio="none" aria-hidden="true">
			<path class="r" d="M0 68 H390 l22 -28 H792 l24 28 H1200"/>
			<path class="y" d="M0 34 H288 l28 30 H904 l22 -18 H1200"/>
		</svg>
	</section>
	<?php
}

/**
 * Bloc d'en-tete sobre pour les pages legales (mentions, confidentialite,
 * CGU), aligne sur le site officiel reel : pas de photo, juste un fil
 * d'ariane, une date de mise a jour et un titre — a la difference du
 * bloc "hero" utilise par les autres pages interieures.
 */
function fnc_render_legal_header( $title, $updated_label, $breadcrumb ) {
	?>
	<header class="section" style="padding-top:calc(clamp(64px,9vh,118px) + 60px);">
		<div class="container reading">
			<p class="breadcrumb" style="color:var(--texte-tert);"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php echo esc_html( $breadcrumb ); ?></p>
			<p class="eyebrow"><?php echo esc_html( $updated_label ); ?></p>
			<h1 style="font-size:var(--h2);color:var(--navy);"><?php echo esc_html( $title ); ?></h1>
		</div>
	</header>
	<?php
}

/**
 * Bandeau CTA final partage par les pages interieures (site.js: finalCta()).
 */
function fnc_render_cta_band() {
	?>
	<section class="cta-band">
		<h2><?php esc_html_e( 'Poursuivre le parcours.', 'fnc-wordpress-theme' ); ?></h2>
		<p><?php esc_html_e( 'Les contenus non confirmés restent masqués ou signalés.', 'fnc-wordpress-theme' ); ?></p>
		<a class="btn btn-red" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contacter l’équipe', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
		</a>
	</section>
	<?php
}

/**
 * Carte editoriale partagee (site.js: card()).
 */
function fnc_render_card( $title, $body, $kicker = '' ) {
	?>
	<article class="card">
		<?php if ( $kicker ) : ?>
			<p class="card-kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<h3><?php echo esc_html( $title ); ?></h3>
		<p><?php echo esc_html( $body ); ?></p>
	</article>
	<?php
}

/**
 * Passe gabarits (reconciliation du modele de contenu, cf. plugin
 * fnc-content-model v0.2.0) : petits utilitaires d'affichage partages par
 * les gabarits qui consomment desormais les nouveaux champs meta.
 */

/**
 * Badge non interactif (type de session, type de publication, niveau de
 * partenariat...), visuellement proche de `.chip` mais jamais cliquable.
 */
function fnc_render_badge( $label ) {
	if ( ! $label ) {
		return;
	}
	printf( '<span class="badge">%s</span>', esc_html( $label ) );
}

/**
 * Nom d'affichage d'un intervenant : "Civilité Nom" si une civilite est
 * renseignee (_fnc_speaker_title), sinon le titre du post seul.
 */
function fnc_speaker_display_name( $speaker_id ) {
	$title_civility = get_post_meta( $speaker_id, FNC_META_SPEAKER_TITLE, true );
	$name           = get_the_title( $speaker_id );
	return $title_civility ? trim( $title_civility . ' ' . $name ) : $name;
}

/**
 * Ligne "organisation · pays" d'un intervenant, sans separateur superflu si
 * l'un des deux champs est vide.
 */
function fnc_speaker_meta_line( $speaker_id ) {
	$org     = get_post_meta( $speaker_id, FNC_META_SPEAKER_ORG, true );
	$country = get_post_meta( $speaker_id, FNC_META_SPEAKER_COUNTRY, true );
	return trim( implode( ' · ', array_filter( array( $org, $country ) ) ) );
}

/**
 * Drapeaux SVG inline des pays representes au Forum — memes pays et memes
 * geometries simplifiees que le composant CountryFlag.tsx du site officiel
 * (forum-numerique-congo/src/app/(frontend)/[locale]/intervenants/CountryFlag.tsx),
 * transposees en PHP puisque zero dependance tierce (ADR-007, Decision 2)
 * exclut d'importer un paquet de drapeaux.
 */
function fnc_country_flag_svg( $country ) {
	$country = trim( (string) $country );
	$flags   = array(
		'France'        => '<rect width="20" height="40" fill="#002654"/><rect x="20" width="20" height="40" fill="#FFFFFF"/><rect x="40" width="20" height="40" fill="#ED2939"/>',
		'Belgique'      => '<rect width="20" height="40" fill="#000000"/><rect x="20" width="20" height="40" fill="#FAE042"/><rect x="40" width="20" height="40" fill="#ED2939"/>',
		'Luxembourg'    => '<rect width="60" height="13.34" fill="#ED2939"/><rect y="13.34" width="60" height="13.33" fill="#FFFFFF"/><rect y="26.67" width="60" height="13.33" fill="#00A1DE"/>',
		'Sénégal'       => '<rect width="20" height="40" fill="#00853F"/><rect x="20" width="20" height="40" fill="#FDEF42"/><rect x="40" width="20" height="40" fill="#E31B23"/>',
		'Cameroun'      => '<rect width="20" height="40" fill="#007A5E"/><rect x="20" width="20" height="40" fill="#CE1126"/><rect x="40" width="20" height="40" fill="#FCD116"/>',
		'Congo'         => '<polygon points="0,0 60,0 0,40" fill="#009543"/><polygon points="60,0 60,40 0,40" fill="#DC241F"/><polygon points="0,40 0,26 46,0 60,0 60,14 14,40" fill="#FBDE4A"/>',
		'RDC'           => '<rect width="60" height="40" fill="#007FFF"/><line x1="0" y1="42" x2="62" y2="-2" stroke="#F7D618" stroke-width="14"/><line x1="0" y1="42" x2="62" y2="-2" stroke="#CE1021" stroke-width="8"/>',
		'Royaume-Uni'   => '<rect width="60" height="40" fill="#012169"/><path d="M0,0 60,40 M60,0 0,40" stroke="#FFFFFF" stroke-width="8"/><path d="M0,0 60,40 M60,0 0,40" stroke="#C8102E" stroke-width="4"/><rect x="24" width="12" height="40" fill="#FFFFFF"/><rect y="14" width="60" height="12" fill="#FFFFFF"/><rect x="26" width="8" height="40" fill="#C8102E"/><rect y="16" width="60" height="8" fill="#C8102E"/>',
		'États-Unis'    => '<rect width="60" height="40" fill="#B22234"/><rect width="60" height="20" fill="#FFFFFF"/><rect width="24" height="20" fill="#3C3B6E"/>',
		'Inde'          => '<rect width="60" height="13.34" fill="#FF9933"/><rect y="13.34" width="60" height="13.33" fill="#FFFFFF"/><rect y="26.67" width="60" height="13.33" fill="#138808"/><circle cx="30" cy="20" r="5.2" fill="none" stroke="#000080" stroke-width="1"/>',
		'Côte d’Ivoire' => '<rect width="20" height="40" fill="#F77F00"/><rect x="20" width="20" height="40" fill="#FFFFFF"/><rect x="40" width="20" height="40" fill="#009E60"/>',
	);

	if ( ! isset( $flags[ $country ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="flag-svg" viewBox="0 0 60 40" role="img" aria-label="%s">%s</svg>',
		esc_attr( $country ),
		$flags[ $country ]
	);
}

/**
 * Decoupe le champ texte libre `_fnc_speaker_country` (ex. "France / États-Unis")
 * en une liste de pays, meme convention que le site officiel (SpeakersExplorer.tsx).
 */
function fnc_split_countries( $country_field ) {
	if ( ! $country_field ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', explode( '/', $country_field ) ) ) );
}

/**
 * Nom du site : nom officiel des Réglages FNC, repli sur le nom WordPress.
 */
function fnc_site_name() {
	return fnc_get_setting( 'official_name', get_bloginfo( 'name' ) );
}

/**
 * Métadonnées SEO/OpenGraph dans le <head>, alimentées par les valeurs par
 * défaut des Réglages FNC (onglet « SEO par défaut »). Pendant WordPress des
 * valeurs SEO du Global Settings de Payload. La surcharge SEO par page/document
 * relève d'un lot ultérieur (blocs / meta de page).
 */
function fnc_head_meta() {
	$site_name   = fnc_site_name();
	$default_desc = fnc_get_setting( 'seo_default_description', fnc_get_setting( 'description', '' ) );
	$og_title    = fnc_get_setting( 'seo_default_title', '' );
	$twitter     = fnc_get_setting( 'twitter_card', 'summary_large_image' );
	$og_image    = fnc_get_setting_image_url( 'og_default_image', 'full' );

	// Titre OG : titre du document courant si disponible, sinon titre par défaut.
	if ( is_singular() ) {
		$og_title = get_the_title();
	} elseif ( ! $og_title ) {
		$og_title = $site_name;
	}

	// La directive robots passe par le filtre natif wp_robots (voir
	// fnc_filter_robots) pour éviter une seconde balise <meta name="robots">.
	if ( $default_desc ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $default_desc ) );
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $default_desc ) );
	}
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $og_title ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( ( is_front_page() ? home_url( '/' ) : get_permalink() ) ?: home_url( '/' ) ) );
	if ( $og_image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $og_image ) );
	}
	if ( $twitter ) {
		printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( $twitter ) );
	}
}
add_action( 'wp_head', 'fnc_head_meta', 5 );

/**
 * Applique la directive robots des Réglages FNC via le filtre natif wp_robots
 * (plutôt qu'une balise en dur), pour ne pas dupliquer <meta name="robots">.
 * « index, follow » = comportement indexable par défaut (rien à forcer) ;
 * « noindex » / « nofollow » sont propagés à WordPress.
 *
 * @param array<string,mixed> $robots
 * @return array<string,mixed>
 */
function fnc_filter_robots( $robots ) {
	$directive = fnc_get_setting( 'robots', '' );
	if ( ! $directive ) {
		return $robots;
	}
	$tokens = array_map( 'trim', explode( ',', strtolower( $directive ) ) );
	if ( in_array( 'noindex', $tokens, true ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}
	if ( in_array( 'index', $tokens, true ) ) {
		unset( $robots['noindex'] );
	}
	if ( in_array( 'nofollow', $tokens, true ) ) {
		$robots['nofollow'] = true;
		unset( $robots['follow'] );
	}
	if ( in_array( 'follow', $tokens, true ) ) {
		unset( $robots['nofollow'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'fnc_filter_robots' );

/**
 * Marque de l'en-tête : logo principal des Réglages FNC s'il est défini, sinon
 * le sigle SVG intégré. Retourne le HTML du logo <img> ou une chaîne vide.
 */
function fnc_header_logo_img() {
	$url = fnc_get_setting_image_url( 'logo_principal', 'full' );
	if ( ! $url ) {
		return '';
	}
	return sprintf(
		'<img class="brand-logo" src="%s" alt="%s" />',
		esc_url( $url ),
		esc_attr( fnc_site_name() )
	);
}
