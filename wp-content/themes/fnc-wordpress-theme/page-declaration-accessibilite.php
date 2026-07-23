<?php
/**
 * Gabarit de page — "Déclaration d'accessibilité".
 *
 * Structure alignee, par coherence, sur les autres pages legales du
 * site officiel reel — suite a l'amendement de la Decision 1 de
 * l'ADR-007. Limite constatee : la route reelle
 * localhost:3000/fr/declaration-accessibilite a renvoye une page 404
 * au moment de l'integration (le <title> de la reponse etait
 * correct, mais le corps affichait "Page non trouvee") — meme
 * constat que pour la page CGU. Impossible de confirmer le contenu
 * reel de cette page.
 *
 * Contenu marque [DEMO], coherent avec la maquette source
 * (site.js, copy.accessibility : "Ne jamais declarer conforme sans
 * audit et validation explicite") : ce gabarit ne declare aucune
 * conformite WCAG, il ne fait qu'illustrer la structure attendue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_legal_header(
	__( 'Déclaration d’accessibilité', 'fnc-wordpress-theme' ),
	__( 'Mise à jour le à confirmer', 'fnc-wordpress-theme' ),
	__( 'Déclaration d’accessibilité', 'fnc-wordpress-theme' )
);
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Cette déclaration ne certifie aucune conformité : elle sera rédigée après audit d’accessibilité réel, jamais présentée comme conforme sans validation explicite.', 'fnc-wordpress-theme' ); ?></p>
			<p>[<?php esc_html_e( 'DÉMO', 'fnc-wordpress-theme' ); ?>] <?php esc_html_e( 'Référentiel visé, résultats d’audit, contenus non accessibles et voies de recours : à confirmer et valider avant toute publication réelle.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
