<?php
/**
 * Archive du custom post type "fnc_session".
 *
 * Porte docs/mockups/homepage-v2/programme.html a l'origine (contenu
 * genere par site.js: programmePage()). Regroupement par jour avec
 * ancres de navigation, aligne sur le site officiel reel
 * (localhost:3000/fr/programme), suite a l'amendement de la Decision 1
 * de l'ADR-007 : la maquette seule ne montrait qu'une liste plate de
 * sessions d'exemple, alors que le site officiel structure deja son
 * agenda par journee. Contenu de demonstration reste fictif — jamais
 * les vraies identites de responsables publics visibles sur le site
 * officiel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Agenda', 'fnc-wordpress-theme' ),
		'title'      => __( 'Un programme lisible avant, pendant et après.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Chaque session devient un nœud de navigation vers édition, intervenants et ressources.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-pupitre.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Programme', 'fnc-wordpress-theme' ),
	)
);

// Regroupement par jour (post meta _fnc_session_jour), en conservant
// l'ordre de premiere apparition (generalement l'ordre chronologique
// des sessions dans la boucle principale).
$fnc_sessions_by_day = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$fnc_jour = get_post_meta( get_the_ID(), '_fnc_session_jour', true );
		$fnc_jour = $fnc_jour ? $fnc_jour : __( 'Jour à confirmer', 'fnc-wordpress-theme' );
		if ( ! isset( $fnc_sessions_by_day[ $fnc_jour ] ) ) {
			$fnc_sessions_by_day[ $fnc_jour ] = array();
		}
		$fnc_sessions_by_day[ $fnc_jour ][] = get_the_ID();
	}
	wp_reset_postdata();
}
?>

<main id="main">
	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Sessions', 'fnc-wordpress-theme' ); ?></h2>
				</div>
			</div>

			<?php if ( ! empty( $fnc_sessions_by_day ) ) : ?>
				<nav class="toolbar" aria-label="<?php esc_attr_e( 'Journées', 'fnc-wordpress-theme' ); ?>">
					<?php foreach ( array_keys( $fnc_sessions_by_day ) as $fnc_i => $fnc_jour_label ) : ?>
						<a class="chip" href="#fnc-jour-<?php echo esc_attr( sanitize_title( $fnc_jour_label ) ); ?>" aria-pressed="<?php echo 0 === $fnc_i ? 'true' : 'false'; ?>"><?php echo esc_html( $fnc_jour_label ); ?></a>
					<?php endforeach; ?>
				</nav>

				<?php foreach ( $fnc_sessions_by_day as $fnc_jour_label => $fnc_session_ids ) : ?>
					<h3 id="fnc-jour-<?php echo esc_attr( sanitize_title( $fnc_jour_label ) ); ?>" style="margin:36px 0 18px;color:var(--navy);"><?php echo esc_html( $fnc_jour_label ); ?></h3>
					<div class="agenda">
						<?php foreach ( $fnc_session_ids as $fnc_session_id ) : ?>
							<a class="agenda-row" href="<?php echo esc_url( get_permalink( $fnc_session_id ) ); ?>">
								<span class="time"><?php echo esc_html( get_post_meta( $fnc_session_id, '_fnc_session_time', true ) ?: '—' ); ?></span>
								<strong><?php echo esc_html( get_the_title( $fnc_session_id ) ); ?></strong>
								<span class="room"><?php echo esc_html( get_post_meta( $fnc_session_id, '_fnc_session_room', true ) ?: __( 'Salle à confirmer', 'fnc-wordpress-theme' ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune session publiée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les données finales proviennent du CMS.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
