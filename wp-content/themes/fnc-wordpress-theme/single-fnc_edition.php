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

	$fnc_ed_dates = fnc_format_date_range( $fnc_ed_start, $fnc_ed_end );

	// Libellé de statut (« Édition en cours / À venir / Édition passée »), en
	// sur-titre du hero — comme le site du Forum.
	$fnc_status_labels = array(
		'current'  => __( 'Édition en cours', 'fnc-wordpress-theme' ),
		'upcoming' => __( 'À venir', 'fnc-wordpress-theme' ),
		'past'     => __( 'Édition passée', 'fnc-wordpress-theme' ),
	);
	$fnc_status_label = isset( $fnc_status_labels[ $fnc_ed_status ] ) ? $fnc_status_labels[ $fnc_ed_status ] : ( $fnc_ed_year ? $fnc_ed_year : __( 'Édition', 'fnc-wordpress-theme' ) );

	fnc_render_opening_hero(
		array(
			'eyebrow'    => $fnc_status_label,
			'title'      => get_the_title(),
			'intro'      => $fnc_ed_theme ? '« ' . $fnc_ed_theme . ' »' : '',
			'image'      => has_post_thumbnail() ? get_the_post_thumbnail_url( $fnc_ed_id, 'full' ) : get_template_directory_uri() . '/assets/images/le-programme.png',
			'image_alt'  => '',
			'breadcrumb' => get_the_title(),
		)
	);
	?>

	<main id="main">
		<?php // Dates + lieu (structure .split du site du Forum). ?>
		<section class="section">
			<div class="split">
				<div>
					<span class="eyebrow"><?php esc_html_e( 'Dates', 'fnc-wordpress-theme' ); ?></span>
					<p class="body"><?php echo $fnc_ed_dates ? esc_html( $fnc_ed_dates ) : '<span class="tbc">' . esc_html__( 'À confirmer', 'fnc-wordpress-theme' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div>
					<span class="eyebrow"><?php esc_html_e( 'Lieu', 'fnc-wordpress-theme' ); ?></span>
					<p class="body"><?php echo $fnc_ed_location ? esc_html( $fnc_ed_location ) : '<span class="tbc">' . esc_html__( 'À confirmer', 'fnc-wordpress-theme' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
			</div>
			<?php if ( 'current' === $fnc_ed_status ) : ?>
				<a class="link-more" href="<?php echo esc_url( fnc_page_url( 'edition-en-cours' ) ); ?>" style="margin-top:24px;display:inline-block;"><?php esc_html_e( 'Voir le hub de l’édition', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
			<?php endif; ?>
		</section>

		<?php
		// Sessions de cette édition (top 6, hors pause/logistique) — structure .session.
		$fnc_ed_sessions = get_posts(
			array(
				'post_type'      => 'fnc_session',
				'posts_per_page' => -1,
				'meta_key'       => '_fnc_session_start',
				'meta_query'     => array( array( 'key' => '_fnc_session_edition', 'value' => $fnc_ed_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'        => 'menu_order date',
				'order'          => 'ASC',
			)
		);
		$fnc_ed_prog = array();
		foreach ( $fnc_ed_sessions as $fnc_es ) {
			$fnc_es_type = get_post_meta( $fnc_es->ID, '_fnc_session_type', true );
			if ( ! in_array( $fnc_es_type, array( 'pause', 'logistique' ), true ) ) {
				$fnc_ed_prog[] = $fnc_es;
			}
		}
		$fnc_ed_prog = array_slice( $fnc_ed_prog, 0, 6 );
		if ( ! empty( $fnc_ed_prog ) ) :
			?>
			<section class="section linen">
				<span class="eyebrow"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></span>
				<div class="rule" aria-hidden="true" style="margin-top:12px;"></div>
				<div role="list">
					<?php foreach ( $fnc_ed_prog as $fnc_es ) : ?>
						<?php
						$fnc_es_jour  = get_post_meta( $fnc_es->ID, '_fnc_session_jour', true );
						/* translators: %s: numéro du jour. */
						$fnc_es_jour  = $fnc_es_jour ? sprintf( __( 'Jour %s', 'fnc-wordpress-theme' ), $fnc_es_jour ) : '';
						$fnc_es_start = get_post_meta( $fnc_es->ID, '_fnc_session_start', true );
						$fnc_es_time  = trim( implode( ' · ', array_filter( array( $fnc_es_jour, $fnc_es_start ) ) ) );
						?>
						<div class="session" role="listitem">
							<span class="time"><?php echo esc_html( $fnc_es_time ); ?></span>
							<a class="title s-title-link" href="<?php echo esc_url( get_permalink( $fnc_es ) ); ?>"><?php echo esc_html( get_the_title( $fnc_es ) ); ?></a>
						</div>
					<?php endforeach; ?>
				</div>
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_session' ) ); ?>"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
			</section>
		<?php endif; ?>

		<?php
		// Intervenants de l'édition (participants dérivés des sessions), top 8 —
		// grille .spk-grid is-preview du site du Forum.
		$fnc_ed_parts = function_exists( 'fnc_edition_participants' ) ? array_slice( fnc_edition_participants( $fnc_ed_id ), 0, 8 ) : array();
		if ( ! empty( $fnc_ed_parts ) ) :
			?>
			<section class="section">
				<span class="eyebrow"><?php esc_html_e( 'Intervenants', 'fnc-wordpress-theme' ); ?></span>
				<div class="spk-grid is-preview" style="margin-top:32px;">
					<?php foreach ( $fnc_ed_parts as $fnc_pid ) : ?>
						<?php
						$fnc_p_role     = get_post_meta( $fnc_pid, '_fnc_speaker_role', true );
						$fnc_p_country  = get_post_meta( $fnc_pid, '_fnc_speaker_country', true );
						$fnc_p_portrait = function_exists( 'fnc_speaker_portrait' ) ? fnc_speaker_portrait( $fnc_pid, 'medium', array( 'alt' => fnc_speaker_display_name( $fnc_pid ) ) ) : '';
						?>
						<a class="spk" href="<?php echo esc_url( get_permalink( $fnc_pid ) ); ?>">
							<div class="ph">
								<?php
								if ( $fnc_p_portrait ) {
									echo $fnc_p_portrait; // phpcs:ignore WordPress.Security.EscapeOutput -- markup <img> genere par WP/plugin.
								} else {
									printf( '<span class="m" aria-hidden="true">%s</span><span class="l">%s</span>', esc_html( fnc_speaker_initials( $fnc_pid ) ), esc_html__( 'Photo à venir', 'fnc-wordpress-theme' ) );
								}
								?>
							</div>
							<div class="n"><?php echo esc_html( fnc_speaker_display_name( $fnc_pid ) ); ?></div>
							<?php if ( $fnc_p_role ) : ?><div class="r"><?php echo esc_html( $fnc_p_role ); ?></div><?php endif; ?>
							<?php if ( $fnc_p_country ) : ?><span class="c"><?php echo esc_html( $fnc_p_country ); ?></span><?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_intervenant' ) ); ?>" style="margin-top:28px;display:inline-block;"><?php esc_html_e( 'Intervenants', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
			</section>
		<?php endif; ?>

		<?php
		// ===== RÉTROSPECTIVE & RESSOURCES (parité editions/[slug]) =====
		// 6 sections SÉPARÉES, chacune masquée si vide (content readiness gate) :
		// 1) Chiffres clés · 2) Bilan · 3) Partenaires par niveau (cartes) ·
		// 4) Ressources (documents) · 5) Vidéos (façade) · 6) Galerie.
		$fnc_ed_review  = get_post_meta( $fnc_ed_id, '_fnc_edition_review', true );
		$fnc_ed_figures = get_post_meta( $fnc_ed_id, '_fnc_edition_figures', true );
		$fnc_ed_gallery = get_post_meta( $fnc_ed_id, '_fnc_edition_gallery', true );
		$fnc_ed_figures = is_array( $fnc_ed_figures ) ? $fnc_ed_figures : array();
		$fnc_ed_gallery = is_array( $fnc_ed_gallery ) ? $fnc_ed_gallery : array();

		$fnc_ed_pubs = get_posts( array( 'post_type' => 'fnc_publication', 'posts_per_page' => -1, 'meta_key' => '_fnc_publication_edition', 'meta_value' => $fnc_ed_id, 'orderby' => 'date', 'order' => 'DESC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		$fnc_ed_docs = array();
		$fnc_ed_videos = array();
		foreach ( $fnc_ed_pubs as $fnc_ep ) {
			$fnc_ep_type  = get_post_meta( $fnc_ep->ID, '_fnc_publication_type', true );
			$fnc_ep_media = (string) get_post_meta( $fnc_ep->ID, '_fnc_publication_media_url', true );
			if ( in_array( $fnc_ep_type, array( 'video', 'interview' ), true ) && '' !== $fnc_ep_media ) {
				$fnc_ed_videos[] = $fnc_ep;
			} else {
				$fnc_ed_docs[] = $fnc_ep;
			}
		}

		$fnc_ed_niv_labels  = function_exists( 'fnc_content_model_partner_niveaux' ) ? fnc_content_model_partner_niveaux() : array();
		$fnc_ed_partners_by = array();
		foreach ( get_posts( array( 'post_type' => 'fnc_partenaire', 'posts_per_page' => -1, 'orderby' => 'meta_value_num title', 'meta_key' => '_fnc_partenaire_sort_index', 'order' => 'ASC' ) ) as $fnc_pt ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			$fnc_pt_flat = get_post_meta( $fnc_pt->ID, '_fnc_partenaire_editions', true );
			$fnc_pt_flat = is_array( $fnc_pt_flat ) ? array_map( 'intval', $fnc_pt_flat ) : array();
			$fnc_pt_part = get_post_meta( $fnc_pt->ID, '_fnc_partenaire_participations', true );
			$fnc_pt_part = is_array( $fnc_pt_part ) ? $fnc_pt_part : array();
			$fnc_pt_niv  = null;
			foreach ( $fnc_pt_part as $fnc_pp ) {
				if ( (int) ( $fnc_pp['edition'] ?? 0 ) === $fnc_ed_id ) {
					$fnc_pt_niv = (string) ( $fnc_pp['niveau'] ?? '' );
					break;
				}
			}
			if ( null === $fnc_pt_niv && in_array( $fnc_ed_id, $fnc_pt_flat, true ) ) {
				$fnc_pt_niv = '';
			}
			if ( null === $fnc_pt_niv ) {
				continue;
			}
			$fnc_key = ( '' !== $fnc_pt_niv && isset( $fnc_ed_niv_labels[ $fnc_pt_niv ] ) ) ? $fnc_pt_niv : '__tbc';
			$fnc_ed_partners_by[ $fnc_key ][] = $fnc_pt;
		}
		?>

		<?php if ( 'current' !== $fnc_ed_status ) : // #3 : rétrospective JAMAIS pour l'édition en cours (sa fiche = hub/agenda ; parité editions/[slug]:83). ?>
		<?php if ( ! empty( $fnc_ed_figures ) ) : ?>
			<section class="section linen">
				<span class="eyebrow"><?php esc_html_e( 'Rétrospective', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Chiffres clés', 'fnc-wordpress-theme' ); ?></h2>
				<div class="retro-stats">
					<?php foreach ( $fnc_ed_figures as $fnc_fig ) : ?>
						<?php if ( ! empty( $fnc_fig['value'] ) && ! empty( $fnc_fig['label'] ) ) : // #4a : value ET label requis. ?>
							<article class="retro-stat">
								<b><?php echo esc_html( $fnc_fig['value'] ); ?></b>
								<span><?php echo esc_html( $fnc_fig['label'] ); ?></span>
							</article>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $fnc_ed_review ) : ?>
			<section class="section">
				<span class="eyebrow"><?php esc_html_e( 'Rétrospective', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Bilan', 'fnc-wordpress-theme' ); ?></h2>
				<div class="prose-legal" style="margin-top:24px;max-width:48ch;"><?php echo wp_kses_post( wpautop( $fnc_ed_review ) ); ?></div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $fnc_ed_partners_by ) ) : ?>
			<section class="section linen">
				<span class="eyebrow"><?php esc_html_e( 'Rétrospective', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Partenaires de l’édition', 'fnc-wordpress-theme' ); ?></h2>
				<?php foreach ( array( 'principal', 'majeur', 'officiel', 'contributeur', '__tbc' ) as $fnc_niv_key ) : ?>
					<?php if ( empty( $fnc_ed_partners_by[ $fnc_niv_key ] ) ) { continue; } ?>
					<?php $fnc_niv_label = ( '__tbc' === $fnc_niv_key ) ? __( 'À confirmer', 'fnc-wordpress-theme' ) : $fnc_ed_niv_labels[ $fnc_niv_key ]; ?>
					<h3 style="margin:28px 0 14px;color:var(--navy);"><?php echo esc_html( $fnc_niv_label ); ?></h3>
					<div class="grid grid-3">
						<?php foreach ( $fnc_ed_partners_by[ $fnc_niv_key ] as $fnc_pt ) : ?>
							<?php
							$fnc_pt_site = (string) get_post_meta( $fnc_pt->ID, '_fnc_partenaire_site', true );
							$fnc_pt_desc = has_excerpt( $fnc_pt ) ? get_the_excerpt( $fnc_pt ) : wp_trim_words( wp_strip_all_tags( (string) $fnc_pt->post_content ), 28 );
							?>
							<article class="card fnc-card">
								<h4 style="font-family:var(--serif);color:var(--navy-deep);font-size:1.2rem;line-height:1.25;"><?php echo esc_html( get_the_title( $fnc_pt ) ); ?></h4>
								<?php if ( $fnc_pt_desc ) : ?><p style="color:var(--texte-sec);margin-top:10px;font-size:.95rem;"><?php echo esc_html( $fnc_pt_desc ); ?></p><?php endif; ?>
								<?php if ( $fnc_pt_site ) : ?><a class="link-more" href="<?php echo esc_url( $fnc_pt_site ); ?>" target="_blank" rel="noopener noreferrer" style="margin-top:12px;display:inline-block;"><?php esc_html_e( 'Voir le site', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a><?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $fnc_ed_docs ) ) : ?>
			<section class="section">
				<span class="eyebrow"><?php esc_html_e( 'Rétrospective', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Ressources', 'fnc-wordpress-theme' ); ?></h2>
				<div class="retro-docs">
					<?php foreach ( $fnc_ed_docs as $fnc_ep ) : ?>
						<?php
						$fnc_ep_type = function_exists( 'fnc_publication_type_label' ) ? fnc_publication_type_label( get_post_meta( $fnc_ep->ID, '_fnc_publication_type', true ) ) : '';
						$fnc_ep_desc = has_excerpt( $fnc_ep ) ? get_the_excerpt( $fnc_ep ) : wp_trim_words( wp_strip_all_tags( (string) $fnc_ep->post_content ), 32 );
						?>
						<article class="retro-doc">
							<?php if ( $fnc_ep_type ) : ?><p class="retro-doc-type"><?php echo esc_html( $fnc_ep_type ); ?></p><?php endif; ?>
							<h3><a href="<?php echo esc_url( get_permalink( $fnc_ep ) ); ?>"><?php echo esc_html( get_the_title( $fnc_ep ) ); ?></a></h3>
							<?php if ( $fnc_ep_desc ) : ?><p><?php echo esc_html( $fnc_ep_desc ); ?></p><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $fnc_ed_videos ) ) : ?>
			<section class="section linen">
				<span class="eyebrow"><?php esc_html_e( 'Rétrospective', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Vidéos', 'fnc-wordpress-theme' ); ?></h2>
				<div class="retro-gallery" style="margin-top:28px;">
					<?php foreach ( $fnc_ed_videos as $fnc_ep ) : ?>
						<?php
						$fnc_ep_media  = (string) get_post_meta( $fnc_ep->ID, '_fnc_publication_media_url', true );
						$fnc_ep_poster = has_post_thumbnail( $fnc_ep->ID ) ? get_the_post_thumbnail_url( $fnc_ep->ID, 'fnc-cover' ) : '';
						$fnc_ep_facade = function_exists( 'fnc_video_facade' ) ? fnc_video_facade( $fnc_ep_media, $fnc_ep_poster ) : '';
						?>
						<figure>
							<?php if ( $fnc_ep_facade ) : ?>
								<?php echo $fnc_ep_facade; // phpcs:ignore WordPress.Security.EscapeOutput -- markup échappé dans le helper. ?>
							<?php else : ?>
								<a class="link-more" href="<?php echo esc_url( $fnc_ep_media ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Regarder', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
							<?php endif; ?>
							<figcaption style="margin-top:8px;font-size:.9rem;color:var(--texte-sec);"><?php echo esc_html( get_the_title( $fnc_ep ) ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $fnc_ed_gallery ) ) : ?>
			<section class="section">
				<span class="eyebrow"><?php esc_html_e( 'Rétrospective', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Galerie', 'fnc-wordpress-theme' ); ?></h2>
				<div class="retro-gallery" style="margin-top:28px;">
					<?php foreach ( $fnc_ed_gallery as $fnc_g_item ) : ?>
						<?php
						// #4b : chaque média exige un texte alternatif (a11y) — sans alt, écarté.
						$fnc_g_url = is_array( $fnc_g_item ) ? (string) ( $fnc_g_item['url'] ?? '' ) : '';
						$fnc_g_alt = is_array( $fnc_g_item ) ? (string) ( $fnc_g_item['alt'] ?? '' ) : '';
						if ( '' === $fnc_g_url || '' === $fnc_g_alt ) {
							continue;
						}
						?>
						<figure><?php echo fnc_theme_image( $fnc_g_url, $fnc_g_alt ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup échappé dans le helper. ?></figure>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
		<?php endif; // #3 fin rétrospective (affichée uniquement pour une édition NON en cours). ?>

		<?php
		// Informations pratiques (mutualisées) — masquées si non renseignées.
		$fnc_ed_pratique = fnc_render_practical_info( $fnc_ed_id );
		if ( '' !== trim( $fnc_ed_pratique ) ) :
			?>
			<section class="section linen">
				<span class="eyebrow"><?php esc_html_e( 'Sur place', 'fnc-wordpress-theme' ); ?></span>
				<h2><?php esc_html_e( 'Informations pratiques.', 'fnc-wordpress-theme' ); ?></h2>
				<div class="practical-list" style="margin-top:24px;">
					<?php echo $fnc_ed_pratique; // phpcs:ignore WordPress.Security.EscapeOutput -- markup produit par les renderers de blocs, deja echappe. ?>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();
