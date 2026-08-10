<?php
/**
 * Forum Numérique Congo — pied de page (liens, réseaux, mentions, réouverture du consentement).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="footer" class="dad-footer">
	<div class="foot-grid">
		<div class="foot-brand">
			<a class="foot-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s — accueil', 'fnc-wordpress-theme' ), fnc_site_name() ) ); ?>">
				<?php echo fnc_footer_logo_img(); // phpcs:ignore WordPress.Security.EscapeOutput -- markup construit et échappé par fnc_footer_logo_img(). ?>
			</a>
			<p><?php echo esc_html( fnc_get_setting_i18n( 'footer_text', __( 'Institution permanente de réflexion sur l’avenir numérique de l’Afrique centrale. Brazzaville.', 'fnc-wordpress-theme' ) ) ); ?></p>
		</div>
		<?php
		// Groupes de liens éditables (Personnaliser → FNC → Footer). Réglage vide
		// → colonnes par défaut du thème (jamais de pied de page sans navigation).
		// Les coordonnées et les réseaux restent rattachés à la DERNIÈRE colonne
		// (fidèle à l'ancienne colonne « Contact »).
		$fnc_columns = function_exists( 'fnc_footer_columns' ) ? fnc_footer_columns() : array();
		$fnc_last    = count( $fnc_columns ) - 1;
		foreach ( $fnc_columns as $fnc_i => $fnc_col ) :
			?>
			<div>
				<?php if ( '' !== $fnc_col['title'] ) : ?>
					<h4><?php echo esc_html( $fnc_col['title'] ); ?></h4>
				<?php endif; ?>
				<?php if ( ! empty( $fnc_col['links'] ) ) : ?>
					<ul>
						<?php
						foreach ( $fnc_col['links'] as $fnc_link ) :
							$fnc_new = ! empty( $fnc_link['openInNewTab'] ) || ! empty( $fnc_link['external'] );
							?>
							<li><a href="<?php echo esc_url( $fnc_link['href'] ); ?>"<?php
							if ( $fnc_new ) {
								echo ' target="_blank" rel="noopener noreferrer"';
							}
							if ( ! empty( $fnc_link['ariaLabel'] ) ) {
								printf( ' aria-label="%s"', esc_attr( $fnc_link['ariaLabel'] ) );
							}
							?>><?php echo esc_html( $fnc_link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $fnc_i === $fnc_last ) : ?>
					<?php
					// Coordonnées publiques (affichées seulement si renseignées, RÈGLE 4).
					// Accesseurs « publics » : écartent les valeurs de démonstration (e-mail
					// invalide, etc.), comme les filtres anti-fictif du site du Forum.
					$fnc_email   = function_exists( 'fnc_public_email' ) ? fnc_public_email() : fnc_get_setting( 'email', '' );
					$fnc_address = function_exists( 'fnc_public_address' ) ? fnc_public_address() : fnc_get_setting_i18n( 'address', '' );
					if ( $fnc_email || $fnc_address ) :
						?>
						<ul class="foot-contact">
							<?php if ( $fnc_email ) : ?>
								<li><a href="mailto:<?php echo esc_attr( antispambot( $fnc_email ) ); ?>"><?php echo esc_html( antispambot( $fnc_email ) ); ?></a></li>
							<?php endif; ?>
							<?php if ( $fnc_address ) : ?>
								<li><?php echo nl2br( esc_html( $fnc_address ) ); ?></li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
					<?php
					// Réseaux sociaux : boutons-icônes, en bas de la dernière colonne
					// (même disposition que le site du Forum).
					$fnc_social = fnc_social_links();
					if ( ! empty( $fnc_social ) ) :
						?>
						<div class="social-row" aria-label="<?php esc_attr_e( 'Réseaux sociaux', 'fnc-wordpress-theme' ); ?>">
							<?php foreach ( $fnc_social as $fnc_s ) :
									$fnc_platform = isset( $fnc_s['platform'] ) ? $fnc_s['platform'] : 'other';
									$fnc_url      = isset( $fnc_s['url'] ) ? $fnc_s['url'] : '';
									$fnc_label    = ( isset( $fnc_s['label'] ) && '' !== $fnc_s['label'] ) ? $fnc_s['label'] : fnc_social_label( $fnc_platform );
									?>
								<a class="social-chip social-chip--icon" href="<?php echo esc_url( $fnc_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( sprintf( '%s, %s', $fnc_label, __( 's’ouvre dans un nouvel onglet', 'fnc-wordpress-theme' ) ) ); ?>">
									<?php echo fnc_social_icon( $fnc_platform ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG statique interne (fnc_social_icon). ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="foot-bottom">
		<?php
		// Copyright éditable (Réglages FNC → Footer) ; l'année est ajoutée
		// automatiquement. Vide → nom officiel du site.
		$fnc_copyright_name = fnc_get_setting_i18n( 'footer_copyright', '' );
		$fnc_copyright_name = $fnc_copyright_name ? $fnc_copyright_name : fnc_site_name();
		?>
		<span><?php printf( esc_html__( '© %1$s %2$s', 'fnc-wordpress-theme' ), esc_html( gmdate( 'Y' ) ), esc_html( $fnc_copyright_name ) ); ?></span>
		<span>
			<a href="<?php echo esc_url( fnc_page_url( 'mentions-legales' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'fnc-wordpress-theme' ); ?></a>
			· <a href="<?php echo esc_url( fnc_page_url( 'politique-confidentialite' ) ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'fnc-wordpress-theme' ); ?></a>
			<?php
			// Bouton de reouverture du consentement (le consentement) : visible seulement
			// si la mesure d'audience est active. Le separateur ne s'affiche que si le
			// bouton existe (sinon « · » orphelin quand la mesure est desactivee).
			if ( function_exists( 'fnc_consent_reopen_button' ) ) {
				$fnc_reopen = fnc_consent_reopen_button();
				if ( '' !== trim( (string) $fnc_reopen ) ) {
					echo ' · ' . $fnc_reopen; // phpcs:ignore WordPress.Security.EscapeOutput -- markup interne echappe.
				}
			}
			?>
		</span>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
