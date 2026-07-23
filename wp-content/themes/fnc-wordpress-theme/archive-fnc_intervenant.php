<?php
/**
 * Archive du custom post type "fnc_intervenant".
 *
 * Porte docs/mockups/homepage-v2/intervenants.html (contenu genere par
 * site.js: speakersPage(), copy.speakers). Branche sur les vraies
 * donnees du plugin fnc-content-model (etape 4 de l'ADR-007) : profils
 * reellement publies, pas les profils d'exemple de la maquette source
 * (examples.people).
 *
 * Le toolbar de filtres ("Institution", "Entreprise", "Recherche",
 * "Presse") de la maquette source n'est pas reproduit ici, pour la meme
 * raison que sur l'archive des sessions : aucune donnee reelle ne le
 * justifie encore.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Voix', 'fnc-wordpress-theme' ),
		'title'      => __( 'Des profils qui donnent du sens aux interventions.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Profils exemples non officiels, rattachés à sessions et ressources.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-portrait.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Intervenants', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Profils', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Profils', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article class="card">
							<p class="card-kicker"><?php esc_html_e( 'Intervenant', 'fnc-wordpress-theme' ); ?></p>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<?php if ( has_excerpt() ) : ?>
								<p><?php the_excerpt(); ?></p>
							<?php endif; ?>
						</article>
						<?php
					endwhile;
					?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucun intervenant publié', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les profils apparaîtront ici dès leur publication.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
