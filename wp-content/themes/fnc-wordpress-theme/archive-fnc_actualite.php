<?php
/**
 * Archive du custom post type "fnc_actualite".
 *
 * Complete la couverture des archives : les 5 autres CPT publics ont chacun
 * leur gabarit archive-{cpt}.php ; seule l'archive des actualites retombait sur
 * index.php (liste brute). Ce gabarit l'habille comme les autres.
 *
 * Rappel (modele de contenu) : la route publique des actualites du vrai site
 * est desactivee par defaut (NEWS_ENABLED) — cette archive n'affiche donc du
 * contenu que si des actualites sont reellement publiees dans l'instance, avec
 * un etat d'attente sobre sinon. On s'appuie sur la boucle principale, deja
 * filtree par langue par Polylang.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_opening_hero(
	array(
		'eyebrow'    => __( 'Actualités', 'fnc-wordpress-theme' ),
		'title'      => __( 'La vie du Forum, entre deux éditions.', 'fnc-wordpress-theme' ),
		'intro'      => __( 'Les temps forts, annonces et rendez-vous de l\'institution, au fil de l\'eau.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-fleuve.png',
		'image_alt'  => __( 'Actualités du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Actualités', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Actualités', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Dernières publications.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						$fnc_ac_cats = get_the_terms( get_the_ID(), 'fnc_categorie' );
						?>
						<article class="card fnc-card">
							<p class="card-kicker">
								<?php echo esc_html( get_the_date() ); ?>
								<?php if ( $fnc_ac_cats && ! is_wp_error( $fnc_ac_cats ) ) : ?>
									· <?php echo esc_html( $fnc_ac_cats[0]->name ); ?>
								<?php endif; ?>
							</p>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<?php if ( has_excerpt() ) : ?>
								<p><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
							<p style="margin-top:12px;"><a class="link-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Lire', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( 'Précédent', 'fnc-wordpress-theme' ),
						'next_text' => __( 'Suivant', 'fnc-wordpress-theme' ),
					)
				);
				?>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune actualité publiée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les actualités apparaîtront ici dès leur publication.', 'fnc-wordpress-theme' ); ?></p>
					<div class="meta" style="justify-content:center;"><span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php
get_footer();
