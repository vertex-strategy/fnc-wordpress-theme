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
				<li><a href="<?php echo esc_url( fnc_page_url( 'le-forum' ) ); ?>"><?php esc_html_e( 'Présentation', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_page_url( 'edition-en-cours' ) ); ?>"><?php esc_html_e( 'Édition en cours', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_archive_url( 'fnc_session' ) ); ?>"><?php esc_html_e( 'Programme', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_archive_url( 'fnc_intervenant' ) ); ?>"><?php esc_html_e( 'Intervenants', 'fnc-wordpress-theme' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( 'Ressources', 'fnc-wordpress-theme' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( fnc_archive_url( 'fnc_edition' ) ); ?>"><?php esc_html_e( 'Éditions', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_archive_url( 'fnc_publication' ) ); ?>"><?php esc_html_e( 'Ressources', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_page_url( 'informations-pratiques' ) ); ?>"><?php esc_html_e( 'Informations pratiques', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_page_url( 'espace-presse' ) ); ?>"><?php esc_html_e( 'Dossier presse', 'fnc-wordpress-theme' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( 'Contact', 'fnc-wordpress-theme' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( fnc_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_page_url( 'partenaires' ) ); ?>"><?php esc_html_e( 'Devenir partenaire', 'fnc-wordpress-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( fnc_page_url( 'inscription' ) ); ?>"><?php esc_html_e( 'S’inscrire', 'fnc-wordpress-theme' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="foot-bottom">
		<span><?php printf( esc_html__( '© %s %s — Coordonnées officielles', 'fnc-wordpress-theme' ), esc_html( gmdate( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) ); ?> <span class="tbc" style="color:#fff"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></span>
		<span>
			<a href="<?php echo esc_url( fnc_page_url( 'mentions-legales' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'fnc-wordpress-theme' ); ?></a>
			· <a href="<?php echo esc_url( fnc_page_url( 'politique-confidentialite' ) ); ?>"><?php esc_html_e( 'Confidentialité', 'fnc-wordpress-theme' ); ?></a>
		</span>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
