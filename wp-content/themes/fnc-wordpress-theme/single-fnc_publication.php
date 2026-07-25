<?php
/**
 * Forum Numérique Congo — fiche détaillée d’une publication.
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

	$fnc_p_id     = get_the_ID();
	$fnc_p_type   = get_post_meta( $fnc_p_id, '_fnc_publication_type', true );
	$fnc_p_media  = get_post_meta( $fnc_p_id, '_fnc_publication_media_url', true );
	$fnc_p_file   = get_post_meta( $fnc_p_id, '_fnc_publication_file', true );
	$fnc_p_ed     = (int) get_post_meta( $fnc_p_id, '_fnc_publication_edition', true );
	$fnc_p_types  = fnc_content_model_publication_types();
	$fnc_p_cats   = get_the_terms( $fnc_p_id, 'fnc_categorie' );
	$fnc_p_tags   = get_the_terms( $fnc_p_id, 'fnc_tag' );
	$fnc_is_media = in_array( $fnc_p_type, array( 'video', 'interview' ), true );

	$fnc_chip_style = 'display:inline-flex;align-items:center;min-height:44px;border:1px solid var(--border);background:var(--linen);border-radius:4px;padding:0 20px;font-size:.9rem;font-weight:600;color:var(--navy-deep);';

	fnc_render_pagehead(
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
		<section class="section linen">
			<div class="container">
				<article class="card fnc-card" style="max-width:48rem;margin:0 auto;padding:clamp(28px,4vw,48px);">

					<p class="page-eyebrow text-rouge"><?php echo esc_html( isset( $fnc_p_types[ $fnc_p_type ] ) ? $fnc_p_types[ $fnc_p_type ] : __( 'Ressource', 'fnc-wordpress-theme' ) ); ?></p>
					<p style="margin-top:8px;font-size:.9rem;color:var(--texte-sec);">
						<?php echo esc_html( get_the_date() ); ?>
						<?php if ( $fnc_p_cats && ! is_wp_error( $fnc_p_cats ) ) : ?> · <?php echo esc_html( implode( ', ', wp_list_pluck( $fnc_p_cats, 'name' ) ) ); ?><?php endif; ?>
					</p>

					<?php if ( trim( get_the_content() ) ) : ?>
						<div class="prose-legal" style="margin-top:22px;"><?php the_content(); ?></div>
					<?php elseif ( has_excerpt() ) : ?>
						<p style="margin-top:22px;font-size:1.1rem;line-height:1.8;color:var(--texte-corps);"><?php echo esc_html( get_the_excerpt() ); ?></p>
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

					<?php if ( $fnc_p_tags && ! is_wp_error( $fnc_p_tags ) ) : ?>
						<ul style="margin-top:22px;display:flex;flex-wrap:wrap;gap:8px;list-style:none;padding:0;">
							<?php foreach ( $fnc_p_tags as $fnc_tag ) : ?>
								<li><span class="badge"><?php echo esc_html( $fnc_tag->name ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $fnc_p_ed > 0 ) : ?>
						<div class="person-detail__block">
							<p class="page-eyebrow text-navy"><?php esc_html_e( 'Édition', 'fnc-wordpress-theme' ); ?></p>
							<a href="<?php echo esc_url( get_permalink( $fnc_p_ed ) ); ?>" style="<?php echo esc_attr( $fnc_chip_style ); ?>margin-top:16px;"><?php echo esc_html( get_the_title( $fnc_p_ed ) ); ?></a>
						</div>
					<?php endif; ?>
				</article>

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
					<div style="max-width:48rem;margin:44px auto 0;">
						<p class="page-eyebrow text-navy"><?php esc_html_e( 'À lire aussi', 'fnc-wordpress-theme' ); ?></p>
						<ul style="margin-top:16px;list-style:none;padding:0;border-top:1px solid var(--border);">
							<?php foreach ( $fnc_p_related as $fnc_pr ) : ?>
								<?php $fnc_pr_type = get_post_meta( $fnc_pr->ID, '_fnc_publication_type', true ); ?>
								<li style="border-bottom:1px solid var(--border);padding:16px 0;">
									<a href="<?php echo esc_url( get_permalink( $fnc_pr ) ); ?>">
										<span class="page-eyebrow text-rouge"><?php echo esc_html( isset( $fnc_p_types[ $fnc_pr_type ] ) ? $fnc_p_types[ $fnc_pr_type ] : __( 'Publication', 'fnc-wordpress-theme' ) ); ?></span>
										<span style="display:block;margin-top:4px;font-family:var(--serif);font-size:1.1rem;color:var(--navy-deep);"><?php echo esc_html( get_the_title( $fnc_pr ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div style="max-width:48rem;margin:40px auto 0;">
					<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_publication' ) ); ?>"><?php esc_html_e( 'Toutes les ressources', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
				</div>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
