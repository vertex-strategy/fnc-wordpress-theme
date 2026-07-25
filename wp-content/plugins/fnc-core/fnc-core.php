<?php
/**
 * Plugin Name: FNC Core
 * Description: Logique métier du Forum Numérique Congo (réglages, réception des
 *              formulaires, données dérivées, données structurées schema.org,
 *              consentement + Matomo, drapeaux de fonctionnalité). Survit à un
 *              changement de thème. Chargeur des 6 modules de docs/design/plugin.
 * Version: 0.1.0
 * Author: FNC
 * Requires PHP: 7.4
 *
 * INSTALLATION (option A d'INSTALL.md) :
 *   wp-content/plugins/fnc-core/
 *   ├── fnc-core.php          ← ce fichier
 *   └── modules/
 *       ├── fnc-settings.php        (B)
 *       ├── fnc-submissions.php     (A)
 *       ├── fnc-derived-data.php    (C)
 *       ├── fnc-structured-data.php (D)
 *       ├── fnc-consent-matomo.php  (E)
 *       └── fnc-feature-flags.php   (F)
 * Puis : Extensions → Activer « FNC Core ». Prérequis : plugin fnc-content-model actif.
 *
 * Les en-têtes « Plugin Name » des modules deviennent alors de simples docblocks
 * (ils ne sont plus scannés comme des plugins distincts, car requis à la main ici).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_CORE_VERSION', '0.1.0' );
define( 'FNC_CORE_DIR', plugin_dir_path( __FILE__ ) );

/*
 * Ordre logique : B (accesseurs) avant ses consommateurs. Non critique en soi —
 * tout est function_exists-guardé et appelé au runtime (hooks) — mais reste clair.
 */
$fnc_core_modules = array(
	'fnc-settings.php',        // B — réglages + accesseurs (fnc_get_setting, …)
	'fnc-submissions.php',     // A — réception des 3 formulaires
	'fnc-derived-data.php',    // C — édition, participants, agenda, facettes, RÈGLE 7
	'fnc-structured-data.php', // D — schema.org (consomme B + C)
	'fnc-consent-matomo.php',  // E — consentement + Matomo
	'fnc-feature-flags.php',   // F — drapeaux (consomme le seam du Module A)
	'fnc-page-archetypes.php', // G — edition des pages (archetypes, hero override, blocs) ; ACF Pro
);

$fnc_core_missing = array();
foreach ( $fnc_core_modules as $fnc_core_module ) {
	$fnc_core_path = FNC_CORE_DIR . 'modules/' . $fnc_core_module;
	if ( is_readable( $fnc_core_path ) ) {
		require_once $fnc_core_path;
	} else {
		$fnc_core_missing[] = $fnc_core_module;
	}
}
unset( $fnc_core_module, $fnc_core_path );

/*
 * Avertissements d'admin NON bloquants : un module introuvable, ou la dépendance
 * fnc-content-model inactive (les CPT n'existent pas → C/D restent vides).
 */
add_action( 'admin_notices', function () use ( $fnc_core_missing ) {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	if ( ! empty( $fnc_core_missing ) ) {
		printf(
			'<div class="notice notice-error"><p><strong>FNC Core :</strong> modules introuvables dans <code>/modules/</code> : %s</p></div>',
			esc_html( implode( ', ', $fnc_core_missing ) )
		);
	}
	if ( ! post_type_exists( 'fnc_edition' ) ) {
		echo '<div class="notice notice-warning"><p><strong>FNC Core :</strong> le plugin « fnc-content-model » semble inactif — les modules Données dérivées (C) et Données structurées (D) resteront vides tant que les types de contenu ne sont pas enregistrés.</p></div>';
	}
} );
