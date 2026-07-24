<?php
/**
 * Archive du custom post type "fnc_publication".
 *
 * Porte docs/mockups/homepage-v2/publications.html a l'origine (contenu
 * genere par site.js: publicationsPage()). Recherche + filtre alignes sur
 * le site officiel reel (localhost:3000/fr/ressources).
 *
 * Passe gabarits (reconciliation du modele de contenu) : le filtre
 * principal utilise desormais le champ dedie `_fnc_publication_type` (8
 * valeurs), comme sur la vraie page /ressources — pas la taxonomie
 * fnc_categorie, qui reste une classification interne distincte (le vrai
 * schema Payload separe deja `type` et `category`, voir Publications.ts).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Ressources', 'fnc-wordpress-theme' ),
		'title'      => __( 'La réflexion continue après les sessions.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Rapports, communiqués, vidéos et analyses sont structurés sans contenu fictif.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Publications', 'fnc-wordpress-theme' ),
	)
);

$fnc_archive_url      = get_post_type_archive_link( 'fnc_publication' );
$fnc_current_type     = isset( $_GET['fnc_type'] ) ? sanitize_text_field( wp_unslash( $_GET['fnc_type'] ) ) : '';
$fnc_search_query     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$fnc_publication_types = fnc_content_model_publication_types();
$fnc_current_type     = array_key_exists( $fnc_current_type, $fnc_publication_types ) ? $fnc_current_type : '';

$fnc_meta_query = array();
if ( $fnc_current_type ) {
	$fnc_meta_query[] = array( 'key' => '_fnc_publication_type', 'value' => $fnc_current_type );
}

$fnc_publications = get_posts(
	array(
		'post_type'      => 'fnc_publication',
		'posts_per_page' => -1,
		's'              => $fnc_search_query,
		'meta_query'     => $fnc_meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- volume faible (vitrine de demonstration).
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<form class="field" method="get" action="<?php echo esc_url( $fnc_archive_url ); ?>" role="search" style="max-width:420px;margin-bottom:20px;">
				<label for="fnc-publication-search"><?php esc_html_e( 'Recherche', 'fnc-wordpress-theme' ); ?></label>
				<input id="fnc-publication-search" type="search" name="s" value="<?php echo esc_attr( $fnc_search_query ); ?>" placeholder="<?php esc_attr_e( 'Rechercher une publication', 'fnc-wordpress-theme' ); ?>" />
				<?php if ( $fnc_current_type ) : ?>
					<input type="hidden" name="fnc_type" value="<?php echo esc_attr( $fnc_current_type ); ?>" />
				<?php endif; ?>
				<button class="btn btn-soft" type="submit" style="margin-top:10px;"><?php esc_html_e( 'Rechercher', 'fnc-wordpress-theme' ); ?></button>
			</form>

			<div class="toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Filtrer par type', 'fnc-wordpress-theme' ); ?>">
				<a class="chip" href="<?php echo esc_url( $fnc_search_query ? add_query_arg( 's', $fnc_search_query, $fnc_archive_url ) : $fnc_archive_url ); ?>" aria-pressed="<?php echo $fnc_current_type ? 'false' : 'true'; ?>"><?php esc_html_e( 'Tous', 'fnc-wordpress-theme' ); ?></a>
				<?php foreach ( $fnc_publication_types as $fnc_type_value => $fnc_type_label ) : ?>
					<a class="chip" href="<?php echo esc_url( add_query_arg( array_filter( array( 'fnc_type' => $fnc_type_value, 's' => $fnc_search_query ) ), $fnc_archive_url ) ); ?>" aria-pressed="<?php echo $fnc_current_type === $fnc_type_value ? 'true' : 'false'; ?>"><?php echo esc_html( $fnc_type_label ); ?></a>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $fnc_publications ) ) : ?>
				<div class="grid grid-3">
					<?php foreach ( $fnc_publications as $fnc_publication ) : ?>
						<?php
						$fnc_type      = get_post_meta( $fnc_publication->ID, '_fnc_publication_type', true );
						$fnc_media_url = get_post_meta( $fnc_publication->ID, '_fnc_publication_media_url', true );
						$fnc_is_media  = in_array( $fnc_type, array( 'video', 'interview' ), true );
						?>
						<article class="card fnc-card">
							<p class="card-kicker"><?php echo esc_html( isset( $fnc_publication_types[ $fnc_type ] ) ? $fnc_publication_types[ $fnc_type ] : __( 'Publication', 'fnc-wordpress-theme' ) ); ?></p>
							<h3><a href="<?php echo esc_url( get_permalink( $fnc_publication ) ); ?>"><?php echo esc_html( get_the_title( $fnc_publication ) ); ?></a></h3>
							<?php if ( has_excerpt( $fnc_publication ) ) : ?>
								<p><?php echo esc_html( get_the_excerpt( $fnc_publication ) ); ?></p>
							<?php endif; ?>
							<?php if ( $fnc_is_media && $fnc_media_url ) : ?>
								<p style="margin-top:14px;"><a class="link-more" href="<?php echo esc_url( $fnc_media_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Regarder', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune publication validée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'L’état vide reste sobre, sans faux contenu.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
