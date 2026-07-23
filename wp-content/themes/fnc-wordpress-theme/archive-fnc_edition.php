<?php
/**
 * Archive du custom post type "fnc_edition".
 *
 * Porte docs/mockups/homepage-v2/editions.html (contenu genere par
 * site.js: editionsPage(), copy.editions). Contrairement a
 * page-le-forum.php et page-contact.php, ce gabarit est branche sur les
 * VRAIES donnees du plugin fnc-content-model (etape 4 de l'ADR-007,
 * amorcee ici) : la liste affichee reflete les editions reellement
 * publiees dans WordPress, pas du contenu d'exemple statique.
 *
 * La section "Memoire du Forum" (timeline) de la maquette source n'est
 * pas reproduite : elle presentait des libelles d'exemple ("Edition 1",
 * "Edition 2"...) qui seraient un contenu invente si affiches ici a la
 * place de vraies editions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Mémoire', 'fnc-wordpress-theme' ),
		'title'      => __( 'L’histoire du Forum comme actif institutionnel.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Chaque édition relie bilan, ressources, galerie et partenaires si ces contenus sont validés.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-territoire-brazzaville.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Éditions', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Éditions publiées', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Éditions publiées', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article class="card">
							<?php if ( has_excerpt() || get_the_content() ) : ?>
								<p class="card-kicker"><?php esc_html_e( 'Édition', 'fnc-wordpress-theme' ); ?></p>
							<?php endif; ?>
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
					<h3><?php esc_html_e( 'Aucune édition publiée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les éditions apparaîtront ici dès leur publication.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
