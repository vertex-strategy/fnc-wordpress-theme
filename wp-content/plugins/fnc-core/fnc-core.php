<?php
/**
 * Plugin Name: FNC Core
 * Description: Logique métier du Forum Numérique Congo (réglages, réception des
 *              formulaires, données du site, données structurées SEO, consentement &
 *              mesure d'audience, options d'affichage, édition des pages). Survit à un
 *              changement de thème.
 * Version: 1.0.49
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Requires PHP: 7.4
 * Text Domain: fnc
 * Domain Path: /languages
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INSTALLATION (option A d'INSTALL.md) :
 *   wp-content/plugins/fnc-core/
 *   ├── fnc-core.php          ← ce fichier
 *   └── modules/
 *       ├── fnc-settings.php         (réglages du site)
 *       ├── fnc-submissions.php      (réception des formulaires)
 *       ├── fnc-derived-data.php     (données du site)
 *       ├── fnc-structured-data.php  (données structurées SEO)
 *       ├── fnc-consent-matomo.php   (consentement & mesure d'audience)
 *       ├── fnc-feature-flags.php    (options d'affichage)
 *       └── fnc-page-archetypes.php  (édition des pages)
 * Puis : Extensions → Activer « FNC Core ». Prérequis : plugin fnc-content-model actif.
 *
 * Les en-têtes « Plugin Name » des modules deviennent alors de simples docblocks
 * (ils ne sont plus scannés comme des plugins distincts, car requis à la main ici).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_CORE_VERSION', '1.0.49' );
define( 'FNC_CORE_DIR', plugin_dir_path( __FILE__ ) );

/*
 * i18n : chargement du domaine de traduction « fnc » (utilisé par les chaînes admin des
 * modules). Hook « init » conforme aux recommandations WP 6.7+. Le dossier /languages
 * peut être vide aujourd'hui — le chargement reste inoffensif et prêt pour les .mo à venir.
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'fnc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

/*
 * Ordre logique : les réglages (accesseurs) avant leurs consommateurs. Non critique en
 * soi — tout est function_exists-guardé et appelé au runtime (hooks) — mais reste clair.
 */
$fnc_core_modules = array(
	'fnc-settings.php',        // Réglages du site + accesseurs (fnc_get_setting, …)
	'fnc-submissions.php',     // Réception des formulaires (contact / inscription / partenariat)
	'fnc-derived-data.php',    // Données du site (édition, programme, filtres, droit à l'image)
	'fnc-structured-data.php', // Données structurées (SEO)
	'fnc-consent-matomo.php',  // Consentement & mesure d'audience
	'fnc-feature-flags.php',   // Options d'affichage (inscriptions, actualités)
	'fnc-page-archetypes.php', // Édition des pages (archétypes, champs)
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
