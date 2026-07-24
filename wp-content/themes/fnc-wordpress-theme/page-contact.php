<?php
/**
 * Gabarit de page — "Contact".
 *
 * Aligne sur la page Contact reelle (localhost:3000/fr/contact) : heros, section
 * "Orientation" (split editorial + photo), puis un bloc en deux colonnes
 * "Coordonnees" + formulaire "Demande de contact".
 *
 * Le formulaire reste non fonctionnel (pas de handler d'envoi) : fidele a la
 * posture du site reel tant que le canal officiel n'est pas ouvert. Les
 * coordonnees proviennent des Reglages FNC (aucune donnee inventee) et ne
 * s'affichent que si elles sont renseignees.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/*
 * Lot 2 — composition par blocs. Des que l'editorial compose cette page avec
 * des blocs FNC, ce gabarit s'efface ; sinon il porte le contenu institutionnel
 * aligne sur le site reel.
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
		'eyebrow'    => __( 'Contact', 'fnc-wordpress-theme' ),
		'title'      => __( 'Une porte d’entrée claire,', 'fnc-wordpress-theme' ),
		'title_b'    => __( 'pour chaque demande.', 'fnc-wordpress-theme' ),
		'intro'      => __( 'Partenariat, presse, intervention ou simple question : dites-nous ce qui vous amène, nous orientons votre demande vers le bon interlocuteur.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/contact.png',
		'image_alt'  => __( 'Prise de parole au Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Contact', 'fnc-wordpress-theme' ),
	)
);

// Coordonnees issues des Reglages FNC (RÈGLE 4 — rien d'invente).
$fnc_email   = fnc_get_setting( 'email', '' );
$fnc_phone   = fnc_get_setting( 'phone', '' );
$fnc_address = fnc_get_setting_i18n( 'address', '' );
$fnc_social  = fnc_social_links();

// Champs du formulaire : name = nom du champ attendu par FNC Core (Module A).
$fnc_contact_fields = array(
	array( 'name' => 'name', 'label' => __( 'Nom', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true ),
	array( 'name' => 'organization', 'label' => __( 'Organisation', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => false ),
	array( 'name' => 'email', 'label' => __( 'Email', 'fnc-wordpress-theme' ), 'type' => 'email', 'required' => true ),
	array( 'name' => 'subject', 'label' => __( 'Sujet', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true ),
	array( 'name' => 'message', 'label' => __( 'Message', 'fnc-wordpress-theme' ), 'type' => 'textarea', 'required' => true ),
);
// Flash de retour (FNC Core Module A) : repopulation + erreurs par champ.
$fnc_flash = function_exists( 'fnc_take_flash' ) ? fnc_take_flash( 'contact' ) : null;
?>

<main id="main">
	<!-- Orientation -->
	<section class="section">
		<div class="split">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Orientation', 'fnc-wordpress-theme' ); ?></span>
				<h2 class="lines"><?php esc_html_e( 'Vous orienter,', 'fnc-wordpress-theme' ); ?><br><?php esc_html_e( 'sans rien promettre', 'fnc-wordpress-theme' ); ?><br><span class="muted"><?php esc_html_e( 'à la légère.', 'fnc-wordpress-theme' ); ?></span></h2>
				<p class="body"><?php esc_html_e( 'Un bon contact inspire confiance avant de demander quoi que ce soit. Vous trouverez ici les coordonnées officielles et un formulaire pour préciser votre demande — nous l’orientons vers le bon interlocuteur.', 'fnc-wordpress-theme' ); ?></p>
			</div>
			<figure><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-pupitre.png' ); ?>" alt="<?php esc_attr_e( 'Prise de parole au Forum Numérique Congo', 'fnc-wordpress-theme' ); ?>" /></figure>
		</div>
	</section>

	<!-- Coordonnées + formulaire -->
	<section class="section linen">
		<div class="split media-left">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Coordonnées', 'fnc-wordpress-theme' ); ?></span>
				<p class="body"><?php esc_html_e( 'Chaque emplacement attend son information validée. Rien n’est complété au hasard.', 'fnc-wordpress-theme' ); ?></p>
				<dl class="pract-contacts" style="margin-top:26px;">
					<dt><?php esc_html_e( 'E-mail', 'fnc-wordpress-theme' ); ?></dt>
					<dd>
						<?php if ( $fnc_email ) : ?>
							<a href="mailto:<?php echo esc_attr( antispambot( $fnc_email ) ); ?>"><?php echo esc_html( antispambot( $fnc_email ) ); ?></a>
						<?php else : ?>
							<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Téléphone', 'fnc-wordpress-theme' ); ?></dt>
					<dd>
						<?php if ( $fnc_phone ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $fnc_phone ) ); ?>"><?php echo esc_html( $fnc_phone ); ?></a>
						<?php else : ?>
							<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Adresse', 'fnc-wordpress-theme' ); ?></dt>
					<dd>
						<?php if ( $fnc_address ) : ?>
							<?php echo nl2br( esc_html( $fnc_address ) ); ?>
						<?php else : ?>
							<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
						<?php endif; ?>
					</dd>
					<?php if ( ! empty( $fnc_social ) ) : ?>
						<dt><?php esc_html_e( 'Réseaux', 'fnc-wordpress-theme' ); ?></dt>
						<dd>
							<?php
							$fnc_links = array();
							foreach ( $fnc_social as $fnc_s ) {
									$fnc_platform = isset( $fnc_s['platform'] ) ? $fnc_s['platform'] : 'other';
									$fnc_url      = isset( $fnc_s['url'] ) ? $fnc_s['url'] : '';
									$fnc_label    = ( isset( $fnc_s['label'] ) && '' !== $fnc_s['label'] ) ? $fnc_s['label'] : fnc_social_label( $fnc_platform );
								$fnc_links[] = '<a href="' . esc_url( $fnc_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $fnc_label ) . '</a>';
							}
							echo implode( ' · ', $fnc_links ); // phpcs:ignore WordPress.Security.EscapeOutput -- liens echappes ci-dessus.
							?>
						</dd>
					<?php endif; ?>
				</dl>
			</div>

			<form class="card form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" aria-label="<?php esc_attr_e( 'Demande de contact', 'fnc-wordpress-theme' ); ?>">
				<h2 style="font-size:1.6rem;color:var(--navy-deep);"><?php esc_html_e( 'Demande de contact', 'fnc-wordpress-theme' ); ?></h2>
				<p class="help"><?php esc_html_e( 'Préciser le sujet nous aide à orienter votre demande dès l’ouverture du canal officiel.', 'fnc-wordpress-theme' ); ?></p>

				<?php
				// Champs caches (action/nonce/honeypot) fournis par FNC Core (Module A).
				if ( function_exists( 'fnc_form_fields' ) ) { fnc_form_fields( 'contact' ); }
				if ( function_exists( 'fnc_submission_banner' ) ) { echo fnc_submission_banner( 'contact', $fnc_flash ); } // phpcs:ignore WordPress.Security.EscapeOutput
				?>

				<div class="form-grid">
					<?php
					foreach ( $fnc_contact_fields as $fnc_i => $fnc_field ) :
						$fnc_field_id = 'fnc-contact-field-' . $fnc_i;
						$fnc_n   = $fnc_field['name'];
						$fnc_val = function_exists( 'fnc_old' ) ? fnc_old( $fnc_flash, $fnc_n ) : '';
						$fnc_inv = ( function_exists( 'fnc_field_error' ) && fnc_field_error( $fnc_flash, $fnc_n ) ) ? 'true' : 'false';
						?>
						<div class="field <?php echo 'textarea' === $fnc_field['type'] ? 'full' : ''; ?>">
							<label for="<?php echo esc_attr( $fnc_field_id ); ?>">
								<?php echo esc_html( $fnc_field['label'] ); ?><?php echo $fnc_field['required'] ? ' <span class="req" aria-hidden="true">*</span>' : ''; ?>
							</label>
							<?php if ( 'textarea' === $fnc_field['type'] ) : ?>
								<textarea id="<?php echo esc_attr( $fnc_field_id ); ?>" name="<?php echo esc_attr( $fnc_n ); ?>" aria-invalid="<?php echo esc_attr( $fnc_inv ); ?>"<?php echo $fnc_field['required'] ? ' required' : ''; ?>><?php echo esc_textarea( $fnc_val ); ?></textarea>
							<?php else : ?>
								<input id="<?php echo esc_attr( $fnc_field_id ); ?>" name="<?php echo esc_attr( $fnc_n ); ?>" type="<?php echo esc_attr( $fnc_field['type'] ); ?>" value="<?php echo esc_attr( $fnc_val ); ?>" aria-invalid="<?php echo esc_attr( $fnc_inv ); ?>"<?php echo $fnc_field['required'] ? ' required' : ''; ?> />
							<?php endif; ?>
						</div>
						<?php
					endforeach;
					?>
				</div>

				<p class="help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires. Vos informations ne sont transmises à personne tant que le canal officiel n’est pas ouvert.', 'fnc-wordpress-theme' ); ?></p>
				<button class="btn btn-red" type="submit"><?php esc_html_e( 'Envoyer la demande', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
		</div>
	</section>
</main>

<?php get_footer(); ?>
