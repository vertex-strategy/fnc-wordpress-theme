<?php
/**
 * Forum Numérique Congo — fiche détaillée d’une édition.
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

	$fnc_ed_id       = get_the_ID();
	$fnc_ed_year     = get_post_meta( $fnc_ed_id, '_fnc_edition_year', true );
	$fnc_ed_status   = get_post_meta( $fnc_ed_id, '_fnc_edition_status', true );
	$fnc_ed_theme    = get_post_meta( $fnc_ed_id, '_fnc_edition_theme', true );
	$fnc_ed_start    = get_post_meta( $fnc_ed_id, '_fnc_edition_start_date', true );
	$fnc_ed_end      = get_post_meta( $fnc_ed_id, '_fnc_edition_end_date', true );
	$fnc_ed_location = get_post_meta( $fnc_ed_id, '_fnc_edition_location', true );
	$fnc_ed_special  = get_post_meta( $fnc_ed_id, '_fnc_edition_special_note', true );
	$fnc_ed_is_spec  = get_post_meta( $fnc_ed_id, '_fnc_edition_is_special', true );
	$fnc_statuses    = fnc_content_model_edition_statuses();

	$fnc_ed_dates = '';
	if ( $fnc_ed_start ) {
		$fnc_ed_dates = date_i18n( 'j F Y', strtotime( $fnc_ed_start ) );
		if ( $fnc_ed_end && $fnc_ed_end !== $fnc_ed_start ) {
			$fnc_ed_dates .= ' – ' . date_i18n( 'j F Y', strtotime( $fnc_ed_end ) );
		}
	}

	fnc_render_opening_hero(
		array(
			'eyebrow'    => $fnc_ed_year ? $fnc_ed_year : __( 'Édition', 'fnc-wordpress-theme' ),
			'title'      => get_the_title(),
			'lead'       => $fnc_ed_theme,
			'image'      => has_post_thumbnail() ? get_the_post_thumbnail_url( $fnc_ed_id, 'full' ) : get_template_directory_uri() . '/assets/images/le-territoire-brazzaville.png',
			'image_alt'  => '',
			'breadcrumb' => get_the_title(),
		)
	);

	$fnc_ed_pratique = fnc_render_practical_info( $fnc_ed_id );
	?>

	<main id="main">
		<section class="section">
			<div class="container">
				<p class="frise-meta">
					<?php if ( isset( $fnc_statuses[ $fnc_ed_status ] ) ) : ?>
						<?php fnc_render_badge( $fnc_statuses[ $fnc_ed_status ] ); ?>
					<?php endif; ?>
					<?php if ( $fnc_ed_dates ) : ?>
						<b><?php echo esc_html( $fnc_ed_dates ); ?></b>
					<?php endif; ?>
					<?php if ( $fnc_ed_dates && $fnc_ed_location ) : ?> · <?php endif; ?>
					<?php echo esc_html( $fnc_ed_location ); ?>
				</p>
				<?php if ( $fnc_ed_is_spec && $fnc_ed_special ) : ?>
					<p class="frise-note"><?php echo esc_html( $fnc_ed_special ); ?></p>
				<?php endif; ?>

				<?php
				// Contenu editorial SANS les rubriques pratiques : celles-ci sont
				// composees dans ce meme contenu mais rendues dans leur section
				// dediee ci-dessous — sans exclusion, elles s'afficheraient deux fois.
				$fnc_ed_content = fnc_render_content_excluding_practical( $fnc_ed_id );
				if ( '' !== trim( wp_strip_all_tags( $fnc_ed_content ) ) ) :
					?>
					<div class="reading" style="margin-top:28px;">
						<?php echo $fnc_ed_content; // phpcs:ignore WordPress.Security.EscapeOutput -- passe par les filtres the_content. ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( '' !== trim( $fnc_ed_pratique ) ) : ?>
			<section class="section linen">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Sur place', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Informations pratiques.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="pract-grid">
						<?php echo $fnc_ed_pratique; // phpcs:ignore WordPress.Security.EscapeOutput -- markup produit par les renderers de blocs, deja echappe. ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		// Sessions rattachees a cette edition.
		$fnc_ed_sessions = get_posts(
			array(
				'post_type'      => 'fnc_session',
				'posts_per_page' => -1,
				'meta_key'       => '_fnc_session_edition',
				'meta_value'     => $fnc_ed_id,
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);
		if ( ! empty( $fnc_ed_sessions ) ) :
			$fnc_session_types = fnc_content_model_session_types();
			?>
			<section class="section">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Sessions de cette édition.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="agenda">
						<?php foreach ( $fnc_ed_sessions as $fnc_es ) : ?>
							<?php
							$fnc_es_type = get_post_meta( $fnc_es->ID, '_fnc_session_type', true );
							$fnc_es_hide = in_array( $fnc_es_type, array( 'pause', 'logistique' ), true );
							?>
							<a class="agenda-row" href="<?php echo esc_url( get_permalink( $fnc_es ) ); ?>">
								<span class="time"><?php echo esc_html( get_post_meta( $fnc_es->ID, '_fnc_session_time', true ) ?: '—' ); ?></span>
								<span>
									<strong><?php echo esc_html( get_the_title( $fnc_es ) ); ?></strong>
									<?php if ( $fnc_es_type && ! $fnc_es_hide && isset( $fnc_session_types[ $fnc_es_type ] ) ) : ?>
										<?php fnc_render_badge( $fnc_session_types[ $fnc_es_type ] ); ?>
									<?php endif; ?>
								</span>
								<span class="room"><?php echo esc_html( get_post_meta( $fnc_es->ID, '_fnc_session_room', true ) ?: __( 'Salle à confirmer', 'fnc-wordpress-theme' ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		// Ressources rattachees a cette edition.
		$fnc_ed_pubs = get_posts(
			array(
				'post_type'      => 'fnc_publication',
				'posts_per_page' => -1,
				'meta_key'       => '_fnc_publication_edition',
				'meta_value'     => $fnc_ed_id,
			)
		);
		if ( ! empty( $fnc_ed_pubs ) ) :
			$fnc_pub_types = fnc_content_model_publication_types();
			?>
			<section class="section linen">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Ressources', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Publications liées.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="grid grid-3">
						<?php foreach ( $fnc_ed_pubs as $fnc_ep ) : ?>
							<?php $fnc_ep_type = get_post_meta( $fnc_ep->ID, '_fnc_publication_type', true ); ?>
							<article class="card fnc-card">
								<p class="card-kicker"><?php echo esc_html( isset( $fnc_pub_types[ $fnc_ep_type ] ) ? $fnc_pub_types[ $fnc_ep_type ] : __( 'Publication', 'fnc-wordpress-theme' ) ); ?></p>
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_ep ) ); ?>"><?php echo esc_html( get_the_title( $fnc_ep ) ); ?></a></h3>
								<?php if ( has_excerpt( $fnc_ep ) ) : ?>
									<p><?php echo esc_html( get_the_excerpt( $fnc_ep ) ); ?></p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php fnc_render_cta_band(); ?>
	</main>

	<?php
endwhile;

get_footer();
