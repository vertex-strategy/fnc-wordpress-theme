<?php
/**
 * Fiche d'une session (single-fnc_session.php).
 *
 * Comble un manque : les liens du programme (archive des sessions, page
 * Edition en cours, fiche d'edition) retombaient sur index.php, qui n'affiche
 * qu'un extrait — la fiche etait donc quasi vide.
 *
 * Une session est une vue de son edition (invariant du vrai site) : on affiche
 * son rattachement, son type, son horaire, sa salle, son moderateur et ses
 * intervenants — en distinguant bien moderateur et intervenants, comme le
 * fait le vrai site. Chaque element est masque si la donnee n'existe pas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

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
	$fnc_types       = fnc_content_model_session_types();

	fnc_render_hero(
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
		<section class="section">
			<div class="container">
				<div class="split">
					<div>
						<?php if ( get_the_content() ) : ?>
							<div class="reading"><?php the_content(); ?></div>
						<?php endif; ?>

						<?php if ( ! empty( $fnc_s_objectifs ) ) : ?>
							<h2 style="font-size:1.4rem;margin-top:32px;"><?php esc_html_e( 'Objectifs', 'fnc-wordpress-theme' ); ?></h2>
							<ul class="pract-list" style="margin-top:14px;">
								<?php foreach ( $fnc_s_objectifs as $fnc_obj ) : ?>
									<li><?php echo esc_html( $fnc_obj ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( $fnc_s_note ) : ?>
							<p class="frise-note" style="margin-top:24px;"><?php echo esc_html( $fnc_s_note ); ?></p>
						<?php endif; ?>
					</div>

					<div>
						<article class="card fnc-card">
							<p class="card-kicker"><?php esc_html_e( 'Repères', 'fnc-wordpress-theme' ); ?></p>
							<dl class="pract-contacts" style="margin-top:14px;">
								<?php if ( isset( $fnc_types[ $fnc_s_type ] ) ) : ?>
									<dt><?php esc_html_e( 'Type', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_types[ $fnc_s_type ] ); ?></dd>
								<?php endif; ?>
								<?php if ( $fnc_s_jour ) : ?>
									<dt><?php esc_html_e( 'Jour', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_s_jour ); ?></dd>
								<?php endif; ?>
								<?php if ( $fnc_s_time ) : ?>
									<dt><?php esc_html_e( 'Horaire', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_s_time ); ?></dd>
								<?php endif; ?>
								<?php if ( $fnc_s_room ) : ?>
									<dt><?php esc_html_e( 'Salle', 'fnc-wordpress-theme' ); ?></dt>
									<dd><?php echo esc_html( $fnc_s_room ); ?></dd>
								<?php endif; ?>
								<?php if ( $fnc_s_edition > 0 ) : ?>
									<dt><?php esc_html_e( 'Édition', 'fnc-wordpress-theme' ); ?></dt>
									<dd><a href="<?php echo esc_url( get_permalink( $fnc_s_edition ) ); ?>"><?php echo esc_html( get_the_title( $fnc_s_edition ) ); ?></a></dd>
								<?php endif; ?>
							</dl>
						</article>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $fnc_s_moderator > 0 || ! empty( $fnc_s_speakers ) ) : ?>
			<section class="section linen">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Qui intervient', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Modérateur et intervenants.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>

					<?php if ( $fnc_s_moderator > 0 ) : ?>
						<div class="grid grid-3" style="margin-bottom:18px;">
							<article class="card fnc-card">
								<p class="card-kicker"><?php esc_html_e( 'Modération', 'fnc-wordpress-theme' ); ?></p>
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_s_moderator ) ); ?>"><?php echo esc_html( fnc_speaker_display_name( $fnc_s_moderator ) ); ?></a></h3>
								<?php $fnc_mod_meta = fnc_speaker_meta_line( $fnc_s_moderator ); ?>
								<?php if ( $fnc_mod_meta ) : ?>
									<span class="person-meta"><?php echo esc_html( $fnc_mod_meta ); ?></span>
								<?php endif; ?>
							</article>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $fnc_s_speakers ) ) : ?>
						<div class="grid grid-3">
							<?php foreach ( $fnc_s_speakers as $fnc_sp_id ) : ?>
								<article class="card fnc-card">
									<p class="card-kicker"><?php esc_html_e( 'Intervenant', 'fnc-wordpress-theme' ); ?></p>
									<h3><a href="<?php echo esc_url( get_permalink( $fnc_sp_id ) ); ?>"><?php echo esc_html( fnc_speaker_display_name( $fnc_sp_id ) ); ?></a></h3>
									<?php $fnc_sp_meta = fnc_speaker_meta_line( $fnc_sp_id ); ?>
									<?php if ( $fnc_sp_meta ) : ?>
										<span class="person-meta"><?php echo esc_html( $fnc_sp_meta ); ?></span>
									<?php endif; ?>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
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
			<section class="section">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Pour aller plus loin', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Ressources de cette édition.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="grid grid-3">
						<?php foreach ( $fnc_s_resources as $fnc_sr ) : ?>
							<?php $fnc_sr_type = get_post_meta( $fnc_sr->ID, '_fnc_publication_type', true ); ?>
							<article class="card fnc-card">
								<p class="card-kicker"><?php echo esc_html( isset( $fnc_pub_types[ $fnc_sr_type ] ) ? $fnc_pub_types[ $fnc_sr_type ] : __( 'Publication', 'fnc-wordpress-theme' ) ); ?></p>
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_sr ) ); ?>"><?php echo esc_html( get_the_title( $fnc_sr ) ); ?></a></h3>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="section linen">
			<div class="container">
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_session' ) ); ?>"><?php esc_html_e( 'Voir tout le programme', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>
	</main>

	<?php
endwhile;

get_footer();
