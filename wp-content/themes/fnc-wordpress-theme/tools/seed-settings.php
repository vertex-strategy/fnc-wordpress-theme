<?php
/**
 * Forum Numérique Congo — semis des réglages du site (coordonnées + réseaux).
 *
 * Renseigne l'option `fnc_settings` : coordonnées publiques réelles et liens de
 * réseaux sociaux de DÉMONSTRATION (pour que les icônes du pied de page
 * s'affichent comme dans la charte). Les comptes réels n'étant pas fournis, ces
 * liens pointent vers le site officiel — à remplacer par les vrais profils dans
 * Réglages → FNC → Réseaux sociaux. Idempotent : ne remplit que les champs vides
 * (sauf argument « force »).
 *
 * Exécution :  wp eval-file wp-content/themes/fnc-wordpress-theme/tools/seed-settings.php [force]
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fnc_force = isset( $args ) && is_array( $args ) && in_array( 'force', $args, true );

$s = get_option( 'fnc_settings', array() );
if ( ! is_array( $s ) ) {
	$s = array();
}

$set = function ( $key, $value ) use ( &$s, $fnc_force ) {
	if ( $fnc_force || empty( $s[ $key ] ) ) {
		$s[ $key ] = $value;
	}
};

// Coordonnées publiques (alignées sur le site du Forum).
$set( 'email', 'contact@forum-numerique-congo.cg' );
$set( 'address', 'Brazzaville, République du Congo' );
$set( 'phone', '+242 06 665 19 04' );

// Réseaux sociaux — DÉMONSTRATION : icônes visibles, liens vers le site officiel.
// Remplacer par les profils réels une fois fournis.
$site = 'https://forumnumeriquecongo.net';
$set( 'social', array(
	array( 'platform' => 'linkedin', 'label' => 'LinkedIn', 'url' => $site ),
	array( 'platform' => 'facebook', 'label' => 'Facebook', 'url' => $site ),
	array( 'platform' => 'x', 'label' => 'X', 'url' => $site ),
	array( 'platform' => 'youtube', 'label' => 'YouTube', 'url' => $site ),
) );

update_option( 'fnc_settings', $s );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::log( 'Réglages semés : adresse + téléphone + ' . count( $s['social'] ) . ' réseaux (démo → site officiel).' );
} else {
	echo "Réglages semés.\n";
}
