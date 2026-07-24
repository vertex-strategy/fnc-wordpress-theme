<?php
/**
 * Gabarit de page — "Inscription".
 *
 * Aligne sur la page reelle localhost:3000/fr/inscription : heros « Demander une
 * inscription » puis la carte « Votre demande » avec le formulaire (nom, e-mail,
 * organisation, profil, mode de participation, motivation).
 *
 * Formulaire non fonctionnel (pas de handler d'envoi) — fidele a la posture du
 * site tant que le canal n'est pas ouvert, et a l'exclusion de la collection
 * "Registrations" du perimetre (ADR-007, Decision 2). Seule partie dynamique :
 * le nom de l'edition active reelle (_fnc_edition_active).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Participer', 'fnc-wordpress-theme' ),
		'title'      => __( 'Demander une inscription', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Adressez votre demande de participation. Notre équipe l’examine et revient vers vous — cette demande ne vaut pas confirmation.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-badge.png',
		'image_alt'  => __( 'Badge du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Inscription', 'fnc-wordpress-theme' ),
	)
);

$fnc_active_edition = get_posts(
	array(
		'post_type'      => 'fnc_edition',
		'posts_per_page' => 1,
		'meta_key'       => '_fnc_edition_active',
		'meta_value'     => '1',
	)
);
$fnc_edition_title = ! empty( $fnc_active_edition ) ? get_the_title( $fnc_active_edition[0] ) : '';

$fnc_profiles       = array(
	__( 'Institution / secteur public', 'fnc-wordpress-theme' ),
	__( 'Entreprise', 'fnc-wordpress-theme' ),
	__( 'Étudiant·e / académique', 'fnc-wordpress-theme' ),
	__( 'Presse / média', 'fnc-wordpress-theme' ),
	__( 'Autre', 'fnc-wordpress-theme' ),
);
$fnc_participations = array(
	__( 'Présentiel', 'fnc-wordpress-theme' ),
	__( 'En ligne', 'fnc-wordpress-theme' ),
);
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<form class="card form" aria-label="<?php esc_attr_e( 'Formulaire d’inscription', 'fnc-wordpress-theme' ); ?>">
				<h2 style="font-size:1.6rem;color:var(--navy-deep);"><?php esc_html_e( 'Votre demande', 'fnc-wordpress-theme' ); ?></h2>
				<p class="help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires.', 'fnc-wordpress-theme' ); ?></p>
				<?php if ( $fnc_edition_title ) : ?>
					<p class="help"><?php esc_html_e( 'Vous vous inscrivez à', 'fnc-wordpress-theme' ); ?> <strong><?php echo esc_html( $fnc_edition_title ); ?></strong></p>
				<?php endif; ?>

				<?php // Pot de miel anti-spam (masque). ?>
				<div class="hp" aria-hidden="true" style="position:absolute;left:-9999px;">
					<label><?php esc_html_e( 'Ne pas remplir', 'fnc-wordpress-theme' ); ?><input type="text" name="fnc_hp" tabindex="-1" autocomplete="off" /></label>
				</div>

				<div class="form-grid">
					<div class="field">
						<label for="fnc-ins-name"><?php esc_html_e( 'Nom complet', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-ins-name" type="text" required placeholder="<?php esc_attr_e( 'Prénom et nom', 'fnc-wordpress-theme' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-ins-email"><?php esc_html_e( 'E-mail', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-ins-email" type="email" required placeholder="<?php esc_attr_e( 'nom@organisation.org', 'fnc-wordpress-theme' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-ins-org"><?php esc_html_e( 'Organisation', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-ins-org" type="text" placeholder="<?php esc_attr_e( 'Organisation ou institution', 'fnc-wordpress-theme' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-ins-profile"><?php esc_html_e( 'Profil', 'fnc-wordpress-theme' ); ?></label>
						<select id="fnc-ins-profile">
							<?php foreach ( $fnc_profiles as $fnc_opt ) : ?>
								<option value="<?php echo esc_attr( $fnc_opt ); ?>"><?php echo esc_html( $fnc_opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field">
						<label for="fnc-ins-participation"><?php esc_html_e( 'Mode de participation', 'fnc-wordpress-theme' ); ?></label>
						<select id="fnc-ins-participation">
							<?php foreach ( $fnc_participations as $fnc_opt ) : ?>
								<option value="<?php echo esc_attr( $fnc_opt ); ?>"><?php echo esc_html( $fnc_opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field full">
						<label for="fnc-ins-motivation"><?php esc_html_e( 'Motivation', 'fnc-wordpress-theme' ); ?></label>
						<textarea id="fnc-ins-motivation" placeholder="<?php esc_attr_e( 'Présentez en quelques lignes votre intérêt (facultatif)', 'fnc-wordpress-theme' ); ?>"></textarea>
					</div>
				</div>

				<div class="notice"><?php esc_html_e( 'Le service d’envoi reste à connecter.', 'fnc-wordpress-theme' ); ?></div>
				<button class="btn btn-red" type="submit"><?php esc_html_e( 'Envoyer ma demande', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
		</div>
	</section>
</main>

<?php get_footer(); ?>
