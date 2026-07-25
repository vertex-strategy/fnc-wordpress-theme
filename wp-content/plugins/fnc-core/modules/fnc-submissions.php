<?php
/**
 * Plugin Name: FNC Core — Réception des formulaires
 * Description: Traitement serveur des formulaires Contact / Inscription / Partenariat :
 *              validation par champ, anti-spam, anti-abus (limite d'envois), stockage
 *              privé et accusé de réception par e-mail.
 * Version: 0.1.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INTÉGRATION : ce fichier est autonome (activable tel quel) OU à fusionner dans le
 * plugin FNC Core (includes/submissions.php). Le MARKUP des formulaires reste au thème ;
 * ce module fournit uniquement le HANDLER + des helpers de gabarit (voir bas de fichier).
 *
 * PRINCIPE (RÈGLE : jamais de faux succès) : le succès n'est affiché que si le stockage
 * a réussi ; si l'e-mail échoue, la demande est TOUT DE MÊME enregistrée et l'état
 * d'accusé est tracé.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * 1. Définition des 3 formulaires — champs de chaque formulaire
 *    (ContactSubmissions, Registrations, PartnershipRequests).
 *    Les longueurs maximales bornent chaque champ.
 * ======================================================================== */
function fnc_submission_forms() {
	return array(
		'contact' => array(
			'label'  => 'Contact',
			'fields' => array(
				'name'         => array( 'required' => true,  'type' => 'text',     'max' => 120 ),
				'organization' => array( 'required' => false, 'type' => 'text',     'max' => 160 ),
				'email'        => array( 'required' => true,  'type' => 'email',    'max' => 180 ),
				'subject'      => array( 'required' => true,  'type' => 'text',     'max' => 180 ),
				'message'      => array( 'required' => true,  'type' => 'textarea', 'max' => 4000 ),
			),
		),
		'inscription' => array(
			'label'  => 'Inscription',
			'fields' => array(
				'fullName'      => array( 'required' => true,  'type' => 'text',     'max' => 120 ),
				'email'         => array( 'required' => true,  'type' => 'email',    'max' => 180 ),
				'organization'  => array( 'required' => false, 'type' => 'text',     'max' => 160 ),
				'profile'       => array( 'required' => false, 'type' => 'text',     'max' => 80 ),
				'participation' => array( 'required' => false, 'type' => 'text',     'max' => 80 ),
				'motivation'    => array( 'required' => false, 'type' => 'textarea', 'max' => 4000 ),
				// `edition` : résolue côté serveur (édition active), l'utilisateur ne
				// choisit pas — voir fnc_resolve_active_edition(). Cf. filtre plus bas.
			),
		),
		'partenariat' => array(
			'label'  => 'Partenariat',
			'fields' => array(
				'fullName'     => array( 'required' => true,  'type' => 'text',     'max' => 120 ),
				'organization' => array( 'required' => true,  'type' => 'text',     'max' => 160 ),
				'role'         => array( 'required' => false, 'type' => 'text',     'max' => 120 ),
				'email'        => array( 'required' => true,  'type' => 'email',    'max' => 180 ),
				'phone'        => array( 'required' => false, 'type' => 'text',     'max' => 40 ),
				'country'      => array( 'required' => false, 'type' => 'text',     'max' => 80 ),
				'interestType' => array( 'required' => true,  'type' => 'text',     'max' => 80 ),
				'message'      => array( 'required' => true,  'type' => 'textarea', 'max' => 4000 ),
			),
		),
	);
}

/* ==========================================================================
 * 2. CPT privé « fnc_submission » — source de vérité des demandes.
 *    Non public, non créable à la main (uniquement via le handler).
 * ======================================================================== */
add_action( 'init', 'fnc_register_submission_cpt' );
function fnc_register_submission_cpt() {
	register_post_type(
		'fnc_submission',
		array(
			'labels'          => array(
				'name'          => __( 'Demandes reçues', 'fnc' ),
				'singular_name' => __( 'Demande', 'fnc' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-email-alt',
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ), // pas de saisie manuelle
			'supports'        => array( 'title' ),
			'has_archive'     => false,
			'rewrite'         => false,
			'exclude_from_search' => true,
		)
	);
}

// Colonnes d'admin lisibles (type, e-mail, accusé, date).
add_filter( 'manage_fnc_submission_posts_columns', function ( $cols ) {
	return array(
		'cb'       => $cols['cb'],
		'title'    => __( 'Sujet', 'fnc' ),
		'fnc_type' => __( 'Type', 'fnc' ),
		'fnc_mail' => __( 'E-mail', 'fnc' ),
		'fnc_ack'  => __( 'Accusé', 'fnc' ),
		'date'     => __( 'Reçue le', 'fnc' ),
	);
} );
add_action( 'manage_fnc_submission_posts_custom_column', function ( $col, $post_id ) {
	if ( 'fnc_type' === $col ) { echo esc_html( (string) get_post_meta( $post_id, 'fnc_type', true ) ); }
	if ( 'fnc_mail' === $col ) { echo esc_html( (string) get_post_meta( $post_id, 'email', true ) ); }
	if ( 'fnc_ack' === $col )  { echo esc_html( (string) get_post_meta( $post_id, 'acknowledgement', true ) ); }
}, 10, 2 );

/* ==========================================================================
 * 3. Endpoint — admin-post.php (fonctionne SANS JS, visiteurs déconnectés inclus).
 * ======================================================================== */
add_action( 'admin_post_nopriv_fnc_submit', 'fnc_handle_submission' );
add_action( 'admin_post_fnc_submit', 'fnc_handle_submission' );

function fnc_handle_submission() {
	$forms    = fnc_submission_forms();
	$type     = isset( $_POST['fnc_form'] ) ? sanitize_key( wp_unslash( $_POST['fnc_form'] ) ) : '';
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	// Nonce + type connu.
	$nonce = isset( $_POST['fnc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_nonce'] ) ) : '';
	if ( ! isset( $forms[ $type ] ) || ! wp_verify_nonce( $nonce, 'fnc_submit_' . $type ) ) {
		fnc_submission_flash_redirect( $redirect, $type, array( '_form' => 'invalid' ), array() );
	}

	// Honeypot silencieux : on répond « ok » sans rien stocker (ne pas informer le robot).
	if ( ! empty( $_POST['fnc_hp'] ) ) {
		fnc_submission_success_redirect( $redirect, $type );
	}

	// Gouvernance par option : un type peut être FERMÉ (p. ex. inscriptions non
	// ouvertes → refus honnête, message « fermé »). Défaut : accepté.
	if ( ! apply_filters( 'fnc_submission_accepts', true, $type ) ) {
		fnc_submission_flash_redirect( $redirect, $type, array( '_form' => 'closed' ), array() );
	}

	// Anti-abus : 5 envois / 10 min / IP / type.
	if ( fnc_submission_rate_limited( $type ) ) {
		fnc_submission_flash_redirect( $redirect, $type, array( '_form' => 'rate' ), array() );
	}

	// Validation par champ.
	$errors = array();
	$data   = array();
	foreach ( $forms[ $type ]['fields'] as $key => $rule ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		if ( 'email' === $rule['type'] ) {
			$val = sanitize_email( $raw );
		} elseif ( 'textarea' === $rule['type'] ) {
			$val = trim( sanitize_textarea_field( $raw ) );
		} else {
			$val = trim( sanitize_text_field( $raw ) );
		}

		if ( $rule['required'] && '' === $val ) {
			$errors[ $key ] = 'required';
			continue;
		}
		if ( 'email' === $rule['type'] && '' !== $val && ! is_email( $val ) ) {
			$errors[ $key ] = 'invalid';
			continue;
		}
		if ( isset( $rule['max'] ) && mb_strlen( $val ) > $rule['max'] ) {
			$errors[ $key ] = 'too_long';
			continue;
		}
		$data[ $key ] = $val;
	}

	if ( ! empty( $errors ) ) {
		fnc_submission_flash_redirect( $redirect, $type, array( '_form' => 'validation' ), $errors, $data );
	}

	$data['locale'] = fnc_submission_locale();

	// Stockage (source de vérité). Échec => erreur, PAS de faux succès.
	$post_id = fnc_store_submission( $type, $data );
	if ( ! $post_id ) {
		fnc_submission_flash_redirect( $redirect, $type, array( '_form' => 'store' ), array(), $data );
	}

	// Accusé de réception best-effort — la demande est déjà enregistrée.
	$sent = fnc_submission_send_ack( $type, $data );
	update_post_meta( $post_id, 'acknowledgement', $sent ? 'sent' : 'not-delivered' );

	/**
	 * Hook d'extension (notifications internes, CRM, résolution d'édition active pour
	 * l'inscription via fnc_current_edition_id(), etc.).
	 */
	do_action( 'fnc_submission_stored', $post_id, $type, $data );

	fnc_submission_success_redirect( $redirect, $type );
}

/* ==========================================================================
 * 4. Cœur — stockage, e-mail, anti-abus, IP, locale.
 * ======================================================================== */
function fnc_store_submission( $type, $data ) {
	$who   = isset( $data['name'] ) ? $data['name'] : ( isset( $data['fullName'] ) ? $data['fullName'] : ( isset( $data['email'] ) ? $data['email'] : '' ) );
	$title = sprintf( '[%s] %s', $type, isset( $data['subject'] ) ? $data['subject'] : $who );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'fnc_submission',
			'post_status' => 'private',
			'post_title'  => wp_strip_all_tags( $title ),
		),
		true
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}

	foreach ( $data as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}
	update_post_meta( $post_id, 'fnc_type', $type );
	update_post_meta( $post_id, 'workflow_status', 'new' );
	update_post_meta( $post_id, 'acknowledgement', 'pending' );
	update_post_meta( $post_id, 'submitted_at', current_time( 'mysql' ) );

	return (int) $post_id;
}

function fnc_submission_send_ack( $type, $data ) {
	$to = isset( $data['email'] ) ? $data['email'] : '';
	if ( ! is_email( $to ) ) {
		return false;
	}
	$en    = ( isset( $data['locale'] ) && 'en' === $data['locale'] );
	$who   = isset( $data['name'] ) ? $data['name'] : ( isset( $data['fullName'] ) ? $data['fullName'] : '' );
	$brand = get_bloginfo( 'name' );

	$subject = $en
		? 'We received your message — ' . $brand
		: 'Votre message a bien été reçu — ' . $brand;
	$body = $en
		? sprintf( "Hello %s,\n\nWe have received your request and will get back to you shortly.\n\n%s", $who, $brand )
		: sprintf( "Bonjour %s,\n\nNous avons bien reçu votre demande et reviendrons vers vous rapidement.\n\n%s", $who, $brand );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $to );
	return (bool) wp_mail( $to, $subject, $body, $headers );
}

/** 5 envois / 10 min / IP / type (transient). Anti-abus, complète le honeypot. */
function fnc_submission_rate_limited( $type ) {
	$key   = 'fnc_rl_' . md5( $type . '|' . fnc_client_ip() );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return true;
	}
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
	return false;
}

function fnc_client_ip() {
	// Derrière un reverse-proxy (Cloudflare…), REMOTE_ADDR est le proxy. Adapter au besoin.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
	return sanitize_text_field( $ip );
}

/** Locale courante (Polylang si présent, sinon fr). */
function fnc_submission_locale() {
	if ( function_exists( 'pll_current_language' ) ) {
		$l = pll_current_language();
		return ( 'en' === $l ) ? 'en' : 'fr';
	}
	return ( 0 === strpos( get_locale(), 'en' ) ) ? 'en' : 'fr';
}

/* ==========================================================================
 * 5. Flash (erreurs + valeurs saisies) via transient + token — aucune donnée
 *    personnelle dans l'URL. Le gabarit repopule et affiche les erreurs.
 * ======================================================================== */
function fnc_submission_flash_redirect( $redirect, $type, $flags, $errors = array(), $old = array() ) {
	$token = wp_generate_password( 16, false );
	set_transient(
		'fnc_flash_' . $token,
		array( 'type' => $type, 'flags' => $flags, 'errors' => $errors, 'old' => $old ),
		MINUTE_IN_SECONDS
	);
	wp_safe_redirect( add_query_arg( array( 'fnc_flash' => $token ), remove_query_arg( array( 'fnc_flash', 'fnc_sent' ), $redirect ) ) . '#form' );
	exit;
}

function fnc_submission_success_redirect( $redirect, $type ) {
	wp_safe_redirect( add_query_arg( array( 'fnc_sent' => rawurlencode( $type ) ), remove_query_arg( array( 'fnc_flash', 'fnc_sent' ), $redirect ) ) . '#form' );
	exit;
}

/* ==========================================================================
 * 6. HELPERS DE GABARIT — à appeler dans les templates du thème.
 *
 *  a) Ouvrir le <form> :
 *       <form class="card form" method="post"
 *             action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
 *         <?php fnc_form_fields( 'contact' ); // ou 'inscription' / 'partenariat' ?>
 *         ... vos champs, avec name="name", name="email", ... ...
 *       </form>
 *     (Le honeypot fnc_hp existe déjà dans vos templates ; sinon fnc_form_fields l'ajoute.)
 *
 *  b) Bannière + repopulation :
 *       <?php $flash = fnc_take_flash( 'contact' ); ?>
 *       <?php echo fnc_submission_banner( 'contact', $flash ); ?>
 *       <input name="email" value="<?php echo esc_attr( fnc_old( $flash, 'email' ) ); ?>"
 *              aria-invalid="<?php echo fnc_field_error( $flash, 'email' ) ? 'true' : 'false'; ?>">
 *       <?php if ( $e = fnc_field_error( $flash, 'email' ) ) : ?>
 *         <p class="msg"><?php echo esc_html( fnc_error_text( $e ) ); ?></p>
 *       <?php endif; ?>
 * ======================================================================== */
function fnc_form_fields( $type ) {
	echo '<input type="hidden" name="action" value="fnc_submit">';
	printf( '<input type="hidden" name="fnc_form" value="%s">', esc_attr( $type ) );
	wp_nonce_field( 'fnc_submit_' . $type, 'fnc_nonce' );
	// Honeypot de secours (masqué) si le template ne le fournit pas.
	echo '<div aria-hidden="true" style="position:absolute;left:-9999px" tabindex="-1"><label>Ne pas remplir<input type="text" name="fnc_hp" tabindex="-1" autocomplete="off"></label></div>';
}

/** Lit ET consomme le flash pour ce type de formulaire. */
function fnc_take_flash( $type ) {
	$token = isset( $_GET['fnc_flash'] ) ? sanitize_text_field( wp_unslash( $_GET['fnc_flash'] ) ) : '';
	if ( '' === $token ) {
		return null;
	}
	$flash = get_transient( 'fnc_flash_' . $token );
	delete_transient( 'fnc_flash_' . $token );
	if ( ! is_array( $flash ) || ( isset( $flash['type'] ) && $flash['type'] !== $type ) ) {
		return null;
	}
	return $flash;
}

function fnc_field_error( $flash, $key ) {
	return ( $flash && ! empty( $flash['errors'][ $key ] ) ) ? $flash['errors'][ $key ] : '';
}

function fnc_old( $flash, $key ) {
	return ( $flash && isset( $flash['old'][ $key ] ) ) ? $flash['old'][ $key ] : '';
}

function fnc_error_text( $code ) {
	$en  = ( 0 === strpos( get_locale(), 'en' ) );
	$map = array(
		'required'  => $en ? 'This field is required.' : 'Ce champ est requis.',
		'invalid'   => $en ? 'Invalid e-mail address.' : 'Adresse e-mail invalide.',
		'too_long'  => $en ? 'This value is too long.' : 'Cette valeur est trop longue.',
	);
	return isset( $map[ $code ] ) ? $map[ $code ] : ( $en ? 'Invalid value.' : 'Valeur invalide.' );
}

/** Rend la bannière succès/erreur (role status/alert), classes DA-D .banner .ok/.ko. */
function fnc_submission_banner( $type, $flash = null ) {
	$en = ( 0 === strpos( get_locale(), 'en' ) );

	// Succès.
	if ( isset( $_GET['fnc_sent'] ) && sanitize_key( wp_unslash( $_GET['fnc_sent'] ) ) === $type ) {
		$t = $en ? 'Message received' : 'Message bien reçu';
		$b = $en ? 'We will get back to you shortly. Nothing is lost.' : 'Nous reviendrons vers vous rapidement. Aucune donnée n’est perdue.';
		return sprintf( '<div class="banner ok" role="status"><b>%s</b><p>%s</p></div>', esc_html( $t ), esc_html( $b ) );
	}

	// Erreur.
	if ( $flash && ! empty( $flash['flags']['_form'] ) ) {
		$code = $flash['flags']['_form'];
		if ( 'rate' === $code ) {
			$b = $en ? 'Too many attempts. Please try again in a few minutes.' : 'Trop de tentatives. Réessayez dans quelques minutes.';
		} elseif ( 'validation' === $code ) {
			$b = $en ? 'Some fields need your attention.' : 'Certains champs demandent votre attention.';
		} else {
			$b = $en ? 'Sending is temporarily unavailable. Please try again later.' : 'L’envoi est momentanément indisponible. Réessayez plus tard.';
		}
		$t = $en ? 'Unable to send' : 'Envoi impossible';
		return sprintf( '<div class="banner ko" role="alert"><b>%s</b><p>%s</p></div>', esc_html( $t ), esc_html( $b ) );
	}

	return '';
}
