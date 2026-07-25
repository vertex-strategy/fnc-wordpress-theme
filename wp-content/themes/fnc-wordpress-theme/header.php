<?php
/**
 * Forum Numérique Congo — en-tête du site (identité, navigation principale, sélecteur de langue).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Aller au contenu', 'fnc-wordpress-theme' ); ?></a>

<header class="nav<?php echo fnc_has_linen_header() ? ' solid' : ''; ?>" id="nav">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s — accueil', 'fnc-wordpress-theme' ), fnc_site_name() ) ); ?>">
		<?php
		// Logos officiels du Forum Numérique Congo : version claire sur l'en-tête
		// transparent (héros sombre), version couleur sur l'en-tête solide (fond
		// blanc au défilement). Le kit CSS prod fait le fondu selon .nav /
		// .nav.solid. Chaque version reste administrable via Réglages FNC → Logos.
		echo fnc_header_logo_img(); // phpcs:ignore WordPress.Security.EscapeOutput -- markup construit et échappé par fnc_header_logo_img().
		?>
	</a>

	<nav class="nav-links" aria-label="<?php esc_attr_e( 'Navigation principale', 'fnc-wordpress-theme' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '<ul>%3$s</ul>',
				)
			);
		} else {
			fnc_default_primary_menu();
		}
		?>
	</nav>

	<div class="nav-utils">
		<?php fnc_language_switcher(); ?>
		<?php
		// CTA d'inscription pilote par le drapeau REGISTRATION_ENABLED (FNC Core,
		// les options d’affichage) : ouvert → « S'inscrire » vers /inscription ; ferme (defaut) →
		// « Decouvrir l'edition » vers l'edition en cours. Meme seam que
		// fnc_registration_cta() du plugin, en conservant la fleche.
		$fnc_reg_open = ! function_exists( 'fnc_registration_enabled' ) || fnc_registration_enabled();
		$fnc_cta_href = $fnc_reg_open ? fnc_page_url( 'inscription' ) : fnc_page_url( 'edition-en-cours' );
		$fnc_cta_lbl  = $fnc_reg_open ? __( 'S’inscrire', 'fnc-wordpress-theme' ) : __( 'Découvrir l’édition', 'fnc-wordpress-theme' );
		?>
		<a class="cta" href="<?php echo esc_url( $fnc_cta_href ); ?>"><?php echo esc_html( $fnc_cta_lbl ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
		<button class="burger" type="button" id="burger" aria-expanded="false" aria-controls="mobile-panel" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'fnc-wordpress-theme' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
		</button>
	</div>
</header>

<div class="mobile-panel" id="mobile-panel" aria-hidden="true">
	<nav aria-label="<?php esc_attr_e( 'Navigation mobile', 'fnc-wordpress-theme' ); ?>">
		<?php
		if ( has_nav_menu( 'mobile' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'mobile',
					'container'      => false,
					'items_wrap'     => '%3$s',
				)
			);
		} elseif ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
				)
			);
		} else {
			fnc_default_mobile_menu();
		}
		?>
	</nav>
	<div class="mp-utils">
		<?php fnc_language_switcher(); ?>
	</div>
</div>
