<?php
/**
 * Archive du custom post type "fnc_publication".
 *
 * Porte docs/mockups/homepage-v2/publications.html a l'origine (contenu
 * genere par site.js: publicationsPage()). Recherche + filtre par
 * categorie alignes sur le site officiel reel
 * (localhost:3000/fr/ressources), suite a l'amendement de la Decision 1
 * de l'ADR-007 : la maquette seule ne proposait pas de recherche, alors
 * que le site officiel en implemente deja une (recherche + filtre par
 * type de document).
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

$fnc_archive_url  = get_post_type_archive_link( 'fnc_publication' );
$fnc_current_cat  = isset( $_GET['fnc_categorie'] ) ? sanitize_title( wp_unslash( $_GET['fnc_categorie'] ) ) : '';
$fnc_search_query = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$fnc_categories   = get_terms(
	array(
		'taxonomy'   => 'fnc_categorie',
		'object_ids' => get_posts( array( 'post_type' => 'fnc_publication', 'posts_per_page' => -1, 'fields' => 'ids' ) ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<form class="field" method="get" action="<?php echo esc_url( $fnc_archive_url ); ?>" role="search" style="max-width:420px;margin-bottom:20px;">
				<label for="fnc-publication-search"><?php esc_html_e( 'Recherche', 'fnc-wordpress-theme' ); ?></label>
				<input id="fnc-publication-search" type="search" name="s" value="<?php echo esc_attr( $fnc_search_query ); ?>" placeholder="<?php esc_attr_e( 'Rechercher une publication', 'fnc-wordpress-theme' ); ?>" />
				<?php if ( $fnc_current_cat ) : ?>
					<input type="hidden" name="fnc_categorie" value="<?php echo esc_attr( $fnc_current_cat ); ?>" />
				<?php endif; ?>
				<button class="btn btn-soft" type="submit" style="margin-top:10px;"><?php esc_html_e( 'Rechercher', 'fnc-wordpress-theme' ); ?></button>
			</form>

			<?php if ( ! is_wp_error( $fnc_categories ) && ! empty( $fnc_categories ) ) : ?>
				<div class="toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Filtres', 'fnc-wordpress-theme' ); ?>">
					<a class="chip" href="<?php echo esc_url( $fnc_search_query ? add_query_arg( 's', $fnc_search_query, $fnc_archive_url ) : $fnc_archive_url ); ?>" aria-pressed="<?php echo $fnc_current_cat ? 'false' : 'true'; ?>"><?php esc_html_e( 'Toutes', 'fnc-wordpress-theme' ); ?></a>
					<?php foreach ( $fnc_categories as $fnc_category ) : ?>
						<a class="chip" href="<?php echo esc_url( add_query_arg( array_filter( array( 'fnc_categorie' => $fnc_category->slug, 's' => $fnc_search_query ) ), $fnc_archive_url ) ); ?>" aria-pressed="<?php echo $fnc_current_cat === $fnc_category->slug ? 'true' : 'false'; ?>"><?php echo esc_html( $fnc_category->name ); ?></a>
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
