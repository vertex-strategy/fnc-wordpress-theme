<?php
/**
 * Forum Numérique Congo — importateur de contenu de démonstration (« starter kit »).
 *
 * Ajoute, sous « Apparence », une page « Contenu de démonstration » avec un
 * bouton qui installe en un clic le jeu complet de données de démonstration
 * (éditions, intervenants, sessions, partenaires, publications), bilingue
 * FR + EN si Polylang est actif, avec les vraies images. C'est l'équivalent du
 * « one-click demo import » des thèmes premium : il permet au client de voir
 * immédiatement la structure du site.
 *
 * Le contenu provient de tools/dataset.json et l'import réutilise le semis
 * testé (tools/seed-dataset.php → fnc_ds_run_seed()). L'opération est
 * IDEMPOTENTE (clé stable _fnc_seed_legacy) : la relancer met à jour, ne
 * duplique jamais.
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
 * Ajoute la page sous le menu « Apparence ».
 */
function fnc_demo_import_menu() {
	add_theme_page(
		__( 'Contenu de démonstration', 'fnc-wordpress-theme' ),
		__( 'Contenu de démonstration', 'fnc-wordpress-theme' ),
		'manage_options',
		'fnc-demo-import',
		'fnc_demo_import_render_page'
	);
}
add_action( 'admin_menu', 'fnc_demo_import_menu' );

/**
 * Purge les caches sensibles après l'import (données dérivées : participants,
 * pays, facettes). Le Module C s'appuie sur le cache objet ; on vide aussi les
 * éventuels transients préfixés « fnc ».
 */
function fnc_demo_import_flush_caches() {
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_fnc\\_%' OR option_name LIKE '\\_transient\\_timeout\\_fnc\\_%'" ); // phpcs:ignore WordPress.DB
}

/**
 * Rendu de la page + traitement de l'import.
 */
function fnc_demo_import_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$fnc_log     = array();
	$fnc_done    = false;
	$fnc_error   = '';
	$fnc_has_cpt = post_type_exists( 'fnc_intervenant' );
	$fnc_has_pll = function_exists( 'pll_set_post_language' );

	if ( isset( $_POST['fnc_demo_import'] )
		&& check_admin_referer( 'fnc_demo_import', 'fnc_demo_import_nonce' )
	) {
		if ( ! $fnc_has_cpt ) {
			$fnc_error = __( 'L’extension « FNC — Modèle de contenu » doit être active avant l’import (elle déclare les types de contenu).', 'fnc-wordpress-theme' );
		} else {
			$fnc_seed = get_template_directory() . '/tools/seed-dataset.php';
			if ( ! is_readable( $fnc_seed ) ) {
				$fnc_error = __( 'Fichier de semis introuvable (tools/seed-dataset.php).', 'fnc-wordpress-theme' );
			} else {
				require_once $fnc_seed;
				if ( ! function_exists( 'fnc_ds_run_seed' ) ) {
					$fnc_error = __( 'Fonction de semis indisponible.', 'fnc-wordpress-theme' );
				} else {
					if ( function_exists( 'set_time_limit' ) ) {
						set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
					}
					$fnc_log  = (array) fnc_ds_run_seed();
					fnc_demo_import_flush_caches();
					$fnc_done = true;
				}
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Contenu de démonstration', 'fnc-wordpress-theme' ); ?></h1>

		<?php if ( $fnc_error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $fnc_error ); ?></p></div>
		<?php endif; ?>

		<?php if ( $fnc_done ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Import terminé.', 'fnc-wordpress-theme' ); ?></strong> <?php esc_html_e( 'Le contenu de démonstration a été installé. Consultez la page d’accueil pour voir la structure.', 'fnc-wordpress-theme' ); ?></p></div>
			<?php if ( ! empty( $fnc_log ) ) : ?>
				<h2><?php esc_html_e( 'Journal', 'fnc-wordpress-theme' ); ?></h2>
				<pre style="max-height:340px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:12px;white-space:pre-wrap;"><?php echo esc_html( implode( "\n", $fnc_log ) ); ?></pre>
			<?php endif; ?>
		<?php endif; ?>

		<p><?php esc_html_e( 'Installe en un clic le jeu de données de démonstration du Forum Numérique Congo — éditions, intervenants, sessions, partenaires et publications, avec leurs images et leurs relations. Idéal pour découvrir la structure du site après une installation neuve.', 'fnc-wordpress-theme' ); ?></p>

		<h2><?php esc_html_e( 'Avant de lancer', 'fnc-wordpress-theme' ); ?></h2>
		<ul style="list-style:disc;margin-left:20px;">
			<li>
				<?php if ( $fnc_has_cpt ) : ?>
					<span style="color:#008a00;">✔</span> <?php esc_html_e( 'Extension « FNC — Modèle de contenu » active.', 'fnc-wordpress-theme' ); ?>
				<?php else : ?>
					<span style="color:#d63638;">✘</span> <?php esc_html_e( 'Activez d’abord l’extension « FNC — Modèle de contenu » (requise).', 'fnc-wordpress-theme' ); ?>
				<?php endif; ?>
			</li>
			<li>
				<?php if ( $fnc_has_pll ) : ?>
					<span style="color:#008a00;">✔</span> <?php esc_html_e( 'Polylang actif : le contenu sera importé en français ET en anglais (traductions liées).', 'fnc-wordpress-theme' ); ?>
				<?php else : ?>
					<span style="color:#dba617;">●</span> <?php esc_html_e( 'Polylang inactif : le contenu sera importé en français uniquement. Activez Polylang (langues fr + en) avant l’import pour obtenir la version bilingue.', 'fnc-wordpress-theme' ); ?>
				<?php endif; ?>
			</li>
			<li><span style="color:#008a00;">✔</span> <?php esc_html_e( 'Opération sûre et rejouable : relancer met à jour le contenu de démonstration, sans jamais créer de doublon.', 'fnc-wordpress-theme' ); ?></li>
		</ul>

		<p class="description"><?php esc_html_e( 'L’import peut prendre jusqu’à une minute (import des images). Ne fermez pas la page pendant l’opération.', 'fnc-wordpress-theme' ); ?></p>

		<form method="post" style="margin-top:16px;">
			<?php wp_nonce_field( 'fnc_demo_import', 'fnc_demo_import_nonce' ); ?>
			<button type="submit" name="fnc_demo_import" value="1" class="button button-primary button-hero"<?php disabled( ! $fnc_has_cpt ); ?>>
				<?php echo $fnc_done ? esc_html__( 'Relancer l’import', 'fnc-wordpress-theme' ) : esc_html__( 'Importer le contenu de démonstration', 'fnc-wordpress-theme' ); ?>
			</button>
		</form>
	</div>
	<?php
}
