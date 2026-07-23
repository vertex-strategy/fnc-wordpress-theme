<?php
/**
 * Gabarit de page — "Conditions générales d'utilisation" (CGU).
 *
 * Structure alignee, par cohérence, sur les deux autres pages legales
 * du site officiel reel (mentions legales, politique de
 * confidentialite) — suite a l'amendement de la Decision 1 de
 * l'ADR-007. Limite constatee : la route reelle
 * localhost:3000/fr/conditions-generales-utilisation a renvoye une
 * page 404 au moment de l'integration (le <title> de la reponse
 * indiquait bien "Conditions generales d'utilisation", mais le corps
 * affichait "Page non trouvee") — impossible de confirmer si cette
 * page existe reellement cote site officiel ou si son contenu differe
 * de la structure ci-dessous. A revoir si la page reelle devient
 * disponible.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_legal_header(
	__( 'Conditions générales d’utilisation', 'fnc-wordpress-theme' ),
	__( 'Mise à jour le à confirmer', 'fnc-wordpress-theme' ),
	__( 'CGU', 'fnc-wordpress-theme' )
);
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Conditions d’usage du site — à confirmer et valider avant toute publication réelle.', 'fnc-wordpress-theme' ); ?></p>
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Page structurelle, à maintenir non publiée tant que le texte n’est pas validé.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
