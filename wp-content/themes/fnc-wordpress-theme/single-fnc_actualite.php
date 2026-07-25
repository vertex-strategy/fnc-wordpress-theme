<?php
/**
 * Forum Numérique Congo — fiche détaillée d’une actualité.
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

while ( have_posts() ) :
	the_post();

	$fnc_ac_id   = get_the_ID();
	$fnc_ac_cats = get_the_terms( $fnc_ac_id, 'fnc_categorie' );
	$fnc_ac_tags = get_the_terms( $fnc_ac_id, 'fnc_tag' );

	?>
	<header class="section" style="padding-top:calc(clamp(64px,9vh,118px) + 40px);padding-bottom:0;">
		<div class="container reading">
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <a href="<?php echo esc_url( fnc_archive_url( 'fnc_actualite' ) ); ?>"><?php esc_html_e( 'Actualités', 'fnc-wordpress-theme' ); ?></a></p>
			<p class="page-eyebrow text-navy"><?php echo esc_html( get_the_date() ); ?><?php if ( $fnc_ac_cats && ! is_wp_error( $fnc_ac_cats ) ) { echo ' · ' . esc_html( $fnc_ac_cats[0]->name ); } ?></p>
			<h1 class="page-h2"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
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
