<?php
/**
 * Gabarit de page — "Espace presse" (Dossier presse).
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/espace-presse), suite a l'amendement de la
 * Decision 1 de l'ADR-007. Sur le site reel, les communiques de presse
 * sont un type de Ressource/Publication, pas une collection separee —
 * reflete ici en filtrant fnc_publication par la categorie
 * "Communiques" (fnc_categorie).
 *
 * Section "Contacts presse" volontairement statique/etat-vide : le
 * site reel la relie aux "Reglages du site" (une configuration globale
 * hors perimetre du plugin, cf. ADR-007 Decision 2 — collections
 * transactionnelles/reglages non modelisees ici).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Presse', 'fnc-wordpress-theme' ),
		'title'      => __( 'Un point d’accès clair pour couvrir le Forum.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'L’Espace presse rassemble les ressources publiées et les contacts confirmés.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
		'image_alt'  => __( 'Documents préparés dans une atmosphère institutionnelle calme', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Espace presse', 'fnc-wordpress-theme' ),
	)
);

$fnc_communiques_term = get_term_by( 'slug', 'communiques', 'fnc_categorie' );
$fnc_communiques      = $fnc_communiques_term
	? get_posts(
		array(
			'post_type'      => 'fnc_publication',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'fnc_categorie',
					'field'    => 'term_id',
					'terms'    => $fnc_communiques_term->term_id,
				),
			),
		)
	)
	: array();
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<p class="eyebrow"><?php esc_html_e( 'Information', 'fnc-wordpress-theme' ); ?></p>
			<h2><?php esc_html_e( 'Des ressources vérifiables, pas un dossier de façade.', 'fnc-wordpress-theme' ); ?></h2>
			<p><?php esc_html_e( 'Les communiqués, documents et contacts presse apparaissent ici lorsqu’ils sont validés.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Communiqués', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Ressources presse publiées.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>
			<?php if ( ! empty( $fnc_communiques ) ) : ?>
				<div class="grid grid-3">
					<?php foreach ( $fnc_communiques as $fnc_communique ) : ?>
						<article class="card">
							<p class="card-kicker"><?php esc_html_e( 'Communiqué', 'fnc-wordpress-theme' ); ?></p>
							<h3><a href="<?php echo esc_url( get_permalink( $fnc_communique ) ); ?>"><?php echo esc_html( get_the_title( $fnc_communique ) ); ?></a></h3>
							<?php if ( has_excerpt( $fnc_communique ) ) : ?>
								<p><?php echo esc_html( get_the_excerpt( $fnc_communique ) ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucun communiqué n’est publié pour le moment.', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les premiers communiqués apparaîtront ici dès leur validation officielle.', 'fnc-wordpress-theme' ); ?></p>
					<div class="meta" style="justify-content:center;"><span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Contacts', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Contacts presse confirmés.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>
			<div class="empty" role="status">
				<h3><?php esc_html_e( 'Les contacts presse ne sont pas encore publiés.', 'fnc-wordpress-theme' ); ?></h3>
				<p><?php esc_html_e( 'Aucune adresse ni aucun nom ne sont affichés tant qu’ils ne sont pas validés.', 'fnc-wordpress-theme' ); ?></p>
				<div class="meta" style="justify-content:center;"><span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></div>
			</div>
		</div>
	</section>

	<section class="section linen">
		<div class="container reading">
			<p class="eyebrow"><?php esc_html_e( 'Accréditation', 'fnc-wordpress-theme' ); ?></p>
			<h2><?php esc_html_e( 'L’accréditation reste un échange direct.', 'fnc-wordpress-theme' ); ?></h2>
			<p><?php esc_html_e( 'Les journalistes peuvent utiliser la page Contact pour toute demande d’accréditation.', 'fnc-wordpress-theme' ); ?></p>
			<p style="margin-top:20px;"><a class="link-more" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contacter l’organisation', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
