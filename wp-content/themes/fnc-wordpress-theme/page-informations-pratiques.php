<?php
/**
 * Gabarit de page — "Informations pratiques".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/infos-pratiques), suite a l'amendement de la
 * Decision 1 de l'ADR-007.
 *
 * Lot 4 : les informations pratiques ne sont ni un texte libre ni une
 * collection autonome, mais un AGREGAT DE RUBRIQUES rattache a l'objet
 * pivot Edition (meme modelisation que le vrai site, practicalInfo.ts).
 * Cette page rend donc les rubriques de l'edition en cours ; chaque
 * rubrique est optionnelle et masquee si vide. Tant qu'aucune n'est
 * renseignee, l'etat d'attente sobre du site reel est conserve.
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

// Edition en cours : porteuse des rubriques pratiques.
$fnc_ip_edition = get_posts(
	array(
		'post_type'      => 'fnc_edition',
		'posts_per_page' => 1,
		'meta_key'       => '_fnc_edition_active',
		'meta_value'     => '1',
	)
);
$fnc_ip_edition   = ! empty( $fnc_ip_edition ) ? $fnc_ip_edition[0] : null;
$fnc_ip_rubriques = $fnc_ip_edition ? fnc_render_practical_info( $fnc_ip_edition ) : '';
?>

<main id="main">
	<?php if ( '' !== trim( $fnc_ip_rubriques ) ) : ?>
		<section class="section">
			<div class="container">
				<div class="section-head">
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Sur place', 'fnc-wordpress-theme' ); ?></p>
						<h2><?php echo esc_html( get_the_title( $fnc_ip_edition ) ); ?></h2>
					</div>
					<p><?php esc_html_e( 'Seules les rubriques confirmées sont publiées.', 'fnc-wordpress-theme' ); ?></p>
				</div>
				<div class="pract-grid">
					<?php echo $fnc_ip_rubriques; // phpcs:ignore WordPress.Security.EscapeOutput -- markup produit par les renderers de blocs, deja echappe. ?>
				</div>
			</div>
		</section>
	<?php else : ?>
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
	<?php endif; ?>

	<section class="cta-band">
		<h2><?php esc_html_e( 'Prêt à participer ?', 'fnc-wordpress-theme' ); ?></h2>
		<p><?php esc_html_e( 'Rejoignez la prochaine édition du Forum Numérique Congo.', 'fnc-wordpress-theme' ); ?></p>
		<a class="btn btn-red" href="#"><?php esc_html_e( 'S’inscrire', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
