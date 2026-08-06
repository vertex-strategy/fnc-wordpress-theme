<?php
/**
 * Forum Numérique Congo — page « introuvable » (404).
 *
 * Localisée (FR/EN via le domaine de traduction) et non indexable (la directive
 * robots noindex est posée par inc/seo.php sur is_404()). Sert aussi d'état de
 * repli aux surfaces sous feature flag fermé (ex. actualités désactivées).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_pagehead(
	array(
		'eyebrow' => __( 'Erreur 404', 'fnc-wordpress-theme' ),
		'title'   => __( 'Page introuvable', 'fnc-wordpress-theme' ),
		'intro'   => __( 'La page demandée n’existe pas ou n’est plus disponible. Elle a peut-être été déplacée.', 'fnc-wordpress-theme' ),
	)
);
?>
<section class="section">
	<div class="container">
		<p><a class="btn btn-red" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Retour à l’accueil', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a></p>
		<p style="margin-top:24px;">
			<?php
			printf(
				/* translators: 1: intervenants archive link, 2: programme archive link. */
				esc_html__( 'Vous cherchiez peut-être les %1$s ou le %2$s ?', 'fnc-wordpress-theme' ),
				'<a class="link-more" href="' . esc_url( fnc_archive_url( 'fnc_intervenant' ) ) . '">' . esc_html__( 'intervenants', 'fnc-wordpress-theme' ) . '</a>',
				'<a class="link-more" href="' . esc_url( fnc_archive_url( 'fnc_session' ) ) . '">' . esc_html__( 'programme', 'fnc-wordpress-theme' ) . '</a>'
			);
			?>
		</p>
	</div>
</section>
<?php
get_footer();
