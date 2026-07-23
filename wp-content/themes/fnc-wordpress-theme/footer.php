<?php
/**
 * Pied de page du theme.
 * Porte le footer de docs/mockups/homepage-v2/index.html (ADR-007).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="footer">
	<div class="foot-grid">
		<div class="foot-brand">
			<b><?php bloginfo( 'name' ); ?></b>
			<p><?php esc_html_e( 'Institution permanente de réflexion sur l’avenir numérique de l’Afrique centrale. Brazzaville.', 'fnc-wordpress-theme' ); ?></p>
		</div>
		<div>
			<h4><?php esc_html_e( 'Le Forum', 'fnc-wordpress-theme' ); ?></h4>
			<ul>
				<li><a href="#m2"><?php esc_html_e( 'Notre mission', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="#m3"><?php esc_html_e( 'Intervenants', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="#m5"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( 'Ressources', 'fnc-wordpress-theme' ); ?></h4>
			<ul>
				<li><a href="#m7"><?php esc_html_e( 'Éditions', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Publications', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Espace presse', 'fnc-wordpress-theme' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( 'Contact', 'fnc-wordpress-theme' ); ?></h4>
			<ul>
				<li><a href="#"><?php esc_html_e( 'Nous écrire', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Devenir partenaire', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Accréditation', 'fnc-wordpress-theme' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="foot-bottom">
		<span><?php printf( esc_html__( '© %s %s — Coordonnées officielles', 'fnc-wordpress-theme' ), esc_html( gmdate( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) ); ?> <span class="tbc" style="color:#fff"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></span>
		<span><?php esc_html_e( 'Mentions légales · Confidentialité', 'fnc-wordpress-theme' ); ?></span>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
