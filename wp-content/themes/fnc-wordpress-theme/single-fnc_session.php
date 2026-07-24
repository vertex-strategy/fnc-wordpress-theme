<?php
/**
 * Fiche d'une session (single-fnc_session.php).
 *
 * Structure alignee sur le site reel (/programme/[slug]) : hero .page-head
 * (type + jour/horaire/salle), puis un ARTICLE en colonne unique sur fond lin :
 * ligne meta -> Description (.prose-legal) -> Objectifs -> Intervenants
 * (.spk-grid : moderateur + intervenants) -> Tags -> Ressources.
 *
 * Une session est une vue de son edition (invariant du vrai site). Chaque bloc
 * est masque si la donnee n'existe pas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Rendu d'une carte .spk (grille d'intervenants), portrait soumis a la RÈGLE 7.
 */
if ( ! function_exists( 'fnc_render_spk_card' ) ) {
	function fnc_render_spk_card( $speaker_id, $kicker = '' ) {
		$org = get_post_meta( $speaker_id, '_fnc_speaker_org', true );
		$country = get_post_meta( $speaker_id, '_fnc_speaker_country', true );
		?>
		<a class="spk" href="<?php echo esc_url( get_permalink( $speaker_id ) ); ?>">
			<?php if ( $kicker ) : ?><span class="cat"><?php echo esc_html( $kicker ); ?></span><?php endif; ?>
			<div class="ph">
				<?php
				$portrait = function_exists( 'fnc_speaker_portrait' )
					? fnc_speaker_portrait( $speaker_id, 'medium', array( 'alt' => fnc_speaker_display_name( $speaker_id ) ) )
					: '';
				if ( $portrait ) {
					echo $portrait; // phpcs:ignore WordPress.Security.EscapeOutput -- markup <img> genere par WP/plugin.
				} else {
					printf( '<img src="%s" alt="" aria-hidden="true" />', esc_url( get_template_directory_uri() . '/assets/images/le-portrait.png' ) );
				}
				?>
			</div>
			<div class="n"><?php echo esc_html( fnc_speaker_display_name( $speaker_id ) ); ?></div>
			<?php if ( $org ) : ?><div class="r"><?php echo esc_html( $org ); ?></div><?php endif; ?>
			<?php if ( $country ) : ?><span class="c"><?php echo esc_html( $country ); ?></span><?php endif; ?>
		</a>
		<?php
	}
}

while ( have_posts() ) :
	the_post();

	$fnc_s_id        = get_the_ID();
	$fnc_s_type      = get_post_meta( $fnc_s_id, '_fnc_session_type', true );
	$fnc_s_jour      = get_post_meta( $fnc_s_id, '_fnc_session_jour', true );
	$fnc_s_time      = get_post_meta( $fnc_s_id, '_fnc_session_time', true );
	$fnc_s_room      = get_post_meta( $fnc_s_id, '_fnc_session_room', true );
	$fnc_s_note      = get_post_meta( $fnc_s_id, '_fnc_session_note', true );
	$fnc_s_edition   = (int) get_post_meta( $fnc_s_id, '_fnc_session_edition', true );
	$fnc_s_moderator = (int) get_post_meta( $fnc_s_id, '_fnc_session_moderator', true );
	$fnc_s_speakers  = get_post_meta( $fnc_s_id, '_fnc_session_speakers', true );
	$fnc_s_speakers  = is_array( $fnc_s_speakers ) ? array_map( 'intval', $fnc_s_speakers ) : array();
	$fnc_s_objectifs = get_post_meta( $fnc_s_id, '_fnc_session_objectives', true );
	$fnc_s_objectifs = is_array( $fnc_s_objectifs ) ? array_filter( $fnc_s_objectifs ) : array();
	$fnc_s_tags      = get_the_terms( $fnc_s_id, 'fnc_tag' );
	$fnc_types       = fnc_content_model_session_types();

	fnc_render_pagehead(
		array(
			'eyebrow'    => isset( $fnc_types[ $fnc_s_type ] ) ? $fnc_types[ $fnc_s_type ] : __( 'Session', 'fnc-wordpress-theme' ),
			'title'      => get_the_title(),
			'lead'       => trim( implode( ' · ', array_filter( array( $fnc_s_jour, $fnc_s_time, $fnc_s_room ) ) ) ),
			'image'      => has_post_thumbnail() ? get_the_post_thumbnail_url( $fnc_s_id, 'full' ) : get_template_directory_uri() . '/assets/images/le-pupitre.png',
			'image_alt'  => '',
			'breadcrumb' => get_the_title(),
		)
	);
	?>

	<main id="main">
		<section class="section linen">
			<div class="container reading">

				<div class="session-meta" style="display:flex;flex-wrap:wrap;gap:8px 24px;font-size:.9rem;color:var(--texte-sec);">
					<?php if ( isset( $fnc_types[ $fnc_s_type ] ) ) : ?><span><?php echo esc_html( $fnc_types[ $fnc_s_type ] ); ?></span><?php endif; ?>
					<?php if ( $fnc_s_jour ) : ?><span><?php echo esc_html( $fnc_s_jour ); ?></span><?php endif; ?>
					<?php if ( $fnc_s_time ) : ?><span><?php echo esc_html( $fnc_s_time ); ?></span><?php endif; ?>
					<?php if ( $fnc_s_room ) : ?><span><?php echo esc_html( $fnc_s_room ); ?></span><?php endif; ?>
					<?php if ( $fnc_s_edition > 0 ) : ?>
						<span><?php esc_html_e( 'Édition', 'fnc-wordpress-theme' ); ?> · <a href="<?php echo esc_url( get_permalink( $fnc_s_edition ) ); ?>" style="font-weight:600;color:var(--navy-deep);text-decoration:underline;text-underline-offset:4px;"><?php echo esc_html( get_the_title( $fnc_s_edition ) ); ?></a></span>
					<?php endif; ?>
				</div>
				<?php if ( $fnc_s_note ) : ?>
					<p style="margin-top:10px;font-size:.9rem;font-style:italic;color:var(--texte-sec);"><?php echo esc_html( $fnc_s_note ); ?></p>
				<?php endif; ?>

				<div class="person-detail__block">
					<p class="page-eyebrow text-navy"><?php esc_html_e( 'Description', 'fnc-wordpress-theme' ); ?></p>
					<?php if ( trim( get_the_content() ) ) : ?>
						<div class="prose-legal" style="margin-top:16px;max-width:42rem;"><?php the_content(); ?></div>
					<?php else : ?>
						<p class="help" style="margin-top:16px;"><?php esc_html_e( 'Le descriptif sera publié dès sa validation.', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $fnc_s_objectifs ) ) : ?>
					<div class="person-detail__block">
						<p class="page-eyebrow text-navy"><?php esc_html_e( 'Objectifs', 'fnc-wordpress-theme' ); ?></p>
						<ol style="margin-top:16px;max-width:42rem;list-style:none;padding:0;display:grid;gap:12px;">
							<?php foreach ( $fnc_s_objectifs as $fnc_oi => $fnc_obj ) : ?>
								<li style="display:flex;gap:16px;line-height:1.7;">
									<span aria-hidden="true" style="font-family:var(--serif);font-weight:500;color:var(--rouge);"><?php echo esc_html( ( $fnc_oi + 1 ) . '.' ); ?></span>
									<span><?php echo esc_html( $fnc_obj ); ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>

				<?php if ( $fnc_s_moderator > 0 || ! empty( $fnc_s_speakers ) ) : ?>
					<div class="person-detail__block">
						<p class="page-eyebrow text-navy"><?php esc_html_e( 'Intervenants', 'fnc-wordpress-theme' ); ?></p>
						<?php if ( $fnc_s_moderator > 0 ) : ?>
							<p style="margin-top:12px;font-size:.9rem;color:var(--texte-sec);">
								<?php esc_html_e( 'Modération', 'fnc-wordpress-theme' ); ?> ·
								<a href="<?php echo esc_url( get_permalink( $fnc_s_moderator ) ); ?>" style="font-weight:600;color:var(--navy-deep);text-decoration:underline;text-underline-offset:4px;"><?php echo esc_html( fnc_speaker_display_name( $fnc_s_moderator ) ); ?></a>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $fnc_s_speakers ) ) : ?>
							<div class="spk-grid" style="margin-top:20px;">
								<?php foreach ( $fnc_s_speakers as $fnc_sp_id ) : ?>
									<?php fnc_render_spk_card( $fnc_sp_id ); ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $fnc_s_tags && ! is_wp_error( $fnc_s_tags ) ) : ?>
					<div class="person-detail__block">
						<p class="page-eyebrow text-navy"><?php esc_html_e( 'Tags', 'fnc-wordpress-theme' ); ?></p>
						<ul style="margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;list-style:none;padding:0;">
							<?php foreach ( $fnc_s_tags as $fnc_tag ) : ?>
								<li><span class="badge"><?php echo esc_html( $fnc_tag->name ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php
				// Ressources rattachees a la meme edition (le modele ne lie pas les
				// ressources a une session : on propose donc celles de l'edition).
				$fnc_s_resources = $fnc_s_edition > 0
					? get_posts(
						array(
							'post_type'      => 'fnc_publication',
							'posts_per_page' => 3,
							'meta_key'       => '_fnc_publication_edition',
							'meta_value'     => $fnc_s_edition,
						)
					)
					: array();
				if ( ! empty( $fnc_s_resources ) ) :
					$fnc_pub_types = fnc_content_model_publication_types();
					?>
					<div class="person-detail__block">
						<p class="page-eyebrow text-navy"><?php esc_html_e( 'Ressources', 'fnc-wordpress-theme' ); ?></p>
						<ul style="margin-top:16px;list-style:none;padding:0;border-top:1px solid var(--border);">
							<?php foreach ( $fnc_s_resources as $fnc_sr ) : ?>
								<?php $fnc_sr_type = get_post_meta( $fnc_sr->ID, '_fnc_publication_type', true ); ?>
								<li style="border-bottom:1px solid var(--border);padding:16px 0;">
									<a href="<?php echo esc_url( get_permalink( $fnc_sr ) ); ?>">
										<span class="page-eyebrow text-rouge"><?php echo esc_html( isset( $fnc_pub_types[ $fnc_sr_type ] ) ? $fnc_pub_types[ $fnc_sr_type ] : __( 'Publication', 'fnc-wordpress-theme' ) ); ?></span>
										<span style="display:block;margin-top:4px;font-family:var(--serif);font-size:1.1rem;color:var(--navy-deep);"><?php echo esc_html( get_the_title( $fnc_sr ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div style="margin-top:48px;">
					<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_session' ) ); ?>"><?php esc_html_e( 'Voir tout le programme', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
				</div>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
