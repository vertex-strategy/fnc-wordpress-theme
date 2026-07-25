<?php
/**
 * Archive du custom post type "fnc_session" — le Programme.
 *
 * Alignee sur la page reelle localhost:3000/fr/programme : heros « Deux jours de
 * dialogue », panorama (badge « Programme provisoire » + statistiques
 * intervenants / pays / jours), puis l'agenda groupe par journee
 * (.programme-detail > .agenda-day > .sess), et le callout « Réservez votre
 * place ». Le regroupement se fait sur _fnc_session_jour, dans l'ordre de la
 * requete principale. Contenu de demonstration (jamais les vraies identites du
 * site officiel).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fnc_route_h = fnc_route_hero( 'programme' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Programme du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Programme', 'fnc-wordpress-theme' ),
	)
);

// Regroupement des sessions par journee (ordre de premiere apparition).
$fnc_sessions_by_day = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$fnc_jour = get_post_meta( get_the_ID(), '_fnc_session_jour', true );
		$fnc_jour = $fnc_jour ? $fnc_jour : __( 'Jour à confirmer', 'fnc-wordpress-theme' );
		$fnc_sessions_by_day[ $fnc_jour ][] = get_the_ID();
	}
	wp_reset_postdata();
	// Journees dans l'ordre (« Jour 1 » avant « Jour 2 »…), la requete principale
	// ne garantissant pas l'ordre chronologique entre journees.
	ksort( $fnc_sessions_by_day, SORT_NATURAL | SORT_FLAG_CASE );
}

// FNC Core (Module C) fait autorite sur le regroupement/tri/scoping edition :
// on prefere ses donnees quand elles existent ; le gabarit garde le rendu.
if ( function_exists( 'fnc_edition_sessions_by_day' ) ) {
	$fnc_plugin_days = fnc_edition_sessions_by_day();
	if ( ! empty( $fnc_plugin_days ) ) {
		$fnc_sessions_by_day = array();
		foreach ( $fnc_plugin_days as $fnc_pd ) {
			/* translators: %s: numero du jour. */
			$fnc_label = sprintf( __( 'Jour %s', 'fnc-wordpress-theme' ), $fnc_pd['day'] );
			$fnc_sessions_by_day[ $fnc_label ] = wp_list_pluck( $fnc_pd['sessions'], 'id' );
		}
	}
}

// Statistiques du panorama : intervenants, pays representes, journees.
$fnc_speaker_ids = get_posts( array( 'post_type' => 'fnc_intervenant', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$fnc_countries   = array();
foreach ( $fnc_speaker_ids as $fnc_sid ) {
	foreach ( fnc_split_countries( get_post_meta( $fnc_sid, '_fnc_speaker_country', true ) ) as $fnc_c ) {
		$fnc_countries[ $fnc_c ] = true;
	}
}
$fnc_stat_speakers = count( $fnc_speaker_ids );
$fnc_stat_pays     = count( $fnc_countries );
$fnc_stat_jours    = count( $fnc_sessions_by_day );

$fnc_session_types = fnc_content_model_session_types();
// Types stylables par le kit (.type.t-*) ; « debat » retombe sur t-session.
$fnc_type_class = static function ( $slug ) {
	$known = array( 'allocution', 'inaugurale', 'interview', 'table-ronde', 'session', 'pause', 'logistique', 'cloture' );
	return in_array( $slug, $known, true ) ? 't-' . $slug : 't-session';
};
?>

<main id="main">
	<section class="section linen programme-panorama">
		<div class="container">
			<?php fnc_render_badge( __( 'Programme provisoire', 'fnc-wordpress-theme' ) ); ?>
			<div class="stat-line">
				<div class="stat"><b><?php echo esc_html( number_format_i18n( $fnc_stat_speakers ) ); ?></b><span><?php esc_html_e( 'intervenants', 'fnc-wordpress-theme' ); ?></span></div>
				<div class="stat"><b><?php echo esc_html( number_format_i18n( $fnc_stat_pays ) ); ?></b><span><?php esc_html_e( 'pays représentés', 'fnc-wordpress-theme' ); ?></span></div>
				<div class="stat"><b><?php echo esc_html( number_format_i18n( $fnc_stat_jours ) ); ?></b><span><?php esc_html_e( 'jours', 'fnc-wordpress-theme' ); ?></span></div>
			</div>
		</div>
	</section>

	<section class="section programme-detail">
		<div class="container">
			<?php if ( ! empty( $fnc_sessions_by_day ) ) : ?>
				<?php $fnc_day_index = 0; ?>
				<?php foreach ( $fnc_sessions_by_day as $fnc_jour_label => $fnc_session_ids ) : ?>
					<?php $fnc_day_index++; ?>
					<div class="agenda-day" id="fnc-jour-<?php echo esc_attr( sanitize_title( $fnc_jour_label ) ); ?>">
						<h2><?php echo esc_html( $fnc_jour_label ); ?></h2>
						<div class="rule" aria-hidden="true"></div>
						<?php foreach ( $fnc_session_ids as $fnc_session_id ) : ?>
							<?php
							$fnc_type      = get_post_meta( $fnc_session_id, '_fnc_session_type', true );
							$fnc_time      = get_post_meta( $fnc_session_id, '_fnc_session_time', true );
							$fnc_room      = get_post_meta( $fnc_session_id, '_fnc_session_room', true );
							$fnc_moderator = (int) get_post_meta( $fnc_session_id, '_fnc_session_moderator', true );
							?>
							<div class="sess">
								<div class="when"><?php echo esc_html( $fnc_time ? $fnc_time : '—' ); ?></div>
								<div>
									<?php if ( $fnc_type && isset( $fnc_session_types[ $fnc_type ] ) ) : ?>
										<span class="type <?php echo esc_attr( $fnc_type_class( $fnc_type ) ); ?>"><?php echo esc_html( $fnc_session_types[ $fnc_type ] ); ?></span>
									<?php endif; ?>
									<div class="s-title"><a href="<?php echo esc_url( get_permalink( $fnc_session_id ) ); ?>"><?php echo esc_html( get_the_title( $fnc_session_id ) ); ?></a></div>
									<?php if ( $fnc_moderator > 0 || $fnc_room ) : ?>
										<div class="s-mod">
											<?php if ( $fnc_moderator > 0 ) : ?>
												<?php esc_html_e( 'Modération', 'fnc-wordpress-theme' ); ?> : <?php echo esc_html( fnc_speaker_display_name( $fnc_moderator ) ); ?>
											<?php endif; ?>
											<?php if ( $fnc_moderator > 0 && $fnc_room ) : ?> · <?php endif; ?>
											<?php echo esc_html( $fnc_room ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune session publiée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Le programme détaillé sera publié après validation des intervenants et des horaires.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="callout">
		<h2 data-fnc-st="programme.cta.title"><?php echo esc_html( fnc_stitle( 'programme', 'cta', 'title' ) ); ?></h2>
		<p><?php esc_html_e( 'L’accès aux sessions se fait sur inscription. L’ouverture des inscriptions sera annoncée prochainement.', 'fnc-wordpress-theme' ); ?> <span class="tbc" style="color:#fff"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
		<a class="btn btn-red" href="<?php echo esc_url( fnc_page_url( 'inscription' ) ); ?>"><?php esc_html_e( 'Réserver votre place', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
