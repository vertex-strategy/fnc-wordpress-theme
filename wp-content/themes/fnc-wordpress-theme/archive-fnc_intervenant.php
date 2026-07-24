<?php
/**
 * Archive du custom post type "fnc_intervenant".
 *
 * Porte docs/mockups/homepage-v2/intervenants.html a l'origine (contenu
 * genere par site.js: speakersPage()). Structure des filtres (profil,
 * pays) alignee sur le site officiel reel (localhost:3000/fr/intervenants),
 * suite a l'amendement de la Decision 1 de l'ADR-007 : la maquette seule
 * ne proposait aucun filtre, alors que le site officiel en implemente
 * deja (profil + pays, avec drapeaux). Contenu de demonstration reste
 * fictif — jamais les vraies identites de responsables publics visibles
 * sur le site officiel.
 *
 * Filtrage par requete GET native WordPress (tax_query via query vars
 * publics `fnc_profil`/`fnc_pays`), sans JavaScript de soumission
 * automatique — degradation gracieuse, coherent avec le reste du theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Voix', 'fnc-wordpress-theme' ),
		'title'      => __( 'Des profils qui donnent du sens aux interventions.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Profils exemples non officiels, rattachés à sessions et ressources.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-portrait.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Intervenants', 'fnc-wordpress-theme' ),
	)
);

$fnc_archive_url    = get_post_type_archive_link( 'fnc_intervenant' );
$fnc_current_profil = isset( $_GET['fnc_profil'] ) ? sanitize_title( wp_unslash( $_GET['fnc_profil'] ) ) : '';
$fnc_current_pays   = isset( $_GET['fnc_pays'] ) ? sanitize_title( wp_unslash( $_GET['fnc_pays'] ) ) : '';
$fnc_profils        = get_terms( array( 'taxonomy' => 'fnc_profil', 'hide_empty' => false ) );
$fnc_pays_terms     = get_terms( array( 'taxonomy' => 'fnc_pays', 'hide_empty' => false ) );

// Frise "pays representes" (passe gabarits) : derivee du champ texte libre
// _fnc_speaker_country (peut cumuler plusieurs pays separes par "/"),
// distinct de la taxonomie fnc_pays utilisee ci-dessous pour le filtre.
$fnc_all_speaker_ids = get_posts( array( 'post_type' => 'fnc_intervenant', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$fnc_countries       = array();
foreach ( $fnc_all_speaker_ids as $fnc_speaker_id ) {
	foreach ( fnc_split_countries( get_post_meta( $fnc_speaker_id, '_fnc_speaker_country', true ) ) as $fnc_country ) {
		$fnc_countries[ $fnc_country ] = true;
	}
}
// Ordre editorial des pays (Reglages FNC > Intervenants) ; repli tri
// alphabetique. Meme logique que countryOrder du vrai site.
$fnc_countries = fnc_order_countries( array_keys( $fnc_countries ) );
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Profils', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Profils', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>

			<?php if ( ! empty( $fnc_countries ) ) : ?>
				<div class="flag-block">
					<span class="flag-eyebrow"><?php esc_html_e( 'Pays représentés', 'fnc-wordpress-theme' ); ?></span>
					<ul class="flag-frise" aria-label="<?php esc_attr_e( 'Pays représentés', 'fnc-wordpress-theme' ); ?>">
						<?php foreach ( $fnc_countries as $fnc_country ) : ?>
							<li class="flag-chip">
								<?php echo fnc_country_flag( $fnc_country ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup construit et echappe par fnc_country_flag(). ?>
								<span><?php echo esc_html( $fnc_country ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! is_wp_error( $fnc_profils ) && ! empty( $fnc_profils ) ) : ?>
				<div class="toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Filtrer par profil', 'fnc-wordpress-theme' ); ?>">
					<a class="chip" href="<?php echo esc_url( $fnc_current_pays ? add_query_arg( 'fnc_pays', $fnc_current_pays, $fnc_archive_url ) : $fnc_archive_url ); ?>" aria-pressed="<?php echo $fnc_current_profil ? 'false' : 'true'; ?>"><?php esc_html_e( 'Tous', 'fnc-wordpress-theme' ); ?></a>
					<?php foreach ( $fnc_profils as $fnc_profil ) : ?>
						<a class="chip" href="<?php echo esc_url( add_query_arg( array_filter( array( 'fnc_profil' => $fnc_profil->slug, 'fnc_pays' => $fnc_current_pays ) ), $fnc_archive_url ) ); ?>" aria-pressed="<?php echo $fnc_current_profil === $fnc_profil->slug ? 'true' : 'false'; ?>"><?php echo esc_html( $fnc_profil->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! is_wp_error( $fnc_pays_terms ) && ! empty( $fnc_pays_terms ) ) : ?>
				<form class="field" method="get" action="<?php echo esc_url( $fnc_archive_url ); ?>" style="max-width:280px;margin-bottom:28px;">
					<?php if ( $fnc_current_profil ) : ?>
						<input type="hidden" name="fnc_profil" value="<?php echo esc_attr( $fnc_current_profil ); ?>" />
					<?php endif; ?>
					<label for="fnc-pays-select"><?php esc_html_e( 'Pays', 'fnc-wordpress-theme' ); ?></label>
					<select id="fnc-pays-select" name="fnc_pays">
						<option value=""><?php esc_html_e( 'Tous les pays', 'fnc-wordpress-theme' ); ?></option>
						<?php foreach ( $fnc_pays_terms as $fnc_pays_term ) : ?>
							<option value="<?php echo esc_attr( $fnc_pays_term->slug ); ?>" <?php selected( $fnc_current_pays, $fnc_pays_term->slug ); ?>><?php echo esc_html( $fnc_pays_term->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<button class="btn btn-soft" type="submit" style="margin-top:10px;"><?php esc_html_e( 'Filtrer', 'fnc-wordpress-theme' ); ?></button>
				</form>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						$fnc_terms_profil = get_the_terms( get_the_ID(), 'fnc_profil' );
						$fnc_terms_pays    = get_the_terms( get_the_ID(), 'fnc_pays' );
						?>
						<article class="card fnc-card">
							<p class="card-kicker">
								<?php
								echo esc_html(
									( $fnc_terms_profil && ! is_wp_error( $fnc_terms_profil ) ? $fnc_terms_profil[0]->name : __( 'Intervenant', 'fnc-wordpress-theme' ) )
									. ( $fnc_terms_pays && ! is_wp_error( $fnc_terms_pays ) ? ' · ' . $fnc_terms_pays[0]->name : '' )
								);
								?>
							</p>
							<h3><a href="<?php the_permalink(); ?>"><?php echo esc_html( fnc_speaker_display_name( get_the_ID() ) ); ?></a></h3>
							<?php $fnc_speaker_meta = fnc_speaker_meta_line( get_the_ID() ); ?>
							<?php if ( $fnc_speaker_meta ) : ?>
								<span class="person-meta"><?php echo esc_html( $fnc_speaker_meta ); ?></span>
							<?php endif; ?>
							<?php if ( has_excerpt() ) : ?>
								<p><?php the_excerpt(); ?></p>
							<?php endif; ?>
						</article>
						<?php
					endwhile;
					?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucun intervenant publié', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les profils apparaîtront ici dès leur publication.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
