<?php
/**
 * Archive du custom post type "fnc_session".
 *
 * Porte docs/mockups/homepage-v2/programme.html (contenu genere par
 * site.js: programmePage(), copy.programme). Branche sur les vraies
 * donnees du plugin fnc-content-model (etape 4 de l'ADR-007) : agenda
 * reel des sessions publiees (horaire, titre, salle), pas les 4 sessions
 * d'exemple de la maquette source (examples.sessions).
 *
 * Le toolbar de filtres par type ("Institutionnel", "Panel", "Atelier",
 * "Presse") de la maquette source n'est pas reproduit ici : ce sont des
 * categories d'exemple qui ne correspondent a aucune donnee reelle du
 * plugin a ce jour (fnc_categorie n'a pas ete peuple avec cette
 * taxonomie precise) — a ajouter si ce filtre est confirme necessaire.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Agenda', 'fnc-wordpress-theme' ),
		'title'      => __( 'Un programme lisible avant, pendant et après.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Chaque session devient un nœud de navigation vers édition, intervenants et ressources.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-pupitre.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Programme', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Sessions', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="agenda">
					<?php
					while ( have_posts() ) :
						the_post();
						$fnc_time = get_post_meta( get_the_ID(), '_fnc_session_time', true );
						$fnc_room = get_post_meta( get_the_ID(), '_fnc_session_room', true );
						?>
						<a class="agenda-row" href="<?php the_permalink(); ?>">
							<span class="time"><?php echo $fnc_time ? esc_html( $fnc_time ) : '—'; ?></span>
							<strong><?php the_title(); ?></strong>
							<span class="room"><?php echo $fnc_room ? esc_html( $fnc_room ) : esc_html__( 'Salle à confirmer', 'fnc-wordpress-theme' ); ?></span>
						</a>
						<?php
					endwhile;
					?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune session publiée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les données finales proviennent du CMS.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
