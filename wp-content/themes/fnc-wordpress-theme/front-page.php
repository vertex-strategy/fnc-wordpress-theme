<?php
/**
 * Page d'accueil — les 8 "moments" (M1 a M8).
 *
 * Porte docs/mockups/homepage-v2/index.html (forum-numerique-congo), seule
 * page committee et validee au moment du scaffold (ADR-007, Decision 1).
 *
 * Lot 3 : le contenu n'est plus code en dur. Chaque moment lit ses textes,
 * medias et libelles de CTA depuis les reglages « Page d'accueil (M1 -> M8) »
 * du Customizer (voir inc/homepage.php), avec repli sur le contenu de la
 * maquette si rien n'est renseigne. Le storyboard reste FIGE : l'editeur ne
 * peut ni reordonner ni supprimer un moment, conformement au vrai site.
 *
 * Les moments qui presentent des donnees metier (M3 voix, M5 programme,
 * M6 partenaires, M7 archives, M8 compte a rebours) sont alimentes par les
 * vrais contenus du plugin fnc-content-model lorsqu'ils existent, et
 * retombent sinon sur un etat « a confirmer » explicite — jamais sur des
 * donnees inventees presentees comme confirmees.
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
		<div class="hero-inner">
			<div class="kicker">
				<?php if ( fnc_home_setting( 'm1_place', 'Brazzaville · République du Congo' ) ) : ?>
					<span class="place"><?php echo esc_html( fnc_home_setting( 'm1_place', 'Brazzaville · République du Congo' ) ); ?></span>
				<?php endif; ?>
				<span class="place">
					<?php if ( fnc_home_setting( 'm1_dates' ) ) : ?>
						<?php echo esc_html( fnc_home_setting( 'm1_dates' ) ); ?>
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
	// Voix : intervenants reellement publies ; sinon etat « a confirmer ».
	$fnc_voices = get_posts(
		array(
			'post_type'      => 'fnc_intervenant',
			'posts_per_page' => max( 1, (int) fnc_home_setting( 'm3_count', 5 ) ),
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$fnc_voice  = ! empty( $fnc_voices ) ? $fnc_voices[0] : null;
	$fnc_voice_photo = $fnc_voice && has_post_thumbnail( $fnc_voice ) ? get_the_post_thumbnail_url( $fnc_voice, 'large' ) : $fnc_img_dir . 'le-portrait.png';
	?>
	<section id="m3" aria-labelledby="m3-title">
		<div class="voix-grid">
			<div class="portrait"><img src="<?php echo esc_url( $fnc_voice_photo ); ?>" alt="<?php echo esc_attr( $fnc_voice ? fnc_speaker_display_name( $fnc_voice->ID ) : __( 'Portrait d’un participant du Forum Numérique Congo', 'fnc-wordpress-theme' ) ); ?>" /></div>
			<div class="identity">
				<span class="eyebrow" style="color:var(--jaune)"><?php echo esc_html( fnc_home_setting( 'm3_eyebrow', __( 'Les voix', 'fnc-wordpress-theme' ) ) ); ?></span>
				<?php if ( $fnc_voice ) : ?>
					<h2 class="name" id="m3-title"><?php echo esc_html( fnc_speaker_display_name( $fnc_voice->ID ) ); ?></h2>
					<div class="role"><?php echo esc_html( get_post_meta( $fnc_voice->ID, '_fnc_speaker_org', true ) ); ?></div>
					<div class="org"><?php echo esc_html( get_post_meta( $fnc_voice->ID, '_fnc_speaker_country', true ) ); ?></div>
					<?php if ( has_excerpt( $fnc_voice ) ) : ?>
						<p class="quote"><?php echo esc_html( get_the_excerpt( $fnc_voice ) ); ?></p>
					<?php endif; ?>
					<div class="dots" aria-hidden="true">
						<?php foreach ( $fnc_voices as $fnc_vi => $fnc_v ) : ?>
							<span<?php echo 0 === $fnc_vi ? ' class="on"' : ''; ?>></span>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<h2 class="name" id="m3-title"><?php esc_html_e( 'Intervenant·e', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></h2>
					<div class="role"><?php esc_html_e( 'Fonction officielle — à valider', 'fnc-wordpress-theme' ); ?></div>
					<div class="org"><?php esc_html_e( 'Institution — à valider', 'fnc-wordpress-theme' ); ?></div>
					<p class="quote"><?php esc_html_e( '« La citation de l’intervenant apparaîtra ici une fois sa participation confirmée. »', 'fnc-wordpress-theme' ); ?></p>
					<div class="dots" aria-hidden="true"><span class="on"></span><span></span><span></span><span></span><span></span></div>
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
					<?php if ( fnc_home_setting( 'm5_date_label' ) ) : ?>
						<?php echo esc_html( fnc_home_setting( 'm5_date_label' ) ); ?>
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
								<span class="room"><?php echo esc_html( get_post_meta( $fnc_hs->ID, '_fnc_session_room', true ) ?: __( 'Salle à confirmer', 'fnc-wordpress-theme' ) ); ?></span>
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
	$fnc_home_partners = get_posts( array( 'post_type' => 'fnc_partenaire', 'posts_per_page' => 6 ) );
	?>
	<section class="moment" id="m6" aria-labelledby="m6-title">
		<span class="eyebrow"><?php echo esc_html( fnc_home_setting( 'm6_eyebrow', __( 'La communauté', 'fnc-wordpress-theme' ) ) ); ?></span>
		<h2 id="m6-title"><?php echo esc_html( fnc_home_setting( 'm6_title', __( 'Ils construisent le Forum avec nous', 'fnc-wordpress-theme' ) ) ); ?></h2>
		<div class="rule-c" aria-hidden="true"></div>
		<div class="tiers">
			<?php if ( ! empty( $fnc_home_partners ) ) : ?>
				<div class="tier t1">
					<?php foreach ( $fnc_home_partners as $fnc_hp ) : ?>
						<a class="logo-ph" href="<?php echo esc_url( get_permalink( $fnc_hp ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $fnc_hp ) ); ?>">
							<?php
							if ( has_post_thumbnail( $fnc_hp ) ) {
								echo get_the_post_thumbnail( $fnc_hp, 'medium', array( 'alt' => esc_attr( get_the_title( $fnc_hp ) ) ) );
							} else {
								echo esc_html( get_the_title( $fnc_hp ) );
							}
							?>
						</a>
					<?php endforeach; ?>
				</div>
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
		<a class="link-more" href="<?php echo esc_url( fnc_page_url( 'partenaires' ) ); ?>"><?php echo esc_html( fnc_home_setting( 'm6_link', __( 'Devenir partenaire', 'fnc-wordpress-theme' ) ) ); ?> <span class="arrow">→</span></a>
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
	$fnc_days_left = null;
	if ( $fnc_home_edition ) {
		$fnc_start = get_post_meta( $fnc_home_edition->ID, '_fnc_edition_start_date', true );
		if ( $fnc_start ) {
			$fnc_ts = strtotime( $fnc_start );
			if ( $fnc_ts ) {
				$fnc_diff = (int) ceil( ( $fnc_ts - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
				if ( $fnc_diff >= 0 ) {
					$fnc_days_left = $fnc_diff;
				}
			}
		}
	}
	?>
	<section class="moment" id="m8" aria-labelledby="m8-title">
		<img class="media-cover" src="<?php echo esc_url( fnc_home_media_url( 'm8_image', $fnc_img_dir . 'le-badge.png' ) ); ?>" alt="" aria-hidden="true" />
		<div class="overlay" aria-hidden="true"></div>
		<div class="decision">
			<div class="count-label"><?php echo esc_html( fnc_home_setting( 'm8_label', __( 'La prochaine édition', 'fnc-wordpress-theme' ) ) ); ?></div>
			<h2 id="m8-title">
				<?php echo esc_html( fnc_home_setting( 'm8_title_before', __( 'Le Forum Numérique Congo commence dans', 'fnc-wordpress-theme' ) ) ); ?>
				<span class="num"><?php echo null !== $fnc_days_left ? esc_html( $fnc_days_left ) : '—'; ?></span>
				<?php echo esc_html( fnc_home_setting( 'm8_title_after', __( 'jours', 'fnc-wordpress-theme' ) ) ); ?>
				<?php if ( null === $fnc_days_left ) : ?>
					<span class="tbc"><?php esc_html_e( 'Date à confirmer', 'fnc-wordpress-theme' ); ?></span>
				<?php endif; ?>
			</h2>
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
