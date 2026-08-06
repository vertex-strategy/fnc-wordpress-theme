<?php
/**
 * Plugin Name: FNC Content Model
 * Plugin URI: https://www.grinso.io
 * Description: Modèle de contenu du Forum Numérique Congo (types de contenu, taxonomies
 *              et relations : éditions, intervenants, sessions, publications, partenaires,
 *              actualités). Survit à un changement de thème. Sans dépendance tierce, hors
 *              multilinguisme assuré séparément par Polylang.
 * Version: 1.0.14
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Text Domain: fnc-content-model
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FNC_CONTENT_MODEL_VERSION', '1.0.14' );
define( 'FNC_CONTENT_MODEL_DIR', plugin_dir_path( __FILE__ ) );

require_once FNC_CONTENT_MODEL_DIR . 'includes/post-types.php';
require_once FNC_CONTENT_MODEL_DIR . 'includes/taxonomies.php';
require_once FNC_CONTENT_MODEL_DIR . 'includes/relations.php';
require_once FNC_CONTENT_MODEL_DIR . 'includes/statuses.php';
require_once FNC_CONTENT_MODEL_DIR . 'includes/polylang.php';

/**
 * Chargement des traductions du plugin.
 *
 * Meme approche que le theme (voir functions.php) : on cible directement le
 * .mo par locale, sur `init` (admin/repli) et `wp` (front, apres que Polylang
 * a fixe la langue de la requete). La langue source est le francais, sans .mo :
 * on ne charge que si un fichier existe pour la locale courante, sinon on
 * decharge pour revenir aux chaines sources.
 */
function fnc_content_model_load_textdomain() {
	$locale = determine_locale();
	$mofile = FNC_CONTENT_MODEL_DIR . 'languages/fnc-content-model-' . $locale . '.mo';

	unload_textdomain( 'fnc-content-model' );
	if ( is_readable( $mofile ) ) {
		load_textdomain( 'fnc-content-model', $mofile, $locale );
	}
}
add_action( 'init', 'fnc_content_model_load_textdomain', 1 );
add_action( 'wp', 'fnc_content_model_load_textdomain' );

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
