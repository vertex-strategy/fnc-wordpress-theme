<?php
/**
 * Forum Numérique Congo — gabarit de la page « Contact ».
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/*
 * Lot 2 — composition par blocs. Des que l'editorial compose cette page avec
 * des blocs FNC, ce gabarit s'efface ; sinon il porte le contenu institutionnel
 * aligne sur le site du Forum.
 */
/*
 * Lot 3 — l’édition des pages (ACF Flexible Content). Sections ACF « institutionnel »
 * prioritaires sur le contenu de demonstration. Voir inc/page-sections.php.
 */
if ( function_exists( 'fnc_render_page_sections' ) ) {
	$fnc_sections_html = fnc_render_page_sections( get_queried_object_id(), 'institutional' );
	if ( '' !== $fnc_sections_html ) {
		echo '<main id="main">' . $fnc_sections_html . '</main>'; // phpcs:ignore WordPress.Security.EscapeOutput -- HTML echappe dans les fonctions de rendu.
		get_footer();
		return;
	}
}

if ( function_exists( 'fnc_page_has_blocks' ) && fnc_page_has_blocks() ) {
	while ( have_posts() ) {
		the_post();
		echo '<main id="main">';
		the_content();
		echo '</main>';
	}
	get_footer();
	return;
}

?>

<main id="main">
	<?php
	// Aucune section ACF ni bloc FNC : état NEUTRE « en préparation »
	// (parité Next « non publié »). Jamais de contenu de démonstration en dur.
	fnc_render_pagehead(
		array(
			'eyebrow'    => __( 'Contact', 'fnc-wordpress-theme' ),
			'title'      => get_the_title(),
			'breadcrumb' => get_the_title(),
		)
	);
	?>
	<section class="section">
		<div class="container">
			<p class="help"><?php esc_html_e( 'Cette page est en cours de préparation.', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
		</div>
	</section>
</main>

<?php
get_footer();
