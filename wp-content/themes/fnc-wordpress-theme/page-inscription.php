<?php
/**
 * Gabarit de page — "Inscription".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/inscription), suite a l'amendement de la
 * Decision 1 de l'ADR-007. Formulaire non fonctionnel — fidele a la
 * maquette source (site.js, copy.registration : "Le formulaire reste
 * un exemple statique") et a l'exclusion explicite de la collection
 * "Registrations" du perimetre du plugin (ADR-007, Decision 2).
 *
 * Seule partie dynamique : le nom de l'edition a laquelle on
 * "s'inscrit" est celui de l'edition active reelle
 * (_fnc_edition_active), pour rester coherent avec
 * page-edition-en-cours.php sans stocker la moindre inscription.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Participer', 'fnc-wordpress-theme' ),
		'title'      => __( 'Demander une inscription.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Adressez votre demande de participation à la prochaine édition.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/le-badge.png',
		'image_alt'  => __( 'Image éditoriale institutionnelle du Forum', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Inscription', 'fnc-wordpress-theme' ),
	)
);

$fnc_active_edition = get_posts(
	array(
		'post_type'      => 'fnc_edition',
		'posts_per_page' => 1,
		'meta_key'       => '_fnc_edition_active',
		'meta_value'     => '1',
	)
);
$fnc_edition_title = ! empty( $fnc_active_edition ) ? get_the_title( $fnc_active_edition[0] ) : __( 'Édition à confirmer', 'fnc-wordpress-theme' );
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<h2><?php esc_html_e( 'Votre demande', 'fnc-wordpress-theme' ); ?></h2>
			<p class="help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires.', 'fnc-wordpress-theme' ); ?></p>

			<form class="card form" aria-label="<?php esc_attr_e( 'Formulaire d’inscription', 'fnc-wordpress-theme' ); ?>" style="margin-top:20px;">
				<p class="help"><?php esc_html_e( 'Vous vous inscrivez à', 'fnc-wordpress-theme' ); ?> <strong><?php echo esc_html( $fnc_edition_title ); ?></strong></p>
				<div class="form-grid">
					<?php
					$fnc_fields = array(
						array( 'label' => __( 'Nom complet *', 'fnc-wordpress-theme' ), 'type' => 'text' ),
						array( 'label' => __( 'E-mail *', 'fnc-wordpress-theme' ), 'type' => 'email' ),
						array( 'label' => __( 'Organisation', 'fnc-wordpress-theme' ), 'type' => 'text' ),
						array( 'label' => __( 'Mode de participation', 'fnc-wordpress-theme' ), 'type' => 'select', 'options' => array( __( 'Présentiel', 'fnc-wordpress-theme' ), __( 'En ligne', 'fnc-wordpress-theme' ) ) ),
						array( 'label' => __( 'Motivation (facultatif)', 'fnc-wordpress-theme' ), 'type' => 'textarea' ),
					);
					foreach ( $fnc_fields as $fnc_i => $fnc_field ) :
						$fnc_field_id = 'fnc-registration-field-' . $fnc_i;
						?>
						<div class="field <?php echo 'textarea' === $fnc_field['type'] ? 'full' : ''; ?>">
							<label for="<?php echo esc_attr( $fnc_field_id ); ?>"><?php echo esc_html( $fnc_field['label'] ); ?></label>
							<?php if ( 'textarea' === $fnc_field['type'] ) : ?>
								<textarea id="<?php echo esc_attr( $fnc_field_id ); ?>" placeholder="<?php esc_attr_e( 'À confirmer', 'fnc-wordpress-theme' ); ?>"></textarea>
							<?php elseif ( 'select' === $fnc_field['type'] ) : ?>
								<select id="<?php echo esc_attr( $fnc_field_id ); ?>">
									<?php foreach ( $fnc_field['options'] as $fnc_option ) : ?>
										<option><?php echo esc_html( $fnc_option ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php else : ?>
								<input id="<?php echo esc_attr( $fnc_field_id ); ?>" type="<?php echo esc_attr( $fnc_field['type'] ); ?>" placeholder="<?php esc_attr_e( 'À confirmer', 'fnc-wordpress-theme' ); ?>" />
							<?php endif; ?>
						</div>
						<?php
					endforeach;
					?>
				</div>
				<div class="notice"><?php esc_html_e( 'Le service d’envoi reste à connecter.', 'fnc-wordpress-theme' ); ?></div>
				<button class="btn btn-red" type="button"><?php esc_html_e( 'Envoyer ma demande', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
