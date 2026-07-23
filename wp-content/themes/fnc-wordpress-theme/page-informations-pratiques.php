<?php
/**
 * Gabarit de page — "Informations pratiques".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/infos-pratiques), suite a l'amendement de la
 * Decision 1 de l'ADR-007 : sur le site reel, cette page est
 * volontairement sobre — un etat d'attente ("en cours de
 * finalisation"), pas une liste de rubriques. Contenu reste
 * generique/structurel, sans texte copie du site officiel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Préparez votre venue', 'fnc-wordpress-theme' ),
		'title'      => __( 'Informations pratiques', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Tout ce qu’il faut pour organiser votre participation — lieu, transport, hébergement, visa, badge et accessibilité.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-badge.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Informations pratiques', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="empty" role="status">
				<h3><?php esc_html_e( 'Informations en cours de finalisation', 'fnc-wordpress-theme' ); ?></h3>
				<p><?php esc_html_e( 'Les détails pratiques de la prochaine édition seront publiés ici dès leur confirmation.', 'fnc-wordpress-theme' ); ?></p>
				<div class="meta" style="justify-content:center;">
					<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<section class="cta-band">
		<h2><?php esc_html_e( 'Prêt à participer ?', 'fnc-wordpress-theme' ); ?></h2>
		<p><?php esc_html_e( 'Rejoignez la prochaine édition du Forum Numérique Congo.', 'fnc-wordpress-theme' ); ?></p>
		<a class="btn btn-red" href="#"><?php esc_html_e( 'S’inscrire', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
