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
			<b><?php echo esc_html( fnc_site_name() ); ?></b>
			<p><?php echo esc_html( fnc_get_setting( 'footer_text', __( 'Institution permanente de réflexion sur l’avenir numérique de l’Afrique centrale. Brazzaville.', 'fnc-wordpress-theme' ) ) ); ?></p>
			<?php
			// Coordonnées : affichées uniquement si renseignées dans les Réglages
			// FNC (RÈGLE 4 — aucune coordonnée fictive).
			$fnc_email   = fnc_get_setting( 'email', '' );
			$fnc_phone   = fnc_get_setting( 'phone', '' );
			$fnc_address = fnc_get_setting( 'address', '' );
			if ( $fnc_address || $fnc_email || $fnc_phone ) :
				?>
				<ul class="foot-contact">
					<?php if ( $fnc_address ) : ?>
						<li><?php echo nl2br( esc_html( $fnc_address ) ); ?></li>
					<?php endif; ?>
					<?php if ( $fnc_email ) : ?>
						<li><a href="mailto:<?php echo esc_attr( antispambot( $fnc_email ) ); ?>"><?php echo esc_html( antispambot( $fnc_email ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( $fnc_phone ) : ?>
						<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $fnc_phone ) ); ?>"><?php echo esc_html( $fnc_phone ); ?></a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
			<?php
			$fnc_social = fnc_social_links();
			if ( ! empty( $fnc_social ) ) :
				?>
				<ul class="foot-social" aria-label="<?php esc_attr_e( 'Réseaux sociaux', 'fnc-wordpress-theme' ); ?>">
					<?php foreach ( $fnc_social as $fnc_platform => $fnc_url ) : ?>
						<li><a href="<?php echo esc_url( $fnc_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( fnc_social_label( $fnc_platform ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
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
		<?php
		// Copyright éditable (Réglages FNC → Footer) ; l'année est ajoutée
		// automatiquement. Vide → nom officiel du site.
		$fnc_copyright_name = fnc_get_setting( 'footer_copyright', '' );
		$fnc_copyright_name = $fnc_copyright_name ? $fnc_copyright_name : fnc_site_name();
		?>
		<span><?php printf( esc_html__( '© %1$s %2$s', 'fnc-wordpress-theme' ), esc_html( gmdate( 'Y' ) ), esc_html( $fnc_copyright_name ) ); ?></span>
		<span>
			<a href="<?php echo esc_url( fnc_page_url( 'mentions-legales' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'fnc-wordpress-theme' ); ?></a>
			· <a href="<?php echo esc_url( fnc_page_url( 'politique-confidentialite' ) ); ?>"><?php esc_html_e( 'Confidentialité', 'fnc-wordpress-theme' ); ?></a>
		</span>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
