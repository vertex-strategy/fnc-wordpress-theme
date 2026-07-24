<?php
/**
 * Fiche d'un intervenant (single-fnc_intervenant.php).
 *
 * Structure alignee sur le site reel (/intervenants/[slug]) : hero .page-head,
 * puis une grille 2 colonnes [0.42fr 1fr] sur fond lin :
 *   - aside sticky (gauche) : portrait (RÈGLE 7) ou monogramme + profil + liens ;
 *   - article (droite)      : biographie (.prose-legal) + sessions de la personne.
 *
 * Les sessions ou la personne intervient sont retrouvees depuis les relations
 * (intervenant OU moderateur), sans champ supplementaire a saisir.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$fnc_i_id      = get_the_ID();
	$fnc_i_org     = get_post_meta( $fnc_i_id, '_fnc_speaker_org', true );
	$fnc_i_country = get_post_meta( $fnc_i_id, '_fnc_speaker_country', true );
	$fnc_i_links   = get_post_meta( $fnc_i_id, '_fnc_speaker_links', true );
	$fnc_i_links   = is_array( $fnc_i_links ) ? $fnc_i_links : array();
	$fnc_i_profils = get_the_terms( $fnc_i_id, 'fnc_profil' );
	$fnc_i_name    = fnc_speaker_display_name( $fnc_i_id );

	/*
	 * Sessions ou la personne intervient (comme intervenant OU moderateur).
	 * Filtrage en PHP plutot qu'en meta_query LIKE : la liste d'intervenants est
	 * un tableau serialise dont les ID peuvent etre stockes en entiers ou en
	 * chaines ; un LIKE raterait certains cas et pourrait matcher un indice.
	 */
	$fnc_i_sessions = array_values(
		array_filter(
			get_posts( array( 'post_type' => 'fnc_session', 'posts_per_page' => -1 ) ),
			static function ( $session ) use ( $fnc_i_id ) {
				if ( (int) get_post_meta( $session->ID, '_fnc_session_moderator', true ) === $fnc_i_id ) {
					return true;
				}
				$speakers = get_post_meta( $session->ID, '_fnc_session_speakers', true );
				if ( ! is_array( $speakers ) ) {
					return false;
				}
				return in_array( $fnc_i_id, array_map( 'intval', $speakers ), true );
			}
		)
	);

	fnc_render_pagehead(
		array(
			'eyebrow'    => '', // spec §4 : la fiche intervenant n'a pas d'eyebrow dans le hero.
			'title'      => $fnc_i_name,
			'lead'       => trim( implode( ' · ', array_filter( array( $fnc_i_org, $fnc_i_country ) ) ) ),
			'image'      => get_template_directory_uri() . '/assets/images/le-portrait.png',
			'image_alt'  => '',
			'breadcrumb' => $fnc_i_name,
		)
	);
	?>

	<main id="main">
		<section class="section linen">
			<div class="container">
				<div class="person-detail">

					<aside class="person-detail__aside">
						<div class="person-detail__portrait">
							<?php
							// RÈGLE 7 : fnc_speaker_portrait ne renvoie la photo que si le droit est
							// « obtenu » et non expire ; sinon monogramme (jamais la vraie photo).
							$fnc_portrait = function_exists( 'fnc_speaker_portrait' )
								? fnc_speaker_portrait( $fnc_i_id, 'large', array( 'alt' => $fnc_i_name ) )
								: '';
							if ( $fnc_portrait ) {
								echo $fnc_portrait; // phpcs:ignore WordPress.Security.EscapeOutput -- markup <img> genere par WP/plugin.
							} else {
								$fnc_initials = '';
								foreach ( preg_split( '/\s+/', trim( wp_strip_all_tags( $fnc_i_name ) ) ) as $fnc_word ) {
									if ( '' !== $fnc_word ) {
										$fnc_initials .= function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $fnc_word, 0, 1 ) ) : strtoupper( substr( $fnc_word, 0, 1 ) );
									}
									if ( strlen( $fnc_initials ) >= 2 ) {
										break;
									}
								}
								?>
								<span>
									<span class="person-detail__mono" aria-hidden="true"><?php echo esc_html( $fnc_initials ); ?></span>
									<span class="person-detail__pending"><?php esc_html_e( 'Photo à venir', 'fnc-wordpress-theme' ); ?></span>
								</span>
								<?php
							}
							?>
						</div>

						<div class="person-detail__meta">
							<?php if ( $fnc_i_profils && ! is_wp_error( $fnc_i_profils ) ) : ?>
								<p class="page-eyebrow text-rouge"><?php echo esc_html( $fnc_i_profils[0]->name ); ?></p>
							<?php endif; ?>
							<?php if ( $fnc_i_org ) : ?>
								<p class="person-detail__org"><?php echo esc_html( $fnc_i_org ); ?></p>
							<?php endif; ?>
							<?php if ( $fnc_i_country ) : ?>
								<p class="person-detail__country">
									<?php foreach ( fnc_split_countries( $fnc_i_country ) as $fnc_c ) : ?>
										<span class="flag-chip" style="margin-right:8px;">
											<?php echo fnc_flag_markup( $fnc_c ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup construit et echappe par le helper. ?>
											<span><?php echo esc_html( $fnc_c ); ?></span>
										</span>
									<?php endforeach; ?>
								</p>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $fnc_i_links ) ) : ?>
							<div class="person-detail__links">
								<p class="page-eyebrow text-navy"><?php esc_html_e( 'Liens', 'fnc-wordpress-theme' ); ?></p>
								<ul>
									<?php foreach ( $fnc_i_links as $fnc_link ) : ?>
										<?php
										$fnc_link_url   = isset( $fnc_link['url'] ) ? $fnc_link['url'] : '';
										$fnc_link_label = isset( $fnc_link['label'] ) && $fnc_link['label'] ? $fnc_link['label'] : $fnc_link_url;
										if ( ! $fnc_link_url ) {
											continue;
										}
										?>
										<li><a href="<?php echo esc_url( $fnc_link_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $fnc_link_label ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</aside>

					<article>
						<div class="person-detail__block">
							<p class="page-eyebrow text-navy"><?php esc_html_e( 'Biographie', 'fnc-wordpress-theme' ); ?></p>
							<?php if ( trim( get_the_content() ) ) : ?>
								<div class="prose-legal" style="margin-top:16px;"><?php the_content(); ?></div>
							<?php else : ?>
								<p class="help" style="margin-top:16px;"><?php esc_html_e( 'La biographie sera publiée dès sa validation.', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $fnc_i_sessions ) ) : ?>
							<?php $fnc_types = fnc_content_model_session_types(); ?>
							<div class="person-detail__block">
								<p class="page-eyebrow text-navy"><?php esc_html_e( 'Sessions', 'fnc-wordpress-theme' ); ?></p>
								<div class="agenda" style="margin-top:16px;">
									<?php foreach ( $fnc_i_sessions as $fnc_is ) : ?>
										<?php
										$fnc_is_type = get_post_meta( $fnc_is->ID, '_fnc_session_type', true );
										$fnc_is_hide = in_array( $fnc_is_type, array( 'pause', 'logistique' ), true );
										$fnc_is_mod  = (int) get_post_meta( $fnc_is->ID, '_fnc_session_moderator', true ) === $fnc_i_id;
										?>
										<a class="agenda-row" href="<?php echo esc_url( get_permalink( $fnc_is ) ); ?>">
											<span class="time"><?php echo esc_html( get_post_meta( $fnc_is->ID, '_fnc_session_time', true ) ?: '—' ); ?></span>
											<span>
												<strong><?php echo esc_html( get_the_title( $fnc_is ) ); ?></strong>
												<?php if ( $fnc_is_type && ! $fnc_is_hide && isset( $fnc_types[ $fnc_is_type ] ) ) : ?>
													<?php fnc_render_badge( $fnc_types[ $fnc_is_type ] ); ?>
												<?php endif; ?>
												<?php if ( $fnc_is_mod ) : ?>
													<span class="person-meta"><?php esc_html_e( 'En modération', 'fnc-wordpress-theme' ); ?></span>
												<?php endif; ?>
											</span>
											<span class="room"><?php echo esc_html( get_post_meta( $fnc_is->ID, '_fnc_session_room', true ) ?: __( 'Salle à confirmer', 'fnc-wordpress-theme' ) ); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</article>

				</div>

				<div style="margin-top:48px;">
					<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_intervenant' ) ); ?>"><?php esc_html_e( 'Tous les intervenants', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
				</div>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
