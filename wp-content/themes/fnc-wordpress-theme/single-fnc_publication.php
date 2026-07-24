<?php
/**
 * Fiche d'une ressource / publication (single-fnc_publication.php).
 *
 * Comble un manque : les liens de l'archive des ressources, de l'espace presse
 * et des fiches d'edition retombaient sur index.php, qui n'affiche qu'un
 * extrait — impossible d'atteindre le document ou la video.
 *
 * Distingue bien le TYPE (rapport, video, communique…) de la CATEGORIE
 * thematique, comme le vrai site : ce sont deux champs differents. L'action
 * proposee depend du type : « Regarder » pour une video ou une interview,
 * « Telecharger » lorsqu'un fichier est renseigne, sinon un etat d'attente.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$fnc_p_id     = get_the_ID();
	$fnc_p_type   = get_post_meta( $fnc_p_id, '_fnc_publication_type', true );
	$fnc_p_media  = get_post_meta( $fnc_p_id, '_fnc_publication_media_url', true );
	$fnc_p_file   = get_post_meta( $fnc_p_id, '_fnc_publication_file', true );
	$fnc_p_ed     = (int) get_post_meta( $fnc_p_id, '_fnc_publication_edition', true );
	$fnc_p_types  = fnc_content_model_publication_types();
	$fnc_p_cats   = get_the_terms( $fnc_p_id, 'fnc_categorie' );
	$fnc_p_tags   = get_the_terms( $fnc_p_id, 'fnc_tag' );
	$fnc_is_media = in_array( $fnc_p_type, array( 'video', 'interview' ), true );

	fnc_render_hero(
		array(
			'eyebrow'    => isset( $fnc_p_types[ $fnc_p_type ] ) ? $fnc_p_types[ $fnc_p_type ] : __( 'Ressource', 'fnc-wordpress-theme' ),
			'title'      => get_the_title(),
			'lead'       => has_excerpt() ? get_the_excerpt() : '',
			'image'      => has_post_thumbnail() ? get_the_post_thumbnail_url( $fnc_p_id, 'full' ) : get_template_directory_uri() . '/assets/images/la-main.png',
			'image_alt'  => '',
			'breadcrumb' => get_the_title(),
		)
	);
	?>

	<main id="main">
		<section class="section">
			<div class="container">
				<div class="split">
					<div>
						<?php if ( get_the_content() ) : ?>
							<div class="reading"><?php the_content(); ?></div>
						<?php endif; ?>

						<div style="margin-top:28px;">
							<?php if ( $fnc_is_media && $fnc_p_media ) : ?>
								<a class="btn btn-red" href="<?php echo esc_url( $fnc_p_media ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Regarder', 'fnc-wordpress-theme' ); ?>
									<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
								</a>
							<?php elseif ( $fnc_p_file ) : ?>
								<a class="btn btn-red" href="<?php echo esc_url( $fnc_p_file ); ?>">
									<?php esc_html_e( 'Télécharger le document', 'fnc-wordpress-theme' ); ?>
									<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
								</a>
							<?php else : ?>
								<p class="help"><?php esc_html_e( 'Le document sera mis à disposition dès sa validation.', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
							<?php endif; ?>
						</div>
					</div>

					<div>
						<article class="card fnc-card">
							<p class="card-kicker"><?php esc_html_e( 'Références', 'fnc-wordpress-theme' ); ?></p>
							<dl class="pract-contacts" style="margin-top:14px;">
								<?php if ( isset( $fnc_p_types[ $fnc_p_type ] ) ) : ?>
									<dt><?php esc_html_e( 'Type', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_p_types[ $fnc_p_type ] ); ?></dd>
								<?php endif; ?>
								<dt><?php esc_html_e( 'Publié le', 'fnc-wordpress-theme' ); ?></dt>
								<dd><?php echo esc_html( get_the_date() ); ?></dd>
								<?php if ( $fnc_p_ed > 0 ) : ?>
									<dt><?php esc_html_e( 'Édition', 'fnc-wordpress-theme' ); ?></dt>
									<dd><a href="<?php echo esc_url( get_permalink( $fnc_p_ed ) ); ?>"><?php echo esc_html( get_the_title( $fnc_p_ed ) ); ?></a></dd>
								<?php endif; ?>
								<?php if ( $fnc_p_cats && ! is_wp_error( $fnc_p_cats ) ) : ?>
									<dt><?php esc_html_e( 'Catégorie', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( implode( ', ', wp_list_pluck( $fnc_p_cats, 'name' ) ) ); ?></dd>
								<?php endif; ?>
							</dl>

							<?php if ( $fnc_p_tags && ! is_wp_error( $fnc_p_tags ) ) : ?>
								<div class="toolbar" style="margin-top:16px;margin-bottom:0;">
									<?php foreach ( $fnc_p_tags as $fnc_tag ) : ?>
										<span class="badge"><?php echo esc_html( $fnc_tag->name ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</article>
					</div>
				</div>
			</div>
		</section>

		<?php
		// Autres ressources de la meme edition.
		$fnc_p_related = $fnc_p_ed > 0
			? get_posts(
				array(
					'post_type'      => 'fnc_publication',
					'posts_per_page' => 3,
					'post__not_in'   => array( $fnc_p_id ),
					'meta_key'       => '_fnc_publication_edition',
					'meta_value'     => $fnc_p_ed,
				)
			)
			: array();
		if ( ! empty( $fnc_p_related ) ) :
			?>
			<section class="section linen">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'À lire aussi', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Autres ressources de cette édition.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="grid grid-3">
						<?php foreach ( $fnc_p_related as $fnc_pr ) : ?>
							<?php $fnc_pr_type = get_post_meta( $fnc_pr->ID, '_fnc_publication_type', true ); ?>
							<article class="card fnc-card">
								<p class="card-kicker"><?php echo esc_html( isset( $fnc_p_types[ $fnc_pr_type ] ) ? $fnc_p_types[ $fnc_pr_type ] : __( 'Publication', 'fnc-wordpress-theme' ) ); ?></p>
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_pr ) ); ?>"><?php echo esc_html( get_the_title( $fnc_pr ) ); ?></a></h3>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="section">
			<div class="container">
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_publication' ) ); ?>"><?php esc_html_e( 'Toutes les ressources', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
