<?php
/**
 * Gabarit de page — "Mentions légales".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/mentions-legales), suite a l'amendement de la
 * Decision 1 de l'ADR-007 : pas de photo hero, juste un fil d'ariane,
 * une date de mise a jour et un titre. Contenu marque [DEMO] — meme
 * convention que le site officiel reel pour signaler un texte non
 * valide juridiquement, jamais presente comme un fait etabli.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_legal_header(
	__( 'Mentions légales', 'fnc-wordpress-theme' ),
	__( 'Mise à jour le à confirmer', 'fnc-wordpress-theme' ),
	__( 'Mentions légales', 'fnc-wordpress-theme' )
);
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Éditeur du site : nom de l’organisation éditrice — à confirmer.', 'fnc-wordpress-theme' ); ?></p>
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Directeur de la publication, hébergeur et coordonnées légales : à confirmer et valider avant toute publication réelle.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
