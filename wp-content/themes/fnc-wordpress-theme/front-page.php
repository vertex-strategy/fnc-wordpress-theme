<?php
/**
 * Page d'accueil — les 8 "moments" (M1 a M8).
 *
 * Porte docs/mockups/homepage-v2/index.html (forum-numerique-congo), seule
 * page committee et validee au moment du scaffold (ADR-007, Decision 1).
 * Le contenu reste statique/exemple, non officiel — aucune donnee inventee
 * n'est presentee comme confirmee (marqueurs "A confirmer" conserves a
 * l'identique de la maquette source).
 *
 * Ce gabarit sera branche sur les donnees du plugin fnc-content-model a
 * l'etape 4 du plan de mise en oeuvre de l'ADR-007.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>
	<!-- M1 — OPENING EXPERIENCE -->
	<section class="moment" id="m1" aria-labelledby="m1-title">
		<img class="media-cover" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/les-portes.png' ); ?>" alt="<?php esc_attr_e( 'Portes du Forum Numérique Congo s’ouvrant sur la salle plénière', 'fnc-wordpress-theme' ); ?>" />
		<div class="overlay" aria-hidden="true"></div>
		<div class="hero-inner">
			<div class="kicker">
				<span class="place"><?php esc_html_e( 'Brazzaville · République du Congo', 'fnc-wordpress-theme' ); ?></span>
				<span class="place"><?php esc_html_e( 'Dates', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></span>
			</div>
			<h1 id="m1-title">Forum<br><span class="thin"><?php esc_html_e( 'Numérique', 'fnc-wordpress-theme' ); ?></span> Congo</h1>
			<p class="tagline"><?php esc_html_e( '« L’espace où l’Afrique centrale décide de son avenir numérique. »', 'fnc-wordpress-theme' ); ?></p>
			<div class="actions">
				<a class="btn btn-white" href="#m5"><?php esc_html_e( 'Voir le programme', 'fnc-wordpress-theme' ); ?></a>
				<a class="btn btn-ghost" href="#m8"><?php esc_html_e( 'Demander une accréditation', 'fnc-wordpress-theme' ); ?></a>
			</div>
		</div>
		<div class="scroll-hint" aria-hidden="true"><span class="dot"></span><?php esc_html_e( 'Défiler', 'fnc-wordpress-theme' ); ?></div>
	</section>

	<!-- M2 — CE QUE NOUS SOMMES -->
	<section class="moment" id="m2" aria-labelledby="m2-title">
		<div class="grid">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Ce que nous sommes', 'fnc-wordpress-theme' ); ?></span>
				<h2 class="lines" id="m2-title"><?php esc_html_e( 'Pas une conférence.', 'fnc-wordpress-theme' ); ?><br><?php esc_html_e( 'Un espace de décision.', 'fnc-wordpress-theme' ); ?><br><span class="muted"><?php esc_html_e( 'Une institution permanente.', 'fnc-wordpress-theme' ); ?></span></h2>
				<a class="link-more" href="#"><?php esc_html_e( 'Découvrir le Forum', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
			<figure><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/la-salle.png' ); ?>" alt="<?php esc_attr_e( 'Salle plénière du Forum Numérique Congo avant l’ouverture', 'fnc-wordpress-theme' ); ?>" /></figure>
		</div>
	</section>

	<!-- M3 — LES VOIX / INTERVENANTS -->
	<section id="m3" aria-labelledby="m3-title">
		<div class="voix-grid">
			<div class="portrait"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-portrait.png' ); ?>" alt="<?php esc_attr_e( 'Portrait d’un participant du Forum Numérique Congo', 'fnc-wordpress-theme' ); ?>" /></div>
			<div class="identity">
				<span class="eyebrow" style="color:var(--jaune)"><?php esc_html_e( 'Les voix', 'fnc-wordpress-theme' ); ?></span>
				<h2 class="name" id="m3-title"><?php esc_html_e( 'Intervenant·e', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></h2>
				<div class="role"><?php esc_html_e( 'Fonction officielle — à valider', 'fnc-wordpress-theme' ); ?></div>
				<div class="org"><?php esc_html_e( 'Institution — à valider', 'fnc-wordpress-theme' ); ?></div>
				<p class="quote"><?php esc_html_e( '« La citation de l’intervenant apparaîtra ici une fois sa participation confirmée. »', 'fnc-wordpress-theme' ); ?></p>
				<div class="dots" aria-hidden="true"><span class="on"></span><span></span><span></span><span></span><span></span></div>
			</div>
		</div>
	</section>

	<!-- M4 — LE TERRITOIRE -->
	<section class="moment" id="m4" aria-labelledby="m4-title">
		<img class="media-cover" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-territoire-brazzaville.png' ); ?>" alt="<?php esc_attr_e( 'Skyline de Brazzaville, République du Congo', 'fnc-wordpress-theme' ); ?>" />
		<div class="overlay" aria-hidden="true"></div>
		<div class="inner">
			<span class="eyebrow"><?php esc_html_e( 'Le territoire', 'fnc-wordpress-theme' ); ?></span>
			<h2 class="manifest" id="m4-title"><?php esc_html_e( 'L’Afrique centrale décide', 'fnc-wordpress-theme' ); ?> <span class="light"><?php esc_html_e( 'de son avenir numérique.', 'fnc-wordpress-theme' ); ?></span></h2>
			<p class="sub"><?php esc_html_e( 'Depuis Brazzaville. Depuis le Bassin du Congo. Une parole ancrée, pas importée.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<!-- M5 — LE PROGRAMME -->
	<section class="moment" id="m5" aria-labelledby="m5-title">
		<div class="grid">
			<figure><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-pupitre.png' ); ?>" alt="<?php esc_attr_e( 'Pupitre du Forum Numérique Congo sur la scène plénière', 'fnc-wordpress-theme' ); ?>" /></figure>
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></span>
				<h2 id="m5-title"><?php esc_html_e( 'Trois jours de travail collectif', 'fnc-wordpress-theme' ); ?></h2>
				<span class="prog-date"><?php esc_html_e( 'Journée d’ouverture · Date', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></span>
				<div class="rule" aria-hidden="true"></div>
				<div role="list">
					<?php
					$fnc_sessions_placeholder = array(
						array( 'time' => '09:00', 'title' => __( 'Ouverture officielle', 'fnc-wordpress-theme' ), 'room' => __( 'Salle plénière', 'fnc-wordpress-theme' ) ),
						array( 'time' => '10:30', 'title' => __( 'Table ronde — Gouvernance du numérique', 'fnc-wordpress-theme' ), 'room' => __( 'Salle de panel', 'fnc-wordpress-theme' ) ),
						array( 'time' => '14:00', 'title' => __( 'Ateliers techniques', 'fnc-wordpress-theme' ), 'room' => __( 'Salles de travail', 'fnc-wordpress-theme' ) ),
						array( 'time' => '16:30', 'title' => __( 'Clôture de la journée', 'fnc-wordpress-theme' ), 'room' => __( 'Salle plénière', 'fnc-wordpress-theme' ) ),
					);
					foreach ( $fnc_sessions_placeholder as $fnc_session ) :
						?>
						<div class="session" role="listitem">
							<span class="time"><?php echo esc_html( $fnc_session['time'] ); ?></span>
							<span class="title"><?php echo esc_html( $fnc_session['title'] ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></span>
							<span class="room"><?php echo esc_html( $fnc_session['room'] ); ?></span>
						</div>
						<?php
					endforeach;
					?>
				</div>
				<a class="link-more" href="#"><?php esc_html_e( 'Programme complet', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</div>
	</section>

	<!-- M6 — LES PARTENAIRES -->
	<section class="moment" id="m6" aria-labelledby="m6-title">
		<span class="eyebrow"><?php esc_html_e( 'La communauté', 'fnc-wordpress-theme' ); ?></span>
		<h2 id="m6-title"><?php esc_html_e( 'Ils construisent le Forum avec nous', 'fnc-wordpress-theme' ); ?></h2>
		<div class="rule-c" aria-hidden="true"></div>
		<div class="tiers">
			<div class="tier t1">
				<div class="logo-ph"><?php esc_html_e( 'Partenaire · À confirmer', 'fnc-wordpress-theme' ); ?></div>
				<div class="logo-ph"><?php esc_html_e( 'Partenaire · À confirmer', 'fnc-wordpress-theme' ); ?></div>
			</div>
			<div class="tier t2">
				<div class="logo-ph"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></div>
				<div class="logo-ph"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></div>
				<div class="logo-ph"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></div>
				<div class="logo-ph"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></div>
			</div>
		</div>
		<a class="link-more" href="#"><?php esc_html_e( 'Devenir partenaire', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
	</section>

	<!-- M7 — LES ARCHIVES -->
	<section class="moment" id="m7" aria-labelledby="m7-title">
		<span class="eyebrow"><?php esc_html_e( 'Les éditions', 'fnc-wordpress-theme' ); ?></span>
		<h2 id="m7-title"><?php esc_html_e( 'Une histoire qui se construit, édition après édition', 'fnc-wordpress-theme' ); ?></h2>
		<div>
			<?php for ( $fnc_i = 0; $fnc_i < 3; $fnc_i++ ) : ?>
				<div class="arch"><span class="year">—</span><span class="ph"><?php esc_html_e( 'Édition · Archive à confirmer', 'fnc-wordpress-theme' ); ?></span></div>
			<?php endfor; ?>
		</div>
		<a class="link-more" href="#"><?php esc_html_e( 'Voir toutes les éditions', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
	</section>

	<!-- M8 — LA DECISION FINALE -->
	<section class="moment" id="m8" aria-labelledby="m8-title">
		<img class="media-cover" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-badge.png' ); ?>" alt="<?php esc_attr_e( 'Badge d’accréditation du Forum Numérique Congo', 'fnc-wordpress-theme' ); ?>" />
		<div class="overlay" aria-hidden="true"></div>
		<div class="decision">
			<div class="count-label"><?php esc_html_e( 'La prochaine édition', 'fnc-wordpress-theme' ); ?></div>
			<h2 id="m8-title"><?php esc_html_e( 'Le Forum Numérique Congo commence dans', 'fnc-wordpress-theme' ); ?> <span class="num">—</span> <?php esc_html_e( 'jours', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'Date à confirmer', 'fnc-wordpress-theme' ); ?></span></h2>
			<a class="btn btn-red" href="#"><?php esc_html_e( 'Demander votre accréditation', 'fnc-wordpress-theme' ); ?>
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
