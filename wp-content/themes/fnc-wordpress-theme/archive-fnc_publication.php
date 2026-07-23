<?php
/**
 * Archive du custom post type "fnc_publication".
 *
 * Porte docs/mockups/homepage-v2/publications.html (contenu genere par
 * site.js: publicationsPage(), copy.publications). Branche sur les
 * vraies donnees du plugin fnc-content-model (etape 4 de l'ADR-007) :
 * liste reelle des publications, chips de filtre generes a partir des
 * vraies categories (fnc_categorie) plutot que des libelles d'exemple
 * ("Rapports", "Communiques"...) de la maquette source.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Ressources', 'fnc-wordpress-theme' ),
		'title'      => __( 'La réflexion continue après les sessions.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Rapports, communiqués, vidéos et analyses sont structurés sans contenu fictif.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Publications', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<?php
			$fnc_categories = get_terms(
				array(
					'taxonomy'   => 'fnc_categorie',
					'object_ids' => get_posts( array( 'post_type' => 'fnc_publication', 'posts_per_page' => -1, 'fields' => 'ids' ) ),
				)
			);
			if ( ! is_wp_error( $fnc_categories ) && ! empty( $fnc_categories ) ) :
				?>
				<div class="toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Filtres', 'fnc-wordpress-theme' ); ?>">
					<button class="chip" type="button" aria-pressed="true"><?php esc_html_e( 'Toutes', 'fnc-wordpress-theme' ); ?></button>
					<?php foreach ( $fnc_categories as $fnc_category ) : ?>
						<button class="chip" type="button" aria-pressed="false"><?php echo esc_html( $fnc_category->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article class="card">
							<p class="card-kicker"><?php esc_html_e( 'Publication', 'fnc-wordpress-theme' ); ?></p>
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
					<h3><?php esc_html_e( 'Aucune publication validée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'L’état vide reste sobre, sans faux contenu.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
