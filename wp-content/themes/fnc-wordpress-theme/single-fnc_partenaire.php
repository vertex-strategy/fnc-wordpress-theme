<?php
/**
 * Fiche d'un partenaire (single-fnc_partenaire.php).
 *
 * Complete la couverture des gabarits single : le CPT fnc_partenaire avait une
 * URL publique mais aucun gabarit dedie, retombant sur index.php (extrait seul).
 * Les partenaires ne sont pas encore lies depuis l'archive (logos non
 * cliquables), mais leur URL reste publique — cette fiche la rend utile.
 *
 * Affiche l'identite du partenaire (logo, type d'engagement), sa description,
 * son site web et ses editions associees avec le niveau par edition. Chaque
 * element est masque si la donnee n'existe pas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$fnc_pa_id      = get_the_ID();
	$fnc_pa_site    = get_post_meta( $fnc_pa_id, '_fnc_partenaire_site', true );
	$fnc_pa_types   = get_the_terms( $fnc_pa_id, 'fnc_niveau_partenariat' );
	$fnc_pa_type    = ( $fnc_pa_types && ! is_wp_error( $fnc_pa_types ) ) ? $fnc_pa_types[0]->name : '';

	// Participations {edition, niveau}, avec repli sur la liste plate d'editions.
	$fnc_pa_participations = get_post_meta( $fnc_pa_id, '_fnc_partenaire_participations', true );
	$fnc_pa_niveau_by_ed   = array();
	if ( is_array( $fnc_pa_participations ) ) {
		foreach ( $fnc_pa_participations as $fnc_part ) {
			if ( ! empty( $fnc_part['edition'] ) ) {
				$fnc_pa_niveau_by_ed[ (int) $fnc_part['edition'] ] = $fnc_part['niveau'] ?? '';
			}
		}
	}
	$fnc_pa_editions = get_post_meta( $fnc_pa_id, '_fnc_partenaire_editions', true );
	$fnc_pa_editions = is_array( $fnc_pa_editions ) ? $fnc_pa_editions : array();
	$fnc_pa_niveaux  = fnc_content_model_partner_niveaux();

	fnc_render_pagehead(
		array(
			'eyebrow'    => $fnc_pa_type ? $fnc_pa_type : __( 'Partenaire', 'fnc-wordpress-theme' ),
			'title'      => get_the_title(),
			'lead'       => has_excerpt() ? get_the_excerpt() : '',
			'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
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
						<?php elseif ( has_excerpt() ) : ?>
							<p class="reading"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<?php if ( $fnc_pa_site ) : ?>
							<p style="margin-top:24px;">
								<a class="btn btn-red" href="<?php echo esc_url( $fnc_pa_site ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Visiter le site', 'fnc-wordpress-theme' ); ?>
									<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
								</a>
							</p>
						<?php endif; ?>
					</div>

					<div>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="card fnc-card" style="display:grid;place-items:center;min-height:180px;">
								<?php the_post_thumbnail( 'medium', array( 'alt' => esc_attr( get_the_title() ), 'style' => 'max-width:100%;height:auto;' ) ); ?>
							</div>
						<?php endif; ?>

						<article class="card fnc-card" style="margin-top:<?php echo has_post_thumbnail() ? '18px' : '0'; ?>;">
							<p class="card-kicker"><?php esc_html_e( 'Engagement', 'fnc-wordpress-theme' ); ?></p>
							<dl class="pract-contacts" style="margin-top:14px;">
								<?php if ( $fnc_pa_type ) : ?>
									<dt><?php esc_html_e( 'Type', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_pa_type ); ?></dd>
								<?php endif; ?>
							</dl>
						</article>
					</div>
				</div>
			</div>
		</section>

		<?php if ( ! empty( $fnc_pa_editions ) ) : ?>
			<section class="section linen">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Engagements', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Éditions associées.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="grid grid-3">
						<?php foreach ( $fnc_pa_editions as $fnc_ed_id ) : ?>
							<?php $fnc_niveau = $fnc_pa_niveau_by_ed[ $fnc_ed_id ] ?? ''; ?>
							<article class="card fnc-card">
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_ed_id ) ); ?>"><?php echo esc_html( get_the_title( $fnc_ed_id ) ); ?></a></h3>
								<?php if ( $fnc_niveau && isset( $fnc_pa_niveaux[ $fnc_niveau ] ) ) : ?>
									<p style="margin-top:10px;"><?php fnc_render_badge( $fnc_pa_niveaux[ $fnc_niveau ] ); ?></p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="section">
			<div class="container">
				<a class="link-more" href="<?php echo esc_url( fnc_page_url( 'partenaires' ) ); ?>"><?php esc_html_e( 'Tous les partenaires', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
