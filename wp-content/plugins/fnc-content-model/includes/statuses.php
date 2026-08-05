<?php
/**
 * Forum Numérique Congo — statut « Archivé » (3e état du modèle Next).
 *
 * Le modèle de référence distingue draft / published / archived. WordPress n'a
 * nativement que brouillon / publié / corbeille. On ajoute donc un statut
 * « archived » NON public : le contenu est retiré du site public mais conservé
 * (distinct de la corbeille). Il est exposé dans les menus de statut (éditeur
 * classique + Modification rapide de la liste) pour rester administrable.
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
 * Types de contenu pouvant être archivés (les CPT du modèle).
 *
 * @return array<int,string>
 */
function fnc_cm_archivable_types() {
	return array( 'fnc_edition', 'fnc_session', 'fnc_intervenant', 'fnc_partenaire', 'fnc_publication', 'fnc_actualite' );
}

/**
 * Enregistre le statut « archived » (non public → invisible côté public, exclu
 * de la recherche ; compté dans la liste d'administration).
 */
function fnc_cm_register_archived_status() {
	register_post_status(
		'archived',
		array(
			'label'                     => _x( 'Archivé', 'statut de contenu', 'fnc-content-model' ),
			'public'                    => false,
			'internal'                  => false,
			'protected'                 => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: nombre de contenus. */
			'label_count'               => _n_noop( 'Archivé <span class="count">(%s)</span>', 'Archivés <span class="count">(%s)</span>', 'fnc-content-model' ),
		)
	);
}
add_action( 'init', 'fnc_cm_register_archived_status' );

/**
 * Ajoute « Archivé » aux listes déroulantes de statut : éditeur classique
 * (panneau Publier) et Modification rapide de la liste. Ne s'affiche que sur les
 * écrans des CPT archivables.
 */
function fnc_cm_archived_status_ui() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, fnc_cm_archivable_types(), true ) ) {
		return;
	}
	global $post;
	$is_archived = ( $post && 'archived' === $post->post_status );
	$label       = esc_js( __( 'Archivé', 'fnc-content-model' ) );
	?>
	<script>
	( function ( $ ) {
		$( function () {
			var opt = '<option value="archived"' + ( <?php echo $is_archived ? 'true' : 'false'; ?> ? ' selected="selected"' : '' ) + '><?php echo $label; ?></option>';
			// Éditeur classique : menu #post_status + libellé affiché.
			var $sel = $( '#post_status' );
			if ( $sel.length && ! $sel.find( 'option[value="archived"]' ).length ) {
				$sel.append( opt );
				<?php if ( $is_archived ) : ?>
				$( '#post-status-display' ).text( '<?php echo $label; ?>' );
				<?php endif; ?>
			}
			// Modification rapide (liste) : select[name="_status"].
			$( 'select[name="_status"]' ).each( function () {
				if ( ! $( this ).find( 'option[value="archived"]' ).length ) {
					$( this ).append( '<option value="archived"><?php echo $label; ?></option>' );
				}
			} );
		} );
	} )( jQuery );
	</script>
	<?php
}
add_action( 'admin_footer-post.php', 'fnc_cm_archived_status_ui' );
add_action( 'admin_footer-post-new.php', 'fnc_cm_archived_status_ui' );
add_action( 'admin_footer-edit.php', 'fnc_cm_archived_status_ui' );
