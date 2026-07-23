<?php
/**
 * Gabarit de page — "Édition en cours".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/edition-en-cours), suite a l'amendement de la
 * Decision 1 de l'ADR-007 : hero + statistiques + apercu programme +
 * apercu intervenants + bloc inscription. Contenu de demonstration
 * reste fictif — jamais les vraies identites de responsables publics
 * visibles sur le site officiel.
 *
 * Page DYNAMIQUE (etape 4) : recupere l'edition marquee "active" via le
 * plugin (_fnc_edition_active), avec repli sur l'edition la plus
 * recente si aucune n'est marquee. Statistiques et apercus calcules a
 * partir des vraies relations session -> edition / session -> intervenants
 * deja construites (archive-fnc_session.php, archive-fnc_intervenant.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fnc_active_edition = get_posts(
	array(
		'post_type'      => 'fnc_edition',
		'posts_per_page' => 1,
		'meta_key'       => '_fnc_edition_active',
		'meta_value'     => '1',
	)
);

if ( empty( $fnc_active_edition ) ) {
	$fnc_active_edition = get_posts(
		array(
			'post_type'      => 'fnc_edition',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

$fnc_edition = ! empty( $fnc_active_edition ) ? $fnc_active_edition[0] : null;

$fnc_sessions       = array();
$fnc_speaker_ids    = array();
$fnc_jours          = array();
$fnc_sessions_by_day = array();

if ( $fnc_edition ) {
	$fnc_sessions = get_posts(
		array(
			'post_type'      => 'fnc_session',
			'posts_per_page' => -1,
			'meta_key'       => '_fnc_session_edition',
			'meta_value'     => $fnc_edition->ID,
			'orderby'        => 'date',
			'order'          => 'ASC',
		)
	);

	foreach ( $fnc_sessions as $fnc_session ) {
		$fnc_session_speakers = get_post_meta( $fnc_session->ID, '_fnc_session_speakers', true );
		if ( is_array( $fnc_session_speakers ) ) {
			$fnc_speaker_ids = array_merge( $fnc_speaker_ids, $fnc_session_speakers );
		}
		$fnc_jour = get_post_meta( $fnc_session->ID, '_fnc_session_jour', true );
		if ( $fnc_jour ) {
			$fnc_jours[ $fnc_jour ] = true;
		}
		$fnc_jour_key = $fnc_jour ? $fnc_jour : __( 'Jour à confirmer', 'fnc-wordpress-theme' );
		if ( ! isset( $fnc_sessions_by_day[ $fnc_jour_key ] ) ) {
			$fnc_sessions_by_day[ $fnc_jour_key ] = array();
		}
		$fnc_sessions_by_day[ $fnc_jour_key ][] = $fnc_session;
	}
	$fnc_speaker_ids = array_unique( $fnc_speaker_ids );
}

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Édition en cours', 'fnc-wordpress-theme' ),
		'title'      => $fnc_edition ? get_the_title( $fnc_edition ) : __( 'Édition à confirmer', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Trois jours de travail collectif. Voici tout ce qu’il faut pour suivre l’édition en préparation.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-territoire-brazzaville.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Édition en cours', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<?php if ( ! $fnc_edition ) : ?>
		<section class="section">
			<div class="container">
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune édition en cours', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Créez une édition et marquez-la « en cours » dans son panneau d’administration.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			</div>
		</section>
	<?php else : ?>
		<?php
		$fnc_edition_theme    = get_post_meta( $fnc_edition->ID, '_fnc_edition_theme', true );
		$fnc_edition_start    = get_post_meta( $fnc_edition->ID, '_fnc_edition_start_date', true );
		$fnc_edition_end      = get_post_meta( $fnc_edition->ID, '_fnc_edition_end_date', true );
		$fnc_edition_location = get_post_meta( $fnc_edition->ID, '_fnc_edition_location', true );
		$fnc_edition_dates    = '';
		if ( $fnc_edition_start ) {
			$fnc_edition_dates = date_i18n( 'j F Y', strtotime( $fnc_edition_start ) );
			if ( $fnc_edition_end && $fnc_edition_end !== $fnc_edition_start ) {
				$fnc_edition_dates .= ' – ' . date_i18n( 'j F Y', strtotime( $fnc_edition_end ) );
			}
		}
		?>
		<?php if ( $fnc_edition_theme || $fnc_edition_dates || $fnc_edition_location ) : ?>
			<section class="section" style="padding-bottom:0;">
				<div class="container">
					<?php if ( $fnc_edition_theme ) : ?>
						<p class="frise-theme" style="font-size:1.15rem;"><?php echo esc_html( $fnc_edition_theme ); ?></p>
					<?php endif; ?>
					<?php if ( $fnc_edition_dates || $fnc_edition_location ) : ?>
						<p class="frise-meta">
							<?php if ( $fnc_edition_dates ) : ?><b><?php echo esc_html( $fnc_edition_dates ); ?></b><?php endif; ?>
							<?php if ( $fnc_edition_dates && $fnc_edition_location ) : ?> · <?php endif; ?>
							<?php echo esc_html( $fnc_edition_location ); ?>
						</p>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="section linen">
			<div class="container">
				<div class="metric-strip" style="background:var(--linen);border-color:var(--border);">
					<div class="metric" style="background:#fff;">
						<b style="color:var(--navy);"><?php echo esc_html( count( $fnc_sessions ) ); ?></b>
						<span style="color:var(--texte-tert);"><?php esc_html_e( 'sessions', 'fnc-wordpress-theme' ); ?></span>
					</div>
					<div class="metric" style="background:#fff;">
						<b style="color:var(--navy);"><?php echo esc_html( count( $fnc_speaker_ids ) ); ?></b>
						<span style="color:var(--texte-tert);"><?php esc_html_e( 'intervenants', 'fnc-wordpress-theme' ); ?></span>
					</div>
					<div class="metric" style="background:#fff;">
						<b style="color:var(--navy);"><?php echo esc_html( count( $fnc_jours ) ); ?></b>
						<span style="color:var(--texte-tert);"><?php esc_html_e( 'jours', 'fnc-wordpress-theme' ); ?></span>
					</div>
				</div>
			</div>
		</section>

		<section class="section">
			<div class="container">
				<div class="section-head">
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Le programme', 'fnc-wordpress-theme' ); ?></p>
						<h2><?php esc_html_e( 'Un aperçu des prochaines sessions.', 'fnc-wordpress-theme' ); ?></h2>
					</div>
				</div>
				<?php if ( ! empty( $fnc_sessions_by_day ) ) : ?>
					<?php
					$fnc_first_day     = array_key_first( $fnc_sessions_by_day );
					$fnc_session_types = fnc_content_model_session_types();
					?>
					<div class="agenda">
						<?php foreach ( array_slice( $fnc_sessions_by_day[ $fnc_first_day ], 0, 4 ) as $fnc_session ) : ?>
							<?php
							$fnc_type     = get_post_meta( $fnc_session->ID, '_fnc_session_type', true );
							$fnc_no_badge = in_array( $fnc_type, array( 'pause', 'logistique' ), true );
							?>
							<a class="agenda-row" href="<?php echo esc_url( get_permalink( $fnc_session ) ); ?>">
								<span class="time"><?php echo esc_html( get_post_meta( $fnc_session->ID, '_fnc_session_time', true ) ?: '—' ); ?></span>
								<span>
									<strong><?php echo esc_html( get_the_title( $fnc_session ) ); ?></strong>
									<?php if ( $fnc_type && ! $fnc_no_badge && isset( $fnc_session_types[ $fnc_type ] ) ) : ?>
										<?php fnc_render_badge( $fnc_session_types[ $fnc_type ] ); ?>
									<?php endif; ?>
								</span>
								<span class="room"><?php echo esc_html( get_post_meta( $fnc_session->ID, '_fnc_session_room', true ) ?: __( 'Salle à confirmer', 'fnc-wordpress-theme' ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="empty" role="status">
						<h3><?php esc_html_e( 'Aucune session publiée', 'fnc-wordpress-theme' ); ?></h3>
						<p><?php esc_html_e( 'Les données finales proviennent du CMS.', 'fnc-wordpress-theme' ); ?></p>
					</div>
				<?php endif; ?>
				<a class="link-more" href="<?php echo esc_url( get_post_type_archive_link( 'fnc_session' ) ); ?>" style="margin-top:24px;"><?php esc_html_e( 'Voir le programme complet', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>

		<section class="section linen">
			<div class="container">
				<div class="section-head">
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Les intervenants', 'fnc-wordpress-theme' ); ?></p>
						<h2><?php esc_html_e( 'Décideurs, experts et acteurs de la société civile.', 'fnc-wordpress-theme' ); ?></h2>
					</div>
				</div>
				<?php if ( ! empty( $fnc_speaker_ids ) ) : ?>
					<div class="grid grid-3">
						<?php foreach ( array_slice( $fnc_speaker_ids, 0, 6 ) as $fnc_speaker_id ) : ?>
							<article class="card">
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_speaker_id ) ); ?>"><?php echo esc_html( get_the_title( $fnc_speaker_id ) ); ?></a></h3>
								<?php
								$fnc_excerpt = get_the_excerpt( $fnc_speaker_id );
								if ( $fnc_excerpt ) :
									?>
									<p><?php echo esc_html( $fnc_excerpt ); ?></p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="empty" role="status">
						<h3><?php esc_html_e( 'Aucun intervenant rattaché', 'fnc-wordpress-theme' ); ?></h3>
						<p><?php esc_html_e( 'Rattachez des intervenants aux sessions de cette édition.', 'fnc-wordpress-theme' ); ?></p>
					</div>
				<?php endif; ?>
				<a class="link-more" href="<?php echo esc_url( get_post_type_archive_link( 'fnc_intervenant' ) ); ?>" style="margin-top:24px;"><?php esc_html_e( 'Voir tous les intervenants', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</section>

		<?php
		// Informations pratiques de l'edition (Lot 4) : agregat rattache a
		// l'edition, masque tant qu'aucune rubrique n'est renseignee.
		$fnc_ec_pratique = fnc_render_practical_info( $fnc_edition );
		if ( '' !== trim( $fnc_ec_pratique ) ) :
			?>
			<section class="section">
				<div class="container">
					<div class="section-head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Sur place', 'fnc-wordpress-theme' ); ?></p>
							<h2><?php esc_html_e( 'Informations pratiques.', 'fnc-wordpress-theme' ); ?></h2>
						</div>
					</div>
					<div class="pract-grid">
						<?php echo $fnc_ec_pratique; // phpcs:ignore WordPress.Security.EscapeOutput -- markup produit par les renderers de blocs, deja echappe. ?>
					</div>
					<a class="link-more" href="<?php echo esc_url( fnc_page_url( 'informations-pratiques' ) ); ?>" style="margin-top:24px;"><?php esc_html_e( 'Toutes les informations pratiques', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
				</div>
			</section>
		<?php endif; ?>

		<section class="section">
			<div class="container reading">
				<h2><?php esc_html_e( 'Inscription', 'fnc-wordpress-theme' ); ?></h2>
				<p><?php esc_html_e( 'L’ouverture des inscriptions sera annoncée prochainement.', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
			</div>
		</section>
	<?php endif; ?>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
