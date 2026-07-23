<?php
/**
 * Gabarit de page — "Politique de confidentialité".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/politique-confidentialite), suite a l'amendement
 * de la Decision 1 de l'ADR-007. Le site reel decrit une mesure
 * d'audience specifique (Matomo auto-heberge) qui n'est pas integree a
 * ce theme de demonstration : le texte ci-dessous est generique et
 * illustratif, pas une description du fonctionnement reel de ce
 * produit — a adapter si une vraie solution de mesure est ajoutee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_legal_header(
	__( 'Politique de confidentialité', 'fnc-wordpress-theme' ),
	__( 'Mise à jour le à confirmer', 'fnc-wordpress-theme' ),
	__( 'Politique de confidentialité', 'fnc-wordpress-theme' )
);
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Traitement des données personnelles conforme au cadre applicable — à confirmer.', 'fnc-wordpress-theme' ); ?></p>
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Finalités, durée de conservation et droits des personnes : à confirmer et valider avant toute publication réelle.', 'fnc-wordpress-theme' ); ?></p>

			<h2 style="margin-top:32px;font-size:1.4rem;color:var(--navy);"><?php esc_html_e( 'Mesure d’audience', 'fnc-wordpress-theme' ); ?></h2>
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Cette section décrit, à titre d’exemple, une mesure d’audience respectueuse de la vie privée. Ce thème de démonstration n’intègre aucune solution de mesure réelle : à adapter selon l’outil effectivement utilisé avant toute publication.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
