<?php
/**
 * Forum Numérique Congo — gabarit de la page « Édition en cours ».
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

/*
 * Composition par blocs : des que l'editorial compose cette page avec des
 * blocs FNC, ce gabarit s'efface au profit du contenu compose (editable et
 * reagenceable) ; sinon il conserve son contenu de demonstration (comportement
 * inchange). Meme convention que page.php et page-le-forum.php.
 */
if ( function_exists( 'fnc_page_has_blocks' ) && fnc_page_has_blocks() ) {
	while ( have_posts() ) {
		the_post();
		echo '<main id="main">';
		the_content();
		echo '</main>';
	}
	get_footer();
	return;
}

// Source UNIQUE = le résolveur (status='current', sinon prochaine 'upcoming'),
// aligné avec le reste du site (plus de double lecture _fnc_edition_active).
$fnc_edition_id = function_exists( 'fnc_current_edition_id' ) ? fnc_current_edition_id() : 0;
$fnc_edition    = $fnc_edition_id ? get_post( $fnc_edition_id ) : null;

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

// Aperçus alignés sur le site Next : les PARTICIPANTS (ordre protocolaire
// protocol_order → sort_index, modérateurs inclus) et le nombre de PAYS
// proviennent des mêmes fonctions dérivées que la page /intervenants — et non
// de l'union brute des _fnc_session_speakers (qui exclut les modérateurs et
// ignore l'ordre).
$fnc_participants = ( $fnc_edition && function_exists( 'fnc_edition_participants' ) )
	? fnc_edition_participants( $fnc_edition->ID )
	: $fnc_speaker_ids;
$fnc_countries = ( $fnc_edition && function_exists( 'fnc_edition_countries' ) )
	? fnc_edition_countries( $fnc_edition->ID )
	: array();

$fnc_route_h = fnc_route_hero( 'edition-en-cours' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => ( $fnc_edition && get_post_meta( $fnc_edition->ID, '_fnc_edition_year', true ) )
			/* translators: %s: année de l'édition. */
			? sprintf( __( 'Édition %s du Forum Numérique Congo', 'fnc-wordpress-theme' ), (string) get_post_meta( $fnc_edition->ID, '_fnc_edition_year', true ) )
			: __( 'Édition en cours du Forum Numérique Congo', 'fnc-wordpress-theme' ),
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
		$fnc_edition_dates = fnc_format_date_range( $fnc_edition_start, $fnc_edition_end );
		?>
		<?php if ( $fnc_edition_theme || $fnc_edition_dates || $fnc_edition_location ) : ?>
			<section class="section" style="padding-bottom:0;">
				<div class="container">
					<?php // Édition provisoire : bandeau « programme provisoire » (comme le site Next). ?>
					<p class="prov-banner"><?php esc_html_e( 'Programme provisoire', 'fnc-wordpress-theme' ); ?></p>
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
				<div class="stat-line">
					<div class="stat">
						<b style="color:var(--navy);"><?php echo esc_html( count( $fnc_participants ) ); ?></b>
						<span style="color:var(--texte-tert);"><?php esc_html_e( 'intervenants', 'fnc-wordpress-theme' ); ?></span>
					</div>
					<div class="stat">
						<b style="color:var(--navy);"><?php echo esc_html( count( $fnc_countries ) ); ?></b>
						<span style="color:var(--texte-tert);"><?php esc_html_e( 'pays', 'fnc-wordpress-theme' ); ?></span>
					</div>
					<div class="stat">
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
						<p class="eyebrow" data-fnc-st="edition-en-cours.programme.eyebrow"><?php echo esc_html( fnc_stitle( 'edition-en-cours', 'programme', 'eyebrow' ) ); ?></p>
						<h2 data-fnc-st="edition-en-cours.programme.title"><?php echo esc_html( fnc_stitle( 'edition-en-cours', 'programme', 'title' ) ); ?></h2>
					</div>
				</div>
				<?php
				// Aperçu Jour 1, aligné sur le site Next : on EXCLUT la logistique et
				// les pauses (inscription, déjeuner…), puis on prend les 4 premières
				// sessions « de fond ».
				$fnc_first_day     = $fnc_edition ? array_key_first( $fnc_sessions_by_day ) : null;
				$fnc_session_types = fnc_content_model_session_types();
				$fnc_day1 = ( $fnc_first_day && isset( $fnc_sessions_by_day[ $fnc_first_day ] ) )
					? array_values(
						array_filter(
							$fnc_sessions_by_day[ $fnc_first_day ],
							static function ( $s ) {
								$t = get_post_meta( $s->ID, '_fnc_session_type', true );
								return ! in_array( $t, array( 'pause', 'logistique' ), true );
							}
						)
					)
					: array();
				?>
				<?php if ( ! empty( $fnc_day1 ) ) : ?>
					<?php
					// En-tête de jour : « JOUR 1 · 25 mars 2027 ». La date du jour N se
					// déduit de la date de début de l'édition (+ N-1 jours).
					$fnc_day_num  = (int) $fnc_first_day;
					$fnc_day_date = '';
					if ( $fnc_edition_start ) {
						$fnc_day_ts   = strtotime( $fnc_edition_start . ' +' . max( 0, $fnc_day_num - 1 ) . ' days' );
						$fnc_day_date = $fnc_day_ts ? date_i18n( 'j F Y', $fnc_day_ts ) : '';
					}
					?>
					<p class="eyebrow" style="color:var(--navy);">
						<?php
						/* translators: %d: numéro du jour. */
						echo esc_html( $fnc_day_num ? sprintf( __( 'Jour %d', 'fnc-wordpress-theme' ), $fnc_day_num ) : (string) $fnc_first_day );
						echo $fnc_day_date ? ' · ' . esc_html( $fnc_day_date ) : '';
						?>
					</p>
					<div class="agenda">
						<?php
						foreach ( array_slice( $fnc_day1, 0, 4 ) as $fnc_session ) :
							$fnc_type  = get_post_meta( $fnc_session->ID, '_fnc_session_type', true );
							// Heure de DÉBUT simple (pas la plage), comme le site Next.
							$fnc_start = get_post_meta( $fnc_session->ID, '_fnc_session_start', true );
							$fnc_start = $fnc_start ? $fnc_start : ( get_post_meta( $fnc_session->ID, '_fnc_session_time', true ) ?: '—' );
							// Sous-titre : « Modération : … » sinon le type, puis « · N intervenants ».
							$fnc_mod   = get_post_meta( $fnc_session->ID, '_fnc_session_moderator', true );
							$fnc_spk   = get_post_meta( $fnc_session->ID, '_fnc_session_speakers', true );
							$fnc_nspk  = is_array( $fnc_spk ) ? count( $fnc_spk ) : 0;
							$fnc_sub   = '';
							if ( $fnc_mod && function_exists( 'fnc_speaker_display_name' ) ) {
								/* translators: %s: nom du modérateur. */
								$fnc_sub = sprintf( __( 'Modération : %s', 'fnc-wordpress-theme' ), fnc_speaker_display_name( (int) $fnc_mod ) );
							} elseif ( $fnc_type && isset( $fnc_session_types[ $fnc_type ] ) ) {
								$fnc_sub = $fnc_session_types[ $fnc_type ];
							}
							if ( $fnc_nspk > 0 ) {
								/* translators: %d: nombre d'intervenants. */
								$fnc_cnt = sprintf( _n( '%d intervenant', '%d intervenants', $fnc_nspk, 'fnc-wordpress-theme' ), $fnc_nspk );
								$fnc_sub = $fnc_sub ? $fnc_sub . ' · ' . $fnc_cnt : $fnc_cnt;
							}
							?>
							<a class="agenda-row" href="<?php echo esc_url( get_permalink( $fnc_session ) ); ?>">
								<span class="time"><?php echo esc_html( $fnc_start ); ?></span>
								<span>
									<strong><?php echo esc_html( get_the_title( $fnc_session ) ); ?></strong>
									<?php if ( $fnc_sub ) : ?>
										<span class="meta" style="display:block;color:var(--texte-sec);font-size:.9rem;margin-top:2px;"><?php echo esc_html( $fnc_sub ); ?></span>
									<?php endif; ?>
								</span>
								<span class="room"><?php echo esc_html( get_post_meta( $fnc_session->ID, '_fnc_session_room', true ) ); ?></span>
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
						<p class="eyebrow" data-fnc-st="edition-en-cours.intervenants.eyebrow"><?php echo esc_html( fnc_stitle( 'edition-en-cours', 'intervenants', 'eyebrow' ) ); ?></p>
						<h2 data-fnc-st="edition-en-cours.intervenants.title"><?php echo esc_html( fnc_stitle( 'edition-en-cours', 'intervenants', 'title' ) ); ?></h2>
					</div>
				</div>
				<?php if ( ! empty( $fnc_participants ) ) : ?>
					<div class="spk-grid is-preview">
						<?php foreach ( array_slice( $fnc_participants, 0, 8 ) as $fnc_speaker_id ) : ?>
							<a class="spk" href="<?php echo esc_url( get_permalink( $fnc_speaker_id ) ); ?>">
								<div class="ph">
									<?php
									// la règle du droit à l’image : portrait uniquement si le droit est « obtenu » et non expire.
									$fnc_pv = function_exists( 'fnc_speaker_portrait' )
										? fnc_speaker_portrait( $fnc_speaker_id, 'medium', array( 'alt' => fnc_speaker_display_name( $fnc_speaker_id ) ) )
										: '';
									if ( $fnc_pv ) {
										echo $fnc_pv; // phpcs:ignore WordPress.Security.EscapeOutput -- markup <img> genere par WP/plugin.
									} else {
										printf( '<span class="m" aria-hidden="true">%s</span><span class="l">%s</span>', esc_html( fnc_speaker_initials( $fnc_speaker_id ) ), esc_html__( 'Photo à venir', 'fnc-wordpress-theme' ) );
									}
									?>
								</div>
								<?php
								// Civilité (M./Mme…) devant le nom si renseignée (secondaire).
								$fnc_pv_civ  = (string) get_post_meta( $fnc_speaker_id, '_fnc_speaker_title', true );
								$fnc_pv_name = fnc_speaker_display_name( $fnc_speaker_id );
								// Sous-titre = RÔLE/fonction (comme /intervenants), repli sur l'organisation.
								$fnc_pv_role = get_post_meta( $fnc_speaker_id, '_fnc_speaker_role', true );
								$fnc_pv_role = $fnc_pv_role ? $fnc_pv_role : get_post_meta( $fnc_speaker_id, '_fnc_speaker_org', true );
								$fnc_pv_ctry = get_post_meta( $fnc_speaker_id, '_fnc_speaker_country', true );
								?>
								<div class="n"><?php echo esc_html( trim( ( $fnc_pv_civ ? $fnc_pv_civ . ' ' : '' ) . $fnc_pv_name ) ); ?></div>
								<?php if ( $fnc_pv_role ) : ?><div class="r"><?php echo esc_html( $fnc_pv_role ); ?></div><?php endif; ?>
								<?php if ( $fnc_pv_ctry ) : ?><span class="c"><?php echo esc_html( $fnc_pv_ctry ); ?></span><?php endif; ?>
							</a>
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
		/*
		 * Informations pratiques : sur le site du Forum, elles vivent sur leur page
		 * dediee (/infos-pratiques), pas sur l'edition en cours. La page Edition
		 * se termine donc sur l'appel a l'inscription — les infos pratiques
		 * restent accessibles via la navigation et le pied de page.
		 */
		?>
		<section class="callout">
			<h2 data-fnc-st="edition-en-cours.inscription.title"><?php echo esc_html( fnc_stitle( 'edition-en-cours', 'inscription', 'title' ) ); ?></h2>
			<p><?php esc_html_e( 'L’ouverture des inscriptions sera annoncée prochainement.', 'fnc-wordpress-theme' ); ?></p>
			<?php if ( function_exists( 'fnc_registration_enabled' ) && fnc_registration_enabled() ) : ?>
				<a class="btn btn-red" href="<?php echo esc_url( fnc_page_url( 'inscription' ) ); ?>"><?php esc_html_e( 'S’inscrire', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</a>
			<?php else : ?>
				<span class="btn btn-disabled" aria-disabled="true"><?php esc_html_e( 'Inscriptions à venir', 'fnc-wordpress-theme' ); ?></span>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
