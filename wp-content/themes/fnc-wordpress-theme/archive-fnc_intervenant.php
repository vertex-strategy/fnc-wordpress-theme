<?php
/**
 * Archive du custom post type "fnc_intervenant" — « Les voix du Forum ».
 *
 * Alignee sur la page reelle localhost:3000/fr/intervenants : heros « Les voix
 * du Forum », panorama (badge « Programme provisoire » + statistiques + frise
 * des pays representes), puis l'explorateur d'intervenants (.spk-explorer) avec
 * filtres par profil (compteurs) et par pays, et la grille de cartes .spk.
 * Se termine sur le callout « Proposer une intervention ».
 *
 * Le filtrage passe par les query vars natifs des taxonomies (fnc_profil /
 * fnc_pays) — degradation gracieuse sans JavaScript. Les compteurs sont
 * calcules cote serveur. Contenu de demonstration (jamais les vraies identites
 * du site officiel).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fnc_route_h = fnc_route_hero( 'intervenants' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Intervenants du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Intervenants', 'fnc-wordpress-theme' ),
	)
);

$fnc_archive_url    = get_post_type_archive_link( 'fnc_intervenant' );
$fnc_current_profil = isset( $_GET['fnc_profil'] ) ? sanitize_title( wp_unslash( $_GET['fnc_profil'] ) ) : '';
$fnc_current_pays   = isset( $_GET['fnc_pays'] ) ? sanitize_title( wp_unslash( $_GET['fnc_pays'] ) ) : '';
$fnc_profils        = get_terms( array( 'taxonomy' => 'fnc_profil', 'hide_empty' => false ) );
$fnc_pays_terms     = get_terms( array( 'taxonomy' => 'fnc_pays', 'hide_empty' => false ) );

// Panorama : total d'intervenants et frise des pays representes (champ texte
// libre _fnc_speaker_country, pouvant cumuler plusieurs pays separes par « / »).
$fnc_all_speaker_ids = get_posts( array( 'post_type' => 'fnc_intervenant', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$fnc_total_speakers  = count( $fnc_all_speaker_ids );
$fnc_countries       = array();
foreach ( $fnc_all_speaker_ids as $fnc_speaker_id ) {
	foreach ( fnc_split_countries( get_post_meta( $fnc_speaker_id, '_fnc_speaker_country', true ) ) as $fnc_country ) {
		$fnc_countries[ $fnc_country ] = true;
	}
}
$fnc_countries     = fnc_order_countries( array_keys( $fnc_countries ) );
$fnc_country_count = count( $fnc_countries );

// FNC Core (Module C) fait autorite sur les facettes (total, comptes par profil,
// pays), scopees aux participants de l'edition en cours. Repli sur le calcul du
// theme si le plugin est absent.
$fnc_facet_counts = array();
if ( function_exists( 'fnc_speaker_facets' ) ) {
	$fnc_facets = fnc_speaker_facets();
	if ( (int) $fnc_facets['total'] > 0 ) {
		$fnc_total_speakers = (int) $fnc_facets['total'];
		foreach ( $fnc_facets['profils'] as $fnc_pf ) { $fnc_facet_counts[ $fnc_pf['slug'] ] = (int) $fnc_pf['count']; }
		$fnc_countries = array();
		foreach ( $fnc_facets['countries'] as $fnc_ct ) { if ( (int) $fnc_ct['count'] > 0 ) { $fnc_countries[] = $fnc_ct['name']; } }
		$fnc_country_count = count( $fnc_countries );
	}
}

// Classe de categorie (.cat-*) derivee du slug de profil, comme le vrai site.
$fnc_cat_class = static function ( $slug ) {
	$slug = strtolower( (string) $slug );
	if ( false !== strpos( $slug, 'off' ) ) { return 'cat-official'; }
	if ( false !== strpos( $slug, 'exp' ) ) { return 'cat-expert'; }
	if ( false !== strpos( $slug, 'hot' ) || false !== strpos( $slug, 'host' ) ) { return 'cat-host'; }
	return 'cat-official';
};
?>

<main id="main">
	<section class="section linen intervenants-panorama">
		<div class="container">
			<?php fnc_render_badge( __( 'Programme provisoire', 'fnc-wordpress-theme' ) ); ?>

			<div class="stat-line">
				<div class="stat"><b><?php echo esc_html( number_format_i18n( $fnc_total_speakers ) ); ?></b><span><?php esc_html_e( 'intervenants', 'fnc-wordpress-theme' ); ?></span></div>
				<div class="stat"><b><?php echo esc_html( number_format_i18n( $fnc_country_count ) ); ?></b><span><?php esc_html_e( 'pays représentés', 'fnc-wordpress-theme' ); ?></span></div>
			</div>

			<?php if ( ! empty( $fnc_countries ) ) : ?>
				<div class="flag-block">
					<span class="flag-eyebrow"><?php esc_html_e( 'Pays représentés', 'fnc-wordpress-theme' ); ?></span>
					<ul class="flag-frise" aria-label="<?php esc_attr_e( 'Pays représentés', 'fnc-wordpress-theme' ); ?>">
						<?php foreach ( $fnc_countries as $fnc_country ) : ?>
							<li class="flag-chip">
								<?php echo fnc_flag_markup( $fnc_country ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup construit et echappe par fnc_flag_markup(). ?>
								<span><?php echo esc_html( $fnc_country ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section intervenants-directory">
		<div class="container">
			<div class="spk-explorer">
				<div class="spk-toolbar">
					<?php if ( ! is_wp_error( $fnc_profils ) && ! empty( $fnc_profils ) ) : ?>
						<div class="spk-filters" role="group" aria-label="<?php esc_attr_e( 'Filtrer par profil', 'fnc-wordpress-theme' ); ?>">
							<a class="spk-chip" href="<?php echo esc_url( $fnc_current_pays ? add_query_arg( 'fnc_pays', $fnc_current_pays, $fnc_archive_url ) : $fnc_archive_url ); ?>" aria-pressed="<?php echo $fnc_current_profil ? 'false' : 'true'; ?>">
								<?php esc_html_e( 'Tous', 'fnc-wordpress-theme' ); ?> <span class="spk-chip-n"><?php echo esc_html( number_format_i18n( $fnc_total_speakers ) ); ?></span>
							</a>
							<?php foreach ( $fnc_profils as $fnc_profil ) : ?>
								<a class="spk-chip" href="<?php echo esc_url( add_query_arg( array_filter( array( 'fnc_profil' => $fnc_profil->slug, 'fnc_pays' => $fnc_current_pays ) ), $fnc_archive_url ) ); ?>" aria-pressed="<?php echo $fnc_current_profil === $fnc_profil->slug ? 'true' : 'false'; ?>">
									<?php echo esc_html( $fnc_profil->name ); ?> <span class="spk-chip-n"><?php echo esc_html( number_format_i18n( isset( $fnc_facet_counts[ $fnc_profil->slug ] ) ? $fnc_facet_counts[ $fnc_profil->slug ] : $fnc_profil->count ) ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! is_wp_error( $fnc_pays_terms ) && ! empty( $fnc_pays_terms ) ) : ?>
						<form class="spk-country" method="get" action="<?php echo esc_url( $fnc_archive_url ); ?>">
							<?php if ( $fnc_current_profil ) : ?>
								<input type="hidden" name="fnc_profil" value="<?php echo esc_attr( $fnc_current_profil ); ?>" />
							<?php endif; ?>
							<label for="fnc-pays-select"><?php esc_html_e( 'Pays', 'fnc-wordpress-theme' ); ?></label>
							<select id="fnc-pays-select" name="fnc_pays" onchange="this.form.submit()">
								<option value=""><?php esc_html_e( 'Tous les pays', 'fnc-wordpress-theme' ); ?></option>
								<?php foreach ( $fnc_pays_terms as $fnc_pays_term ) : ?>
									<option value="<?php echo esc_attr( $fnc_pays_term->slug ); ?>" <?php selected( $fnc_current_pays, $fnc_pays_term->slug ); ?>><?php echo esc_html( $fnc_pays_term->name ); ?></option>
								<?php endforeach; ?>
							</select>
							<noscript><button class="btn btn-soft" type="submit"><?php esc_html_e( 'Filtrer', 'fnc-wordpress-theme' ); ?></button></noscript>
						</form>
					<?php endif; ?>
				</div>

				<?php if ( have_posts() ) : ?>
					<div class="spk-resultbar">
						<p class="spk-result" aria-live="polite">
							<?php
							/* translators: %s: nombre d'intervenants. */
							printf( esc_html( _n( '%s intervenant', '%s intervenants', $wp_query->post_count, 'fnc-wordpress-theme' ) ), esc_html( number_format_i18n( $wp_query->post_count ) ) );
							?>
						</p>
					</div>

					<div class="spk-grid">
						<?php
						while ( have_posts() ) :
							the_post();
							$fnc_sp_id      = get_the_ID();
							$fnc_sp_profils = get_the_terms( $fnc_sp_id, 'fnc_profil' );
							$fnc_sp_profil  = ( $fnc_sp_profils && ! is_wp_error( $fnc_sp_profils ) ) ? $fnc_sp_profils[0] : null;
							$fnc_sp_org     = get_post_meta( $fnc_sp_id, '_fnc_speaker_org', true );
							$fnc_sp_country = get_post_meta( $fnc_sp_id, '_fnc_speaker_country', true );
							?>
							<a class="spk" href="<?php the_permalink(); ?>">
								<?php if ( $fnc_sp_profil ) : ?>
									<span class="cat <?php echo esc_attr( $fnc_cat_class( $fnc_sp_profil->slug ) ); ?>"><?php echo esc_html( $fnc_sp_profil->name ); ?></span>
								<?php endif; ?>
								<div class="ph">
									<?php
									// RÈGLE 7 : fnc_speaker_portrait ne renvoie la photo que si le droit est
									// « obtenu » et non expire ; sinon on affiche l'illustration neutre (jamais la vraie photo).
									$fnc_portrait = function_exists( 'fnc_speaker_portrait' )
										? fnc_speaker_portrait( $fnc_sp_id, 'medium', array( 'alt' => fnc_speaker_display_name( $fnc_sp_id ) ) )
										: ( has_post_thumbnail() ? get_the_post_thumbnail( $fnc_sp_id, 'medium', array( 'alt' => esc_attr( fnc_speaker_display_name( $fnc_sp_id ) ) ) ) : '' );
									if ( $fnc_portrait ) {
										echo $fnc_portrait; // phpcs:ignore WordPress.Security.EscapeOutput -- markup <img> genere par WP/plugin.
									} else {
										printf( '<img src="%s" alt="" aria-hidden="true" />', esc_url( get_template_directory_uri() . '/assets/images/le-portrait.png' ) );
									}
									?>
								</div>
								<div class="n"><?php echo esc_html( fnc_speaker_display_name( $fnc_sp_id ) ); ?></div>
								<?php if ( $fnc_sp_org ) : ?>
									<div class="r"><?php echo esc_html( $fnc_sp_org ); ?></div>
								<?php endif; ?>
								<?php if ( $fnc_sp_country ) : ?>
									<span class="c"><?php echo esc_html( $fnc_sp_country ); ?></span>
								<?php endif; ?>
							</a>
							<?php
						endwhile;
						?>
					</div>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<div class="empty" role="status">
						<h3><?php esc_html_e( 'Aucun intervenant ne correspond', 'fnc-wordpress-theme' ); ?></h3>
						<p><?php esc_html_e( 'Modifiez le filtre pour poursuivre, ou revenez à la liste complète.', 'fnc-wordpress-theme' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="callout">
		<h2 data-fnc-st="intervenants.cta.title"><?php echo esc_html( fnc_stitle( 'intervenants', 'cta', 'title' ) ); ?></h2>
		<p><?php esc_html_e( 'Vous souhaitez contribuer au programme ? Les candidatures d’intervenants seront ouvertes prochainement.', 'fnc-wordpress-theme' ); ?> <span class="tbc" style="color:#fff"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
		<a class="btn btn-red" href="<?php echo esc_url( fnc_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Nous contacter', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
