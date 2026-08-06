<?php
/**
 * Forum Numérique Congo — page d’accueil (enchaînement des sections éditoriales).
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

$fnc_img_dir = get_template_directory_uri() . '/assets/images/';

// Edition en cours : sert au programme (M5) et au compte a rebours (M8).
$fnc_home_edition = get_posts(
	array(
		'post_type'      => 'fnc_edition',
		'posts_per_page' => 1,
		'meta_key'       => '_fnc_edition_active',
		'meta_value'     => '1',
	)
);
$fnc_home_edition = ! empty( $fnc_home_edition ) ? $fnc_home_edition[0] : null;
?>

<main id="main">
	<!-- M1 — OPENING EXPERIENCE -->
	<section class="moment" id="m1" aria-labelledby="m1-title">
		<?php fnc_render_home_hero_media(); ?>
		<div class="overlay" aria-hidden="true"></div>
		<?php // Calque d'ambiance du hero : nappe de points en vague dessinee sur canvas (variante « dots » du site du Forum, HeroBackdrop). Anime par assets/js/main.js, statique sous prefers-reduced-motion. ?>
		<div class="hero-backdrop" aria-hidden="true"><canvas class="hb-canvas"></canvas></div>
		<div class="hero-inner">
			<div class="kicker">
				<?php if ( fnc_home_setting( 'm1_place', 'Brazzaville · République du Congo' ) ) : ?>
					<span class="place"><?php echo esc_html( fnc_home_setting( 'm1_place', 'Brazzaville · République du Congo' ) ); ?></span>
				<?php endif; ?>
				<span class="place">
					<?php $fnc_m1_dates = fnc_home_setting( 'm1_dates' ) ?: fnc_edition_dates_label(); ?>
					<?php if ( $fnc_m1_dates ) : ?>
						<?php echo esc_html( $fnc_m1_dates ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Dates', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
					<?php endif; ?>
				</span>
			</div>
			<h1 id="m1-title"><?php echo esc_html( fnc_home_setting( 'm1_title_line1', 'Forum' ) ); ?><br><span class="thin"><?php echo esc_html( fnc_home_setting( 'm1_title_line2', __( 'Numérique', 'fnc-wordpress-theme' ) ) ); ?></span> <?php echo esc_html( fnc_home_setting( 'm1_title_line3', 'Congo' ) ); ?></h1>
			<p class="tagline"><?php echo esc_html( fnc_home_setting( 'm1_tagline', __( '« L’espace où l’Afrique centrale décide de son avenir numérique. »', 'fnc-wordpress-theme' ) ) ); ?></p>
			<div class="actions">
				<a class="btn btn-white" href="<?php echo esc_url( fnc_home_setting( 'm1_cta_primary_url', '#m5' ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm1_cta_primary', __( 'Voir le programme', 'fnc-wordpress-theme' ) ) ); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( fnc_home_setting( 'm1_cta_secondary_url', '#m8' ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm1_cta_secondary', __( 'Réserver votre place', 'fnc-wordpress-theme' ) ) ); ?></a>
			</div>
		</div>
		<div class="scroll-hint" aria-hidden="true"><span class="dot"></span><?php echo esc_html( fnc_home_setting( 'm1_scroll', __( 'Défiler', 'fnc-wordpress-theme' ) ) ); ?></div>
	</section>

	<!-- M2 — CE QUE NOUS SOMMES -->
	<section class="moment" id="m2" aria-labelledby="m2-title">
		<div class="grid">
			<div>
				<span class="eyebrow"><?php echo esc_html( fnc_home_setting( 'm2_eyebrow', __( 'Ce que nous sommes', 'fnc-wordpress-theme' ) ) ); ?></span>
				<h2 class="lines" id="m2-title"><?php echo esc_html( fnc_home_setting( 'm2_line1', __( 'Pas une conférence.', 'fnc-wordpress-theme' ) ) ); ?><br><?php echo esc_html( fnc_home_setting( 'm2_line2', __( 'Un espace de décision.', 'fnc-wordpress-theme' ) ) ); ?><br><span class="muted"><?php echo esc_html( fnc_home_setting( 'm2_line3', __( 'Une institution permanente.', 'fnc-wordpress-theme' ) ) ); ?></span></h2>
				<a class="link-more" href="<?php echo esc_url( fnc_home_setting( 'm2_link_url', fnc_page_url( 'le-forum' ) ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm2_link', __( 'Découvrir le Forum', 'fnc-wordpress-theme' ) ) ); ?> <span class="arrow">→</span></a>
			</div>
			<figure><img src="<?php echo esc_url( fnc_home_media_url( 'm2_image', $fnc_img_dir . 'la-salle.png' ) ); ?>" alt="<?php esc_attr_e( 'Salle plénière du Forum Numérique Congo avant l’ouverture', 'fnc-wordpress-theme' ); ?>" /></figure>
		</div>
	</section>

	<!-- M3 — LES VOIX / INTERVENANTS -->
	<?php
	// Voix : participants de l'édition active, en ordre protocolaire (module Données
	// du site). Sinon état « à confirmer ». Le carrousel monte toutes les voix ; le
	// script assets/js/main.js gère le défilement, les flèches, les pastilles et
	// lecture/pause. Droit à l'image respecté : portrait seulement si acquis, sinon
	// monogramme (initiales).
	// Plafond du carrousel : défaut 10, borné 4–12 (comme le champ CMS m3VoicesCount
	// du site du Forum). fnc_home_setting reste administrable dans cet intervalle.
	$fnc_m3_count  = max( 4, min( 12, (int) fnc_home_setting( 'm3_count', 10 ) ) );
	$fnc_voice_ids = function_exists( 'fnc_home_voices' )
		? fnc_home_voices( $fnc_m3_count )
		: array();
	$fnc_voices = array_filter( array_map( 'get_post', $fnc_voice_ids ) );

	// Eyebrow DYNAMIQUE (comme le site du Forum #m3) : « Les voix · N intervenants,
	// N pays » — décomptes de l'édition en cours. On garde le libellé de base
	// administrable (m3_eyebrow) et on lui adjoint les décomptes ; les fragments
	// sont traduisibles (_n gère le pluriel FR/EN : speakers/countries). Si aucun
	// participant n'est encore publié, on n'affiche que le libellé.
	$fnc_m3_label   = fnc_home_setting( 'm3_eyebrow', __( 'Les voix', 'fnc-wordpress-theme' ) );
	$fnc_m3_eyebrow = $fnc_m3_label;
	if ( function_exists( 'fnc_edition_participants' ) && function_exists( 'fnc_edition_countries' ) ) {
		$fnc_n_part = count( fnc_edition_participants() );
		$fnc_n_pays = count( fnc_edition_countries() );
		if ( $fnc_n_part > 0 ) {
			/* translators: %d: nombre d'intervenants. */
			$fnc_part_txt   = sprintf( _n( '%d intervenant', '%d intervenants', $fnc_n_part, 'fnc-wordpress-theme' ), $fnc_n_part );
			/* translators: %d: nombre de pays. */
			$fnc_pays_txt   = sprintf( _n( '%d pays', '%d pays', $fnc_n_pays, 'fnc-wordpress-theme' ), $fnc_n_pays );
			/* translators: 1: libellé (« Les voix »), 2: « N intervenants », 3: « N pays ». */
			$fnc_m3_eyebrow = sprintf( _x( '%1$s · %2$s, %3$s', 'eyebrow carrousel des voix', 'fnc-wordpress-theme' ), $fnc_m3_label, $fnc_part_txt, $fnc_pays_txt );
		}
	}
	$fnc_intervenants_url = get_post_type_archive_link( 'fnc_intervenant' );

	$fnc_initials = static function ( $name ) {
		$ini = '';
		foreach ( preg_split( '/\s+/u', trim( wp_strip_all_tags( (string) $name ) ) ) as $word ) {
			if ( '' !== $word && preg_match( '/\p{L}/u', $word ) ) {
				$ini .= function_exists( 'mb_strtoupper' ) ? mb_strtoupper( mb_substr( $word, 0, 1 ) ) : strtoupper( substr( $word, 0, 1 ) );
			}
			if ( strlen( $ini ) >= 2 ) {
				break;
			}
		}
		return '' !== $ini ? $ini : '·';
	};
	?>
	<section id="m3" aria-label="<?php echo esc_attr( $fnc_m3_eyebrow ); ?>">
		<div class="voix-grid"<?php echo $fnc_voices ? ' data-voices' : ''; ?>>
			<div class="portrait voice-portrait">
				<?php
				if ( $fnc_voices ) :
					foreach ( array_values( $fnc_voices ) as $fnc_vi => $fnc_v ) :
						$fnc_on   = 0 === $fnc_vi ? ' on' : '';
						$fnc_attr = array( 'class' => 'voice-slide' . $fnc_on, 'alt' => fnc_speaker_display_name( $fnc_v->ID ) );
						if ( 0 !== $fnc_vi ) {
							$fnc_attr['aria-hidden'] = 'true';
						}
						$fnc_img = function_exists( 'fnc_speaker_portrait' ) ? fnc_speaker_portrait( $fnc_v->ID, 'large', $fnc_attr ) : '';
						if ( $fnc_img ) :
							echo $fnc_img; // phpcs:ignore WordPress.Security.EscapeOutput -- markup <img> genere par WordPress.
						else :
							?>
							<div class="voice-ph voice-slide<?php echo esc_attr( $fnc_on ); ?>" aria-hidden="true">
								<span class="voice-mono"><?php echo esc_html( $fnc_initials( get_the_title( $fnc_v ) ) ); ?></span>
								<span class="voice-phlabel"><?php esc_html_e( 'Photo à venir', 'fnc-wordpress-theme' ); ?></span>
							</div>
							<?php
						endif;
					endforeach;
				else :
					?>
					<img class="media-cover" src="<?php echo esc_url( $fnc_img_dir . 'le-portrait.png' ); ?>" alt="" aria-hidden="true" />
				<?php endif; ?>
			</div>
			<div class="identity">
				<span class="eyebrow" style="color:var(--jaune)"><?php echo esc_html( $fnc_m3_eyebrow ); ?></span>
				<?php if ( $fnc_voices ) : ?>
					<div class="voice-live" aria-live="polite" aria-atomic="true">
						<?php foreach ( array_values( $fnc_voices ) as $fnc_vi => $fnc_v ) : ?>
							<div class="voice-fade voice-identity<?php echo 0 === $fnc_vi ? ' on' : ''; ?>"<?php echo 0 === $fnc_vi ? '' : ' hidden'; ?> data-voice-identity>
								<p class="name"><?php echo esc_html( fnc_speaker_display_name( $fnc_v->ID ) ); ?></p>
								<div class="role"><?php echo esc_html( get_post_meta( $fnc_v->ID, '_fnc_speaker_role', true ) ); ?></div>
								<div class="org">
									<?php
									$fnc_v_org  = (string) get_post_meta( $fnc_v->ID, '_fnc_speaker_org', true );
									$fnc_v_pays = (string) get_post_meta( $fnc_v->ID, '_fnc_speaker_country', true );
									echo esc_html( trim( implode( ' · ', array_filter( array( $fnc_v_org, $fnc_v_pays ) ) ) ) );
									?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="voice-controls">
						<button type="button" class="voice-arrow" data-voice-prev aria-label="<?php esc_attr_e( 'Précédent', 'fnc-wordpress-theme' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 6l-6 6 6 6" /></svg>
						</button>
						<div class="voice-dots" role="group" aria-label="<?php echo esc_attr( $fnc_m3_eyebrow ); ?>">
							<?php foreach ( array_values( $fnc_voices ) as $fnc_vi => $fnc_v ) : ?>
								<button type="button" data-voice-dot="<?php echo (int) $fnc_vi; ?>" aria-label="<?php echo esc_attr( fnc_speaker_display_name( $fnc_v->ID ) ); ?>"<?php echo 0 === $fnc_vi ? ' class="on" aria-current="true"' : ''; ?>></button>
							<?php endforeach; ?>
						</div>
						<button type="button" class="voice-arrow" data-voice-next aria-label="<?php esc_attr_e( 'Suivant', 'fnc-wordpress-theme' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
						</button>
						<button type="button" class="voice-play" data-voice-play aria-pressed="false" aria-label="<?php esc_attr_e( 'Mettre en pause le défilement', 'fnc-wordpress-theme' ); ?>">
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-voice-icon-pause><rect x="6" y="5" width="4" height="14" rx="1" /><rect x="14" y="5" width="4" height="14" rx="1" /></svg>
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-voice-icon-play hidden><path d="M8 5v14l11-7z" /></svg>
						</button>
					</div>

					<?php if ( $fnc_intervenants_url ) : ?>
						<a class="link-more" href="<?php echo esc_url( $fnc_intervenants_url ); ?>" style="margin-top:26px;color:#fff"><?php esc_html_e( 'Voir tous les intervenants', 'fnc-wordpress-theme' ); ?> <span class="arrow" aria-hidden="true">→</span></a>
					<?php endif; ?>
				<?php else : ?>
					<p class="name"><?php esc_html_e( 'Intervenant·e', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
					<div class="role"><?php esc_html_e( 'Fonction officielle — à valider', 'fnc-wordpress-theme' ); ?></div>
					<div class="org"><?php esc_html_e( 'Institution — à valider', 'fnc-wordpress-theme' ); ?></div>
					<p class="quote"><?php esc_html_e( '« La citation de l’intervenant apparaîtra ici une fois sa participation confirmée. »', 'fnc-wordpress-theme' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- M4 — LE TERRITOIRE -->
	<section class="moment" id="m4" aria-labelledby="m4-title">
		<img class="media-cover" src="<?php echo esc_url( fnc_home_media_url( 'm4_image', $fnc_img_dir . 'le-territoire-brazzaville.png' ) ); ?>" alt="" aria-hidden="true" />
		<div class="overlay" aria-hidden="true"></div>
		<div class="inner">
			<span class="eyebrow"><?php echo esc_html( fnc_home_setting( 'm4_eyebrow', __( 'Le territoire', 'fnc-wordpress-theme' ) ) ); ?></span>
			<h2 class="manifest" id="m4-title"><?php echo esc_html( fnc_home_setting( 'm4_manifest_strong', __( 'L’Afrique centrale décide', 'fnc-wordpress-theme' ) ) ); ?> <span class="light"><?php echo esc_html( fnc_home_setting( 'm4_manifest_light', __( 'de son avenir numérique.', 'fnc-wordpress-theme' ) ) ); ?></span></h2>
			<p class="sub"><?php echo esc_html( fnc_home_setting( 'm4_sub', __( 'Depuis Brazzaville. Depuis le Bassin du Congo. Une parole ancrée, pas importée.', 'fnc-wordpress-theme' ) ) ); ?></p>
		</div>
	</section>

	<!-- M5 — LE PROGRAMME -->
	<?php
	// Sessions de l'edition en cours ; sinon etat « a confirmer ».
	$fnc_home_sessions = array();
	if ( $fnc_home_edition ) {
		$fnc_home_sessions = get_posts(
			array(
				'post_type'      => 'fnc_session',
				'posts_per_page' => 4,
				'meta_key'       => '_fnc_session_edition',
				'meta_value'     => $fnc_home_edition->ID,
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);
	}
	?>
	<section class="moment" id="m5" aria-labelledby="m5-title">
		<div class="grid">
			<figure><img src="<?php echo esc_url( fnc_home_media_url( 'm5_image', $fnc_img_dir . 'le-pupitre.png' ) ); ?>" alt="<?php esc_attr_e( 'Pupitre du Forum Numérique Congo sur la scène plénière', 'fnc-wordpress-theme' ); ?>" /></figure>
			<div>
				<span class="eyebrow"><?php echo esc_html( fnc_home_setting( 'm5_eyebrow', __( 'Programme', 'fnc-wordpress-theme' ) ) ); ?></span>
				<h2 id="m5-title"><?php echo esc_html( fnc_home_setting( 'm5_title', __( 'Trois jours de travail collectif', 'fnc-wordpress-theme' ) ) ); ?></h2>
				<span class="prog-date">
					<?php if ( fnc_home_setting( 'm5_date_label' ) || fnc_edition_dates_label() ) : ?>
						<?php echo esc_html( fnc_home_setting( 'm5_date_label' ) ?: fnc_edition_dates_label() ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Journée d’ouverture · Date', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
					<?php endif; ?>
				</span>
				<div class="rule" aria-hidden="true"></div>
				<div role="list">
					<?php if ( ! empty( $fnc_home_sessions ) ) : ?>
						<?php foreach ( $fnc_home_sessions as $fnc_hs ) : ?>
							<div class="session" role="listitem">
								<span class="time"><?php echo esc_html( get_post_meta( $fnc_hs->ID, '_fnc_session_time', true ) ?: '—' ); ?></span>
								<span class="title"><?php echo esc_html( get_the_title( $fnc_hs ) ); ?></span>
								<span class="room"><?php echo esc_html( (string) get_post_meta( $fnc_hs->ID, '_fnc_session_room', true ) ); ?></span>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="session" role="listitem">
							<span class="time">—</span>
							<span class="title"><?php esc_html_e( 'Programme en préparation', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></span>
							<span class="room"></span>
						</div>
					<?php endif; ?>
				</div>
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_session' ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm5_link', __( 'Programme complet', 'fnc-wordpress-theme' ) ) ); ?> <span class="arrow">→</span></a>
			</div>
		</div>
	</section>

	<!-- M6 — LES PARTENAIRES -->
	<?php
	// Bloc communauté : partenaires publiés ET munis d'un logo (image à la une),
	// triés par ordre éditorial _fnc_partenaire_sort_index (absent -> dernier).
	// Décision MOA : la rangée du haut porte 3 GRANDS logos (2 organisateurs +
	// le sponsor officiel) — écart VOLONTAIRE vs le site du Forum (qui en met 2).
	// Tiers : 3 GRANDS (t1) + jusqu'à 5 PETITS (t2) = 8 logos maximum. Les 3 du
	// haut passent devant via un sort_index bas (GUOT 0, Grinso 1, sponsor 2).
	$fnc_home_partners = get_posts( array(
		'post_type'      => 'fnc_partenaire',
		'posts_per_page' => 12,
		'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => true,
	) );
	usort( $fnc_home_partners, function ( $a, $b ) {
		$sa = get_post_meta( $a->ID, '_fnc_partenaire_sort_index', true );
		$sb = get_post_meta( $b->ID, '_fnc_partenaire_sort_index', true );
		$sa = ( '' === $sa ) ? PHP_INT_MAX : (int) $sa;
		$sb = ( '' === $sb ) ? PHP_INT_MAX : (int) $sb;
		if ( $sa !== $sb ) {
			return $sa - $sb;
		}
		if ( $a->menu_order !== $b->menu_order ) {
			return $a->menu_order - $b->menu_order;
		}
		return strcmp( get_the_title( $a ), get_the_title( $b ) );
	} );
	$fnc_p_t1 = array_slice( $fnc_home_partners, 0, 3 ); // 3 grands
	$fnc_p_t2 = array_slice( $fnc_home_partners, 3, 5 ); // 4 à 5 petits

	/**
	 * Rendu d'une case logo (lien vers la fiche partenaire), logo contenu ; à
	 * défaut de logo, le nom en texte (comme les placeholders du Forum).
	 */
	$fnc_render_partner = static function ( $partner ) {
		printf(
			'<a class="logo-ph" href="%s" aria-label="%s">%s</a>',
			esc_url( get_permalink( $partner ) ),
			esc_attr( get_the_title( $partner ) ),
			has_post_thumbnail( $partner )
				? get_the_post_thumbnail( $partner, 'medium', array( 'alt' => esc_attr( get_the_title( $partner ) ) ) )
				: esc_html( get_the_title( $partner ) )
		);
	};
	?>
	<section class="moment" id="m6" aria-labelledby="m6-title">
		<span class="eyebrow"><?php echo esc_html( fnc_home_setting( 'm6_eyebrow', __( 'La communauté', 'fnc-wordpress-theme' ) ) ); ?></span>
		<h2 id="m6-title"><?php echo esc_html( fnc_home_setting( 'm6_title', __( 'Ils construisent le Forum avec nous', 'fnc-wordpress-theme' ) ) ); ?></h2>
		<div class="rule-c" aria-hidden="true"></div>
		<div class="tiers">
			<?php if ( ! empty( $fnc_p_t1 ) ) : ?>
				<div class="tier t1">
					<?php foreach ( $fnc_p_t1 as $fnc_hp ) { $fnc_render_partner( $fnc_hp ); } ?>
				</div>
				<?php if ( ! empty( $fnc_p_t2 ) ) : ?>
					<div class="tier t2">
						<?php foreach ( $fnc_p_t2 as $fnc_hp ) { $fnc_render_partner( $fnc_hp ); } ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="tier t1">
					<div class="logo-ph"><?php esc_html_e( 'Partenaire · À confirmer', 'fnc-wordpress-theme' ); ?></div>
					<div class="logo-ph"><?php esc_html_e( 'Partenaire · À confirmer', 'fnc-wordpress-theme' ); ?></div>
				</div>
				<div class="tier t2">
					<?php for ( $fnc_i = 0; $fnc_i < 4; $fnc_i++ ) : ?>
						<div class="logo-ph"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></div>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
		<a class="link-more" href="<?php echo esc_url( fnc_page_url( 'contact' ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm6_link', __( 'Devenir partenaire', 'fnc-wordpress-theme' ) ) ); ?> <span class="arrow">→</span></a>
	</section>

	<!-- M7 — LES ARCHIVES -->
	<?php
	$fnc_home_editions = get_posts(
		array(
			'post_type'      => 'fnc_edition',
			'posts_per_page' => 3,
			'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
			'meta_key'       => '_fnc_edition_year',
		)
	);
	?>
	<section class="moment" id="m7" aria-labelledby="m7-title">
		<span class="eyebrow"><?php echo esc_html( fnc_home_setting( 'm7_eyebrow', __( 'Les éditions', 'fnc-wordpress-theme' ) ) ); ?></span>
		<h2 id="m7-title"><?php echo esc_html( fnc_home_setting( 'm7_title', __( 'Une histoire qui se construit, édition après édition', 'fnc-wordpress-theme' ) ) ); ?></h2>
		<div>
			<?php if ( ! empty( $fnc_home_editions ) ) : ?>
				<?php foreach ( $fnc_home_editions as $fnc_he ) : ?>
					<a class="arch" href="<?php echo esc_url( get_permalink( $fnc_he ) ); ?>">
						<span class="year"><?php echo esc_html( get_post_meta( $fnc_he->ID, '_fnc_edition_year', true ) ?: get_the_date( 'Y', $fnc_he ) ); ?></span>
						<span class="ph"><?php echo esc_html( get_the_title( $fnc_he ) ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<?php for ( $fnc_i = 0; $fnc_i < 3; $fnc_i++ ) : ?>
					<div class="arch"><span class="year">—</span><span class="ph"><?php esc_html_e( 'Édition · Archive à confirmer', 'fnc-wordpress-theme' ); ?></span></div>
				<?php endfor; ?>
			<?php endif; ?>
		</div>
		<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_edition' ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm7_link', __( 'Voir toutes les éditions', 'fnc-wordpress-theme' ) ) ); ?> <span class="arrow">→</span></a>
	</section>

	<!-- M8 — LA DECISION FINALE -->
	<?php
	// Compte a rebours reel depuis la date de debut de l'edition en cours.
	// Clampe a 0 quand la date est aujourd'hui/passee (jamais negatif, comme le
	// site Next). Reste null — « — · Date a confirmer » — seulement en l'ABSENCE
	// de date (choix produit : pas de date fictive). La date ISO est aussi
	// exposee en attribut pour un recalcul cote client (anti-cache) dans main.js.
	$fnc_days_left = null;
	$fnc_start_iso = '';
	if ( $fnc_home_edition ) {
		$fnc_start = get_post_meta( $fnc_home_edition->ID, '_fnc_edition_start_date', true );
		if ( $fnc_start ) {
			$fnc_ts = strtotime( $fnc_start );
			if ( $fnc_ts ) {
				$fnc_days_left = max( 0, (int) ceil( ( $fnc_ts - current_time( 'timestamp' ) ) / DAY_IN_SECONDS ) );
				$fnc_start_iso = gmdate( 'Y-m-d', $fnc_ts );
			}
		}
	}

	// Lieu affiche sous le compte : reglage explicite, sinon lieu de l'edition.
	$fnc_m8_venue = (string) fnc_home_setting( 'm8_venue', '' );
	if ( '' === trim( $fnc_m8_venue ) && $fnc_home_edition ) {
		$fnc_m8_venue = (string) get_post_meta( $fnc_home_edition->ID, '_fnc_edition_location', true );
	}
	?>
	<section class="moment" id="m8" aria-labelledby="m8-title">
		<img class="media-cover" src="<?php echo esc_url( fnc_home_media_url( 'm8_image', $fnc_img_dir . 'le-badge.png' ) ); ?>" alt="" aria-hidden="true" />
		<div class="overlay" aria-hidden="true"></div>
		<div class="decision">
			<div class="count-label"><?php echo esc_html( fnc_home_setting( 'm8_label', __( 'La prochaine édition', 'fnc-wordpress-theme' ) ) ); ?></div>
			<h2 id="m8-title">
				<?php echo esc_html( fnc_home_setting( 'm8_title_before', __( 'Le Forum Numérique Congo commence dans', 'fnc-wordpress-theme' ) ) ); ?>
				<span class="num"<?php echo '' !== $fnc_start_iso ? ' data-fnc-countdown="' . esc_attr( $fnc_start_iso ) . '"' : ''; ?>><?php echo null !== $fnc_days_left ? esc_html( $fnc_days_left ) : '—'; ?></span>
				<?php echo esc_html( fnc_home_setting( 'm8_title_after', __( 'jours', 'fnc-wordpress-theme' ) ) ); ?>
				<?php if ( null === $fnc_days_left ) : ?>
					<span class="tbc"><?php esc_html_e( 'Date à confirmer', 'fnc-wordpress-theme' ); ?></span>
				<?php endif; ?>
			</h2>
			<?php if ( '' !== trim( $fnc_m8_venue ) ) : ?>
				<p class="count-label" style="margin-top:16px;margin-bottom:0;opacity:.85;"><?php echo esc_html( $fnc_m8_venue ); ?></p>
			<?php endif; ?>
			<a class="btn btn-red" href="<?php echo esc_url( fnc_home_setting( 'm8_cta_url', fnc_page_url( 'inscription' ) ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm8_cta', __( 'Réserver votre place', 'fnc-wordpress-theme' ) ) ); ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		</div>
		<svg class="pcb" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
			<path class="r" d="M0 40 H420 L470 70 H820 L860 40 H1200"/>
			<path class="y" d="M0 90 H300 L340 60 H700 L740 92 H1200"/>
			<circle class="node" cx="860" cy="40" r="4" fill="#CC2222"/>
			<circle class="node node-y" cx="740" cy="92" r="4" fill="#F5C000"/>
		</svg>
	</section>
</main>

<?php get_footer(); ?>
