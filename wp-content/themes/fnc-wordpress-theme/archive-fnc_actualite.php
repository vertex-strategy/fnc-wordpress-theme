<?php
/**
 * Forum Numérique Congo — liste des actualités.
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fnc_route_h = fnc_route_hero( 'actualites' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
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
					<p class="eyebrow" data-fnc-st="actualites.list.eyebrow"><?php echo esc_html( fnc_stitle( 'actualites', 'list', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="actualites.list.title"><?php echo esc_html( fnc_stitle( 'actualites', 'list', 'title' ) ); ?></h2>
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
							<h3><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
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
