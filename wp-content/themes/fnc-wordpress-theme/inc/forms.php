<?php
/**
 * Forum Numérique Congo — rendu partagé des formulaires et des coordonnées.
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Definition d'un formulaire par type : entete, aide, libelle du bouton et
 * champs. Le `name` de chaque champ est celui attendu par FNC Core (réception des formulaires).
 *
 * @param string $type contact|inscription|partenariat
 * @return array<string,mixed>|null
 */
function fnc_submission_form_config( $type ) {
	$configs = array(
		'contact'     => array(
			'aria'    => __( 'Demande de contact', 'fnc-wordpress-theme' ),
			'heading' => __( 'Demande de contact', 'fnc-wordpress-theme' ),
			'help'    => __( 'Préciser le sujet nous aide à orienter votre demande dès l’ouverture du canal officiel.', 'fnc-wordpress-theme' ),
			'submit'  => __( 'Envoyer la demande', 'fnc-wordpress-theme' ),
			'fields'  => array(
				// Disposition alignée sur le site Next : Nom* / Organisation sur une
				// ligne, puis Email*, Sujet* et Message* chacun en PLEINE LARGEUR.
				array( 'name' => 'name', 'label' => __( 'Nom', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true ),
				array( 'name' => 'organization', 'label' => __( 'Organisation', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => false ),
				array( 'name' => 'email', 'label' => __( 'Email', 'fnc-wordpress-theme' ), 'type' => 'email', 'required' => true, 'full' => true ),
				array( 'name' => 'subject', 'label' => __( 'Sujet', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true, 'full' => true ),
				array( 'name' => 'message', 'label' => __( 'Message', 'fnc-wordpress-theme' ), 'type' => 'textarea', 'required' => true ),
			),
		),
		'inscription' => array(
			'aria'    => __( 'Formulaire d’inscription', 'fnc-wordpress-theme' ),
			'heading' => __( 'Votre demande', 'fnc-wordpress-theme' ),
			'help'    => __( 'Les champs marqués d’un astérisque sont obligatoires.', 'fnc-wordpress-theme' ),
			'submit'  => __( 'Envoyer ma demande', 'fnc-wordpress-theme' ),
			'fields'  => array(
				array( 'name' => 'fullName', 'label' => __( 'Nom complet', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => __( 'E-mail', 'fnc-wordpress-theme' ), 'type' => 'email', 'required' => true ),
				array( 'name' => 'organization', 'label' => __( 'Organisation', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => false ),
				array(
					'name'    => 'profile',
					'label'   => __( 'Profil', 'fnc-wordpress-theme' ),
					'type'    => 'select',
					'options' => array(
						__( 'Institution / secteur public', 'fnc-wordpress-theme' ),
						__( 'Entreprise', 'fnc-wordpress-theme' ),
						__( 'Étudiant·e / académique', 'fnc-wordpress-theme' ),
						__( 'Presse / média', 'fnc-wordpress-theme' ),
						__( 'Autre', 'fnc-wordpress-theme' ),
					),
				),
				array(
					'name'    => 'participation',
					'label'   => __( 'Mode de participation', 'fnc-wordpress-theme' ),
					'type'    => 'select',
					'options' => array(
						__( 'Présentiel', 'fnc-wordpress-theme' ),
						__( 'En ligne', 'fnc-wordpress-theme' ),
					),
				),
				array( 'name' => 'motivation', 'label' => __( 'Motivation', 'fnc-wordpress-theme' ), 'type' => 'textarea', 'required' => false ),
			),
		),
		'partenariat' => array(
			'aria'    => __( 'Demande de partenariat', 'fnc-wordpress-theme' ),
			'heading' => __( 'Demande de partenariat', 'fnc-wordpress-theme' ),
			'help'    => __( 'Votre demande est enregistrée avant tout accusé de réception. Les modalités détaillées restent à confirmer par l’organisation.', 'fnc-wordpress-theme' ),
			'submit'  => __( 'Envoyer la demande', 'fnc-wordpress-theme' ),
			'fields'  => array(
				array( 'name' => 'fullName', 'label' => __( 'Nom complet', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true ),
				array( 'name' => 'organization', 'label' => __( 'Organisation', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => true ),
				array( 'name' => 'role', 'label' => __( 'Fonction', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => false ),
				array( 'name' => 'email', 'label' => __( 'Email', 'fnc-wordpress-theme' ), 'type' => 'email', 'required' => true ),
				array( 'name' => 'phone', 'label' => __( 'Téléphone', 'fnc-wordpress-theme' ), 'type' => 'tel', 'required' => false ),
				array( 'name' => 'country', 'label' => __( 'Pays', 'fnc-wordpress-theme' ), 'type' => 'text', 'required' => false ),
				array(
					'name'     => 'interestType',
					'label'    => __( 'Type d’intérêt', 'fnc-wordpress-theme' ),
					'type'     => 'select',
					'required' => true,
					'options'  => array(
						__( 'Partenariat institutionnel', 'fnc-wordpress-theme' ),
						__( 'Sponsoring', 'fnc-wordpress-theme' ),
						__( 'Soutien média', 'fnc-wordpress-theme' ),
						__( 'Coopération technique', 'fnc-wordpress-theme' ),
						__( 'Autre', 'fnc-wordpress-theme' ),
					),
				),
				array( 'name' => 'message', 'label' => __( 'Message', 'fnc-wordpress-theme' ), 'type' => 'textarea', 'required' => true ),
			),
		),
	);

	return isset( $configs[ $type ] ) ? $configs[ $type ] : null;
}

/**
 * Rend un champ de formulaire (text / email / tel / select / textarea) avec
 * repopulation et etat d'erreur issus du flash FNC Core.
 */
function fnc_render_form_field( $type, $index, array $field, $flash, $focus_name = '' ) {
	$id       = 'fnc-' . $type . '-field-' . $index;
	$name     = $field['name'];
	$required = ! empty( $field['required'] );
	$is_area  = 'textarea' === $field['type'];
	$is_sel   = 'select' === $field['type'];
	$val      = function_exists( 'fnc_old' ) ? fnc_old( $flash, $name ) : '';
	$err_code = function_exists( 'fnc_field_error' ) ? fnc_field_error( $flash, $name ) : '';
	$invalid  = $err_code ? 'true' : 'false';
	$err_id   = $id . '-error';
	// aria-describedby relie le champ à son message d'erreur (a11y), autofocus
	// place le curseur sur le 1er champ invalide (parité ContactForm.tsx).
	$describe = $err_code ? ' aria-describedby="' . esc_attr( $err_id ) . '"' : '';
	$focus    = ( '' !== $focus_name && $name === $focus_name ) ? ' autofocus' : '';

	ob_start();
	?>
	<div class="field <?php echo ( $is_area || $is_sel || ! empty( $field['full'] ) ) ? 'full' : ''; ?><?php echo $err_code ? ' has-error' : ''; ?>">
		<label for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $field['label'] ); ?><?php echo $required ? ' <span class="req" aria-hidden="true">*</span>' : ''; ?>
		</label>
		<?php if ( $is_area ) : ?>
			<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" aria-invalid="<?php echo esc_attr( $invalid ); ?>"<?php echo $describe . $focus; // phpcs:ignore WordPress.Security.EscapeOutput -- attributs déjà échappés. ?><?php echo $required ? ' required' : ''; ?>><?php echo esc_textarea( $val ); ?></textarea>
		<?php elseif ( $is_sel ) : ?>
			<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" aria-invalid="<?php echo esc_attr( $invalid ); ?>"<?php echo $describe . $focus; // phpcs:ignore WordPress.Security.EscapeOutput -- attributs déjà échappés. ?><?php echo $required ? ' required' : ''; ?>>
				<?php foreach ( $field['options'] as $opt ) : ?>
					<option value="<?php echo esc_attr( $opt ); ?>"<?php selected( $val, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php else : ?>
			<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $field['type'] ); ?>" value="<?php echo esc_attr( $val ); ?>" aria-invalid="<?php echo esc_attr( $invalid ); ?>"<?php echo $describe . $focus; // phpcs:ignore WordPress.Security.EscapeOutput -- attributs déjà échappés. ?><?php echo $required ? ' required' : ''; ?> />
		<?php endif; ?>
		<?php if ( $err_code && function_exists( 'fnc_error_text' ) ) : ?>
			<p class="field-error" id="<?php echo esc_attr( $err_id ); ?>" role="alert"><?php echo esc_html( fnc_error_text( $err_code ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Rend le formulaire complet d'un type donne (identique aux gabarits).
 *
 * @param string $type contact|inscription|partenariat
 * @return string
 */
function fnc_render_submission_form( $type ) {
	$cfg = fnc_submission_form_config( $type );
	if ( ! $cfg ) {
		return '';
	}
	$flash = function_exists( 'fnc_take_flash' ) ? fnc_take_flash( $type ) : null;

	ob_start();
	?>
	<form class="card form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" aria-label="<?php echo esc_attr( $cfg['aria'] ); ?>">
		<h2 style="font-size:1.6rem;color:var(--navy-deep);"><?php echo esc_html( $cfg['heading'] ); ?></h2>
		<?php if ( ! empty( $cfg['help'] ) ) : ?>
			<p class="help"><?php echo esc_html( $cfg['help'] ); ?></p>
		<?php endif; ?>

		<?php
		// Champs caches (action / nonce / honeypot) + banniere de retour (réception des formulaires).
		if ( function_exists( 'fnc_form_fields' ) ) {
			fnc_form_fields( $type );
		}
		if ( function_exists( 'fnc_submission_banner' ) ) {
			echo fnc_submission_banner( $type, $flash ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup echappe par le plugin.
		}
		?>

		<div class="form-grid">
			<?php
			// 1er champ en erreur (ordre du formulaire) → reçoit autofocus.
			$fnc_focus_name = '';
			if ( function_exists( 'fnc_field_error' ) ) {
				foreach ( $cfg['fields'] as $field ) {
					if ( fnc_field_error( $flash, $field['name'] ) ) {
						$fnc_focus_name = $field['name'];
						break;
					}
				}
			}
			foreach ( $cfg['fields'] as $i => $field ) {
				echo fnc_render_form_field( $type, $i, $field, $flash, $fnc_focus_name ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup echappe dans le helper.
			}
			?>
		</div>

		<p class="help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires.', 'fnc-wordpress-theme' ); ?></p>
		<button class="btn form-submit" type="submit"><?php echo esc_html( $cfg['submit'] ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</button>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Rend le bloc coordonnees (RÈGLE 4 : uniquement les valeurs des Reglages,
 * placeholders filtres par « À confirmer »).
 *
 * @return string
 */
function fnc_render_contact_coordinates() {
	// Accesseurs « publics » : ils écartent les valeurs de démonstration (e-mail
	// invalide, téléphone factice « À confirmer » — motif ^\+?2420?60+$). À défaut
	// (extension inactive), repli sur le réglage brut.
	$email   = function_exists( 'fnc_public_email' ) ? fnc_public_email() : ( function_exists( 'fnc_get_setting' ) ? fnc_get_setting( 'email', '' ) : '' );
	$phone   = function_exists( 'fnc_public_phone' ) ? fnc_public_phone() : ( function_exists( 'fnc_get_setting' ) ? fnc_get_setting( 'phone', '' ) : '' );
	$address = function_exists( 'fnc_public_address' ) ? fnc_public_address() : ( function_exists( 'fnc_get_setting_i18n' ) ? fnc_get_setting_i18n( 'address', '' ) : '' );
	$social  = function_exists( 'fnc_social_links' ) ? fnc_social_links() : array();

	ob_start();
	?>
	<dl class="pract-contacts" style="margin-top:26px;">
		<dt><?php esc_html_e( 'E-mail', 'fnc-wordpress-theme' ); ?></dt>
		<dd>
			<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
			<?php else : ?>
				<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
			<?php endif; ?>
		</dd>
		<dt><?php esc_html_e( 'Téléphone', 'fnc-wordpress-theme' ); ?></dt>
		<dd>
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
			<?php else : ?>
				<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
			<?php endif; ?>
		</dd>
		<dt><?php esc_html_e( 'Adresse', 'fnc-wordpress-theme' ); ?></dt>
		<dd>
			<?php if ( $address ) : ?>
				<?php echo nl2br( esc_html( $address ) ); ?>
			<?php else : ?>
				<span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span>
			<?php endif; ?>
		</dd>
		<?php if ( ! empty( $social ) ) : ?>
			<dt><?php esc_html_e( 'Réseaux', 'fnc-wordpress-theme' ); ?></dt>
			<dd>
				<?php
				$links = array();
				foreach ( $social as $s ) {
					$platform = isset( $s['platform'] ) ? $s['platform'] : 'other';
					$url      = isset( $s['url'] ) ? $s['url'] : '';
					$label    = ( isset( $s['label'] ) && '' !== $s['label'] ) ? $s['label'] : ( function_exists( 'fnc_social_label' ) ? fnc_social_label( $platform ) : $platform );
					$links[]  = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a>';
				}
				echo implode( ' · ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput -- liens echappes ci-dessus.
				?>
			</dd>
		<?php endif; ?>
	</dl>
	<?php
	return (string) ob_get_clean();
}
