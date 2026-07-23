<?php
/**
 * Gabarit de page — "Contact".
 *
 * Applique automatiquement (hierarchie de templates WordPress) a toute
 * Page dont le slug est "contact". Porte docs/mockups/homepage-v2/contact.html,
 * dont le contenu reel est genere par site.js (contactPage(), copy.contact).
 * Ce fichier n'etait pas committe sur la branche de l'ADR-007 au moment de
 * l'integration (voir note pour page-le-forum.php) — utilise a la demande
 * explicite du Decideur.
 *
 * Formulaire non fonctionnel (pas de handler d'envoi) : fidele a la
 * maquette source, qui indique explicitement "Le service d'envoi reste
 * a connecter." Aucune coordonnee officielle inventee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Contact', 'fnc-wordpress-theme' ),
		'title'      => __( 'Orienter chaque demande vers le bon canal.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Contact général, presse, partenariat et accessibilité restent à confirmer.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-badge.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Contact', 'fnc-wordpress-theme' ),
	)
);
?>

<main id="main">
	<section class="section">
		<div class="container grid grid-2">
			<form class="card form" aria-label="<?php esc_attr_e( 'Formulaire de contact', 'fnc-wordpress-theme' ); ?>">
				<p class="card-kicker"><?php esc_html_e( 'Formulaire', 'fnc-wordpress-theme' ); ?></p>
				<h2 style="font-size:2rem;color:var(--navy)"><?php esc_html_e( 'Formulaire de contact', 'fnc-wordpress-theme' ); ?></h2>
				<p class="help"><?php esc_html_e( 'Le service d’envoi reste à connecter.', 'fnc-wordpress-theme' ); ?></p>
				<div class="form-grid">
					<?php
					$fnc_contact_fields = array(
						array( 'label' => __( 'Nom', 'fnc-wordpress-theme' ), 'type' => 'text' ),
						array( 'label' => __( 'Organisation', 'fnc-wordpress-theme' ), 'type' => 'text' ),
						array( 'label' => __( 'Email', 'fnc-wordpress-theme' ), 'type' => 'email' ),
						array( 'label' => __( 'Sujet', 'fnc-wordpress-theme' ), 'type' => 'text' ),
						array( 'label' => __( 'Message', 'fnc-wordpress-theme' ), 'type' => 'textarea' ),
					);
					foreach ( $fnc_contact_fields as $fnc_i => $fnc_field ) :
						$fnc_field_id = 'fnc-contact-field-' . $fnc_i;
						?>
						<div class="field <?php echo 'textarea' === $fnc_field['type'] ? 'full' : ''; ?>">
							<label for="<?php echo esc_attr( $fnc_field_id ); ?>"><?php echo esc_html( $fnc_field['label'] ); ?></label>
							<?php if ( 'textarea' === $fnc_field['type'] ) : ?>
								<textarea id="<?php echo esc_attr( $fnc_field_id ); ?>" placeholder="<?php esc_attr_e( 'À confirmer', 'fnc-wordpress-theme' ); ?>"></textarea>
							<?php else : ?>
								<input id="<?php echo esc_attr( $fnc_field_id ); ?>" type="<?php echo esc_attr( $fnc_field['type'] ); ?>" placeholder="<?php esc_attr_e( 'À confirmer', 'fnc-wordpress-theme' ); ?>" />
							<?php endif; ?>
							<span class="help"><?php esc_html_e( 'Champ requis.', 'fnc-wordpress-theme' ); ?></span>
						</div>
						<?php
					endforeach;
					?>
				</div>
				<div class="notice"><?php esc_html_e( 'Les coordonnées officielles restent à confirmer avant publication.', 'fnc-wordpress-theme' ); ?></div>
				<button class="btn btn-red" type="button"><?php esc_html_e( 'Envoyer', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
			<div class="grid">
				<?php
				fnc_render_card( __( 'Institutionnel', 'fnc-wordpress-theme' ), __( 'Email officiel à confirmer', 'fnc-wordpress-theme' ) );
				fnc_render_card( __( 'Presse', 'fnc-wordpress-theme' ), __( 'Contact presse à confirmer', 'fnc-wordpress-theme' ) );
				fnc_render_card( __( 'Accessibilité', 'fnc-wordpress-theme' ), __( 'Contact dédié à confirmer', 'fnc-wordpress-theme' ) );
				?>
			</div>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
