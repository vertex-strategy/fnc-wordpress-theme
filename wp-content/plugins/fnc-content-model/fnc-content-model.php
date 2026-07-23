<?php
/**
 * Plugin Name: FNC Content Model
 * Plugin URI: https://github.com/vertex-strategy/fnc-wordpress-theme
 * Description: Modele de contenu (custom post types, taxonomies, relations) reflete depuis les collections publiques Payload CMS du site officiel Forum Numerique Congo. Voir ADR-007 (depot forum-numerique-congo). Zero dependance tierce (ADR-007, Decision 2) — hors multilinguisme, assure separement par Polylang ou equivalent (Decision 2, arbitrage bilingue).
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Vertex Strategy
 * Text Domain: fnc-content-model
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_CONTENT_MODEL_VERSION', '0.2.0' );
define( 'FNC_CONTENT_MODEL_DIR', plugin_dir_path( __FILE__ ) );

require_once FNC_CONTENT_MODEL_DIR . 'includes/post-types.php';
require_once FNC_CONTENT_MODEL_DIR . 'includes/taxonomies.php';
require_once FNC_CONTENT_MODEL_DIR . 'includes/relations.php';

/**
 * Recharge les regles de reecriture d'URL a l'activation/desactivation,
 * necessaire des qu'on enregistre de nouveaux post types avec has_archive.
 */
function fnc_content_model_activate() {
	fnc_content_model_register_post_types();
	fnc_content_model_register_taxonomies();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'fnc_content_model_activate' );

function fnc_content_model_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'fnc_content_model_deactivate' );
