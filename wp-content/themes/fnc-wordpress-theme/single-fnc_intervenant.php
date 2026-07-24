<?php
/**
 * Fiche d'un intervenant (single-fnc_intervenant.php).
 *
 * Comble un manque : les liens de l'annuaire et de la page Edition en cours
 * retombaient sur index.php, qui n'affiche qu'un extrait.
 *
 * Reprend les champs du modele reconcilie : civilite, organisation, pays
 * (texte libre pouvant cumuler plusieurs pays), profil, liens externes. Les
 * sessions ou la personne intervient sont retrouvees depuis les relations
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
		<section class="section">
			<div class="container">
				<div class="split">
					<div>
						<?php if ( get_the_content() ) : ?>
							<div class="reading"><?php the_content(); ?></div>
						<?php else : ?>
							<p class="help"><?php esc_html_e( 'La biographie sera publiée dès sa validation.', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
						<?php endif; ?>

						<?php if ( ! empty( $fnc_i_links ) ) : ?>
							<h2 style="font-size:1.3rem;margin-top:32px;"><?php esc_html_e( 'Liens', 'fnc-wordpress-theme' ); ?></h2>
							<ul class="pract-list" style="margin-top:12px;">
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
						<?php endif; ?>
					</div>

					<div>
						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="president-block" style="margin:0;">
								<?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;border-radius:5px;' ) ); ?>
							</figure>
						<?php endif; ?>

						<article class="card fnc-card" style="margin-top:<?php echo has_post_thumbnail() ? '18px' : '0'; ?>;">
							<p class="card-kicker"><?php esc_html_e( 'Profil', 'fnc-wordpress-theme' ); ?></p>
							<dl class="pract-contacts" style="margin-top:14px;">
								<?php if ( $fnc_i_org ) : ?>
									<dt><?php esc_html_e( 'Organisation', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_i_org ); ?></dd>
								<?php endif; ?>
								<?php if ( $fnc_i_country ) : ?>
									<dt><?php esc_html_e( 'Pays', 'fnc-wordpress-theme' ); ?></dt>
									<dd>
										<?php foreach ( fnc_split_countries( $fnc_i_country ) as $fnc_c ) : ?>
											<span class="flag-chip" style="margin-right:10px;">
												<?php echo fnc_flag_markup( $fnc_c ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup construit et echappe par le helper. ?>
												<span><?php echo esc_html( $fnc_c ); ?></span>
											</span>
										<?php endforeach; ?>
									</dd>
								<?php endif; ?>
								<?php if ( $fnc_i_profils && ! is_wp_error( $fnc_i_profils ) ) : ?>
									<dt><?php esc_html_e( 'Type', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( implode( ', ', wp_list_pluck( $fnc_i_profils, 'name' ) ) ); ?></dd>
								<?php endif; ?>
							</dl>
						</article>
					</div>
				</div>
			</div>
		</section>

		<?php
		/*
		 * Sessions ou la personne intervient (comme intervenant OU moderateur).
		 *
		 * Filtrage en PHP plutot qu'en meta_query LIKE : la liste d'intervenants
		 * est un tableau serialise dont les ID peuvent etre stockes en entiers
		 * ou en chaines selon la voie d'ecriture (formulaire d'administration,
		 * import, wp-cli). Un LIKE sur « i:12; » raterait « s:2:"12"; » — et
		 * pourrait en plus matcher un INDICE de tableau plutot qu'une valeur.
		 * Le volume reste faible sur une vitrine, la comparaison souple ici est
		 * a la fois plus sure et plus lisible.
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
		if ( ! empty( $fnc_i_sessions ) ) :
			$fnc_types = fnc_content_model_session_types();
			?>
			<section class="section linen">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Participation', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Sessions au programme.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="agenda">
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
			</section>
		<?php endif; ?>

		<section class="section">
			<div class="container">
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_intervenant' ) ); ?>"><?php esc_html_e( 'Tous les intervenants', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
