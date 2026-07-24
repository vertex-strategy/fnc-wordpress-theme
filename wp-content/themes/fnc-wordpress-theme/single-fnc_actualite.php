<?php
/**
 * Fiche d'une actualite (single-fnc_actualite.php).
 *
 * Complete la couverture des gabarits single : le CPT fnc_actualite avait une
 * URL publique mais aucun gabarit dedie, retombant sur index.php (extrait seul).
 *
 * Le CPT ne porte que les champs natifs (titre, contenu, image, extrait) et les
 * taxonomies partagees (categories, etiquettes). La fiche affiche le contenu
 * editorial en colonne de lecture, avec la date et les classifications. Chaque
 * element est masque si la donnee n'existe pas.
 *
 * Rappel (voir modele de contenu) : la route publique des actualites du vrai
 * site est desactivee par defaut (NEWS_ENABLED) ; ce gabarit reste disponible
 * si des actualites sont publiees dans cette instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$fnc_ac_id   = get_the_ID();
	$fnc_ac_cats = get_the_terms( $fnc_ac_id, 'fnc_categorie' );
	$fnc_ac_tags = get_the_terms( $fnc_ac_id, 'fnc_tag' );

	fnc_render_hero(
		array(
			'eyebrow'    => get_the_date(),
			'title'      => get_the_title(),
			'lead'       => has_excerpt() ? get_the_excerpt() : '',
			'image'      => has_post_thumbnail() ? get_the_post_thumbnail_url( $fnc_ac_id, 'full' ) : get_template_directory_uri() . '/assets/images/le-territoire-brazzaville.png',
			'image_alt'  => '',
			'breadcrumb' => get_the_title(),
		)
	);
	?>

	<main id="main">
		<section class="section">
			<div class="container reading">
				<?php the_content(); ?>

				<?php if ( ( $fnc_ac_cats && ! is_wp_error( $fnc_ac_cats ) ) || ( $fnc_ac_tags && ! is_wp_error( $fnc_ac_tags ) ) ) : ?>
					<div class="toolbar" style="margin-top:32px;margin-bottom:0;border-top:1px solid var(--border);padding-top:24px;">
						<?php
						foreach ( array( $fnc_ac_cats, $fnc_ac_tags ) as $fnc_terms ) {
							if ( $fnc_terms && ! is_wp_error( $fnc_terms ) ) {
								foreach ( $fnc_terms as $fnc_term ) {
									printf( '<span class="badge">%s</span>', esc_html( $fnc_term->name ) );
								}
							}
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="section linen">
			<div class="container">
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_actualite' ) ); ?>"><?php esc_html_e( 'Toutes les actualités', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
