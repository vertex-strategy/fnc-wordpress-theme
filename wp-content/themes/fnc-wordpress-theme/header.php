<?php
/**
 * En-tete du theme : <head>, navigation desktop, panneau mobile.
 * Porte la navigation de docs/mockups/homepage-v2/index.html (ADR-007).
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
<a class="skip" href="#m1"><?php esc_html_e( 'Aller au contenu', 'fnc-wordpress-theme' ); ?></a>

<header class="nav" id="nav">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Forum Numérique Congo — accueil', 'fnc-wordpress-theme' ); ?>">
		<span class="mark" aria-hidden="true">
			<svg viewBox="0 0 32 32">
				<rect x="14.5" y="3" width="3" height="26" fill="currentColor"/>
				<path d="M6 10 C10 6, 22 6, 25 12" stroke="#CC2222" stroke-width="1.5" fill="none"/>
				<circle cx="25" cy="12" r="1.6" fill="#CC2222"/>
				<path d="M7 22 C11 26, 21 26, 24 21" stroke="#F5C000" stroke-width="1.5" fill="none"/>
				<circle cx="24" cy="21" r="1.6" fill="#F5C000"/>
				<path d="M11 13 q4 3 0 6 q-3-3 0-6Z" fill="#3BA04A"/>
			</svg>
		</span>
		<span class="wordmark"><b><?php esc_html_e( 'Forum Numérique', 'fnc-wordpress-theme' ); ?></b><span><?php esc_html_e( 'Congo', 'fnc-wordpress-theme' ); ?></span></span>
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
		<button class="cta" type="button"><?php esc_html_e( 'Accréditation', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</button>
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
