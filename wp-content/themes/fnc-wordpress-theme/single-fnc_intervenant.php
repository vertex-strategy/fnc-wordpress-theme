<?php
/**
 * Forum Numérique Congo — fiche détaillée d’un intervenant.
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
			'eyebrow'    => '', // La fiche intervenant n'a pas de sur-titre dans le hero.
			'title'      => $fnc_i_name,
			'lead'       => get_post_meta( $fnc_i_id, '_fnc_speaker_role', true ), // Fonction, comme le site du Forum.
			'image'      => get_template_directory_uri() . '/assets/images/le-portrait.png',
			'image_alt'  => '',
			'breadcrumb' => '<a href="' . esc_url( fnc_archive_url( 'fnc_intervenant' ) ) . '">' . esc_html__( 'Intervenants', 'fnc-wordpress-theme' ) . '</a> · ' . esc_html( $fnc_i_name ),
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
							// la règle du droit à l’image : fnc_speaker_portrait ne renvoie la photo que si le droit est
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
								<p class="person-detail__country"><?php echo esc_html( $fnc_i_country ); ?></p>
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
							<div class="person-detail__block">
								<p class="page-eyebrow text-navy"><?php esc_html_e( 'Ses interventions', 'fnc-wordpress-theme' ); ?></p>
								<ul class="person-sessions" role="list" style="margin-top:16px;">
									<?php foreach ( $fnc_i_sessions as $fnc_is ) : ?>
										<?php
										$fnc_is_jour  = get_post_meta( $fnc_is->ID, '_fnc_session_jour', true );
										/* translators: %s: numéro du jour. */
										$fnc_is_jour  = $fnc_is_jour ? sprintf( __( 'Jour %s', 'fnc-wordpress-theme' ), $fnc_is_jour ) : '';
										$fnc_is_start = get_post_meta( $fnc_is->ID, '_fnc_session_start', true );
										$fnc_is_when  = trim( implode( ' · ', array_filter( array( $fnc_is_jour, $fnc_is_start ) ) ) );
										$fnc_is_mod   = (int) get_post_meta( $fnc_is->ID, '_fnc_session_moderator', true ) === $fnc_i_id;
										?>
										<li>
											<a class="s-title-link" href="<?php echo esc_url( get_permalink( $fnc_is ) ); ?>">
												<span class="time"><?php echo esc_html( $fnc_is_when ); ?></span>
												<span class="title"><?php echo esc_html( get_the_title( $fnc_is ) ); ?></span>
												<?php if ( $fnc_is_mod ) : ?><span class="as-moderator"><?php esc_html_e( 'En modération', 'fnc-wordpress-theme' ); ?></span><?php endif; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>

							<?php
							// Éditions auxquelles l'intervenant a participé (dérivées de ses sessions).
							$fnc_i_editions = array();
							foreach ( $fnc_i_sessions as $fnc_is ) {
								$fnc_eid = (int) get_post_meta( $fnc_is->ID, '_fnc_session_edition', true );
								if ( $fnc_eid && ! isset( $fnc_i_editions[ $fnc_eid ] ) ) {
									$fnc_i_editions[ $fnc_eid ] = get_post( $fnc_eid );
								}
							}
							if ( ! empty( $fnc_i_editions ) ) :
								?>
								<div class="person-detail__block">
									<p class="page-eyebrow text-navy"><?php esc_html_e( 'Éditions', 'fnc-wordpress-theme' ); ?></p>
									<ul class="ed-chips" style="margin-top:16px;">
										<?php foreach ( $fnc_i_editions as $fnc_ie ) : ?>
											<?php if ( ! $fnc_ie ) { continue; } ?>
											<li><a class="ed-chip" href="<?php echo esc_url( get_permalink( $fnc_ie ) ); ?>"><?php echo esc_html( get_the_title( $fnc_ie ) ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</article>

				</div>

				<div class="person-detail__back" style="margin-top:40px;border-top:1px solid var(--border);padding-top:32px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
					<a class="btn btn-ghost" href="<?php echo esc_url( fnc_archive_url( 'fnc_intervenant' ) ); ?>">← <?php esc_html_e( 'Tous les intervenants', 'fnc-wordpress-theme' ); ?></a>
					<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_session' ) ); ?>"><?php esc_html_e( 'Voir le programme', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
				</div>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
