<?php
/**
 * Forum Numérique Congo — gabarit de la page « Inscription ».
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

fnc_render_opening_hero(
	array(
		'eyebrow'    => __( 'Participer', 'fnc-wordpress-theme' ),
		'title'      => __( 'Demander une inscription', 'fnc-wordpress-theme' ),
		'intro'      => __( 'Adressez votre demande de participation. Notre équipe l’examine et revient vers vous — cette demande ne vaut pas confirmation.', 'fnc-wordpress-theme' ),
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
$fnc_flash = function_exists( 'fnc_take_flash' ) ? fnc_take_flash( 'inscription' ) : null;
$fnc_participations = array(
	__( 'Présentiel', 'fnc-wordpress-theme' ),
	__( 'En ligne', 'fnc-wordpress-theme' ),
);
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<?php
			// État « fermé » honnête quand les inscriptions ne sont pas ouvertes
			// La page reste en ligne mais passe en noindex
			// (posé par fnc_feature_flag_gate / inc/seo.php) et le formulaire est
			// remplacé par un message ; le CTA d'inscription est déjà masqué ailleurs.
			$fnc_reg_open = ! function_exists( 'fnc_registration_enabled' ) || fnc_registration_enabled();
			if ( ! $fnc_reg_open ) :
				?>
				<div class="callout">
					<h2><?php esc_html_e( 'Les inscriptions ne sont pas encore ouvertes', 'fnc-wordpress-theme' ); ?></h2>
					<p><?php esc_html_e( 'La billetterie de la prochaine édition ouvrira prochainement. Revenez bientôt ou suivez nos actualités pour être informé·e de l’ouverture.', 'fnc-wordpress-theme' ); ?></p>
					<a class="btn btn-ghost-light" href="<?php echo esc_url( fnc_page_url( 'le-forum' ) ); ?>"><?php esc_html_e( 'Découvrir le Forum', 'fnc-wordpress-theme' ); ?></a>
				</div>
				<?php
			else :
				?>
			<form class="card form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" aria-label="<?php esc_attr_e( 'Formulaire d’inscription', 'fnc-wordpress-theme' ); ?>" aria-describedby="fnc-inscription-help">
				<h2 style="font-size:1.6rem;color:var(--navy-deep);"><?php esc_html_e( 'Votre demande', 'fnc-wordpress-theme' ); ?></h2>
				<p class="help" id="fnc-inscription-help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires.', 'fnc-wordpress-theme' ); ?></p>
				<?php if ( $fnc_edition_title ) : ?>
					<p class="help"><?php esc_html_e( 'Vous vous inscrivez à', 'fnc-wordpress-theme' ); ?> <strong><?php echo esc_html( $fnc_edition_title ); ?></strong></p>
				<?php endif; ?>

				<?php
				if ( function_exists( 'fnc_form_fields' ) ) { fnc_form_fields( 'inscription' ); }
				if ( function_exists( 'fnc_submission_banner' ) ) { echo fnc_submission_banner( 'inscription', $fnc_flash ); } // phpcs:ignore WordPress.Security.EscapeOutput
				?>

				<div class="form-grid">
					<div class="field">
						<label for="fnc-ins-name"><?php esc_html_e( 'Nom complet', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-ins-name" name="fullName" type="text" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_flash, 'fullName' ) : '' ); ?>" aria-invalid="<?php echo esc_attr( ( function_exists( 'fnc_field_error' ) && fnc_field_error( $fnc_flash, 'fullName' ) ) ? 'true' : 'false' ); ?>" required placeholder="<?php esc_attr_e( 'Prénom et nom', 'fnc-wordpress-theme' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-ins-email"><?php esc_html_e( 'E-mail', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-ins-email" name="email" type="email" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_flash, 'email' ) : '' ); ?>" aria-invalid="<?php echo esc_attr( ( function_exists( 'fnc_field_error' ) && fnc_field_error( $fnc_flash, 'email' ) ) ? 'true' : 'false' ); ?>" required placeholder="<?php esc_attr_e( 'nom@organisation.org', 'fnc-wordpress-theme' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-ins-org"><?php esc_html_e( 'Organisation', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-ins-org" name="organization" type="text" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_flash, 'organization' ) : '' ); ?>" placeholder="<?php esc_attr_e( 'Organisation ou institution', 'fnc-wordpress-theme' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-ins-profile"><?php esc_html_e( 'Profil', 'fnc-wordpress-theme' ); ?></label>
						<select id="fnc-ins-profile" name="profile">
							<?php foreach ( $fnc_profiles as $fnc_opt ) : ?>
								<option value="<?php echo esc_attr( $fnc_opt ); ?>"><?php echo esc_html( $fnc_opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field">
						<label for="fnc-ins-participation"><?php esc_html_e( 'Mode de participation', 'fnc-wordpress-theme' ); ?></label>
						<select id="fnc-ins-participation" name="participation">
							<?php foreach ( $fnc_participations as $fnc_opt ) : ?>
								<option value="<?php echo esc_attr( $fnc_opt ); ?>"><?php echo esc_html( $fnc_opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field full">
						<label for="fnc-ins-motivation"><?php esc_html_e( 'Motivation', 'fnc-wordpress-theme' ); ?></label>
						<textarea id="fnc-ins-motivation" name="motivation" placeholder="<?php esc_attr_e( 'Présentez en quelques lignes votre intérêt (facultatif)', 'fnc-wordpress-theme' ); ?>"></textarea>
					</div>
				</div>

				<button class="btn" type="submit"><?php esc_html_e( 'Envoyer ma demande', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php get_footer(); ?>
