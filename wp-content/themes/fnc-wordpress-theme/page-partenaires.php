<?php
/**
 * Gabarit de page — "Partenaires".
 *
 * Structure alignee sur le site officiel reel (localhost:3000/fr/partenaires) :
 * heros, "Pourquoi devenir partenaire", "Types et niveaux", mur de partenaires
 * groupes par niveau d'engagement (Institutionnel / Organisateur / Soutien /
 * Sponsor), puis une section "Discutons d'un partenariat" avec le formulaire de
 * demande de partenariat.
 *
 * Le mur de partenaires est DYNAMIQUE : vrais posts fnc_partenaire publies,
 * groupes par la taxonomie fnc_niveau_partenariat. Le formulaire reste non
 * fonctionnel (pas de handler d'envoi), fidele a la posture du site reel tant
 * que le canal officiel n'est pas ouvert.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Partenaires', 'fnc-wordpress-theme' ),
		'title'      => __( 'S’associer au Forum, c’est engager l’avenir numérique du Congo.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Le Forum réunit institutions, organisations et entreprises autour d’une même ambition. Découvrez pourquoi et comment s’y associer.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
		'image_alt'  => __( 'Poignée de main lors du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Partenaires', 'fnc-wordpress-theme' ),
	)
);

// Champs du formulaire de demande de partenariat (ceux du site reel).
$fnc_interest_options = array(
	__( 'Partenariat institutionnel', 'fnc-wordpress-theme' ),
	__( 'Sponsoring', 'fnc-wordpress-theme' ),
	__( 'Soutien média', 'fnc-wordpress-theme' ),
	__( 'Coopération technique', 'fnc-wordpress-theme' ),
	__( 'Autre', 'fnc-wordpress-theme' ),
);
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'S’associer', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Pourquoi devenir partenaire.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Soutenir le Forum, c’est prendre part à un espace de dialogue durable sur le numérique. Chaque partenaire renforce la crédibilité collective de la démarche et gagne en visibilité auprès des décideurs.', 'fnc-wordpress-theme' ); ?></p>
			</div>
		</div>
	</section>

	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Cadre', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Types et niveaux de partenariat.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Le partenariat distingue clairement l’engagement institutionnel de l’engagement commercial.', 'fnc-wordpress-theme' ); ?></p>
			</div>
			<div class="grid grid-3">
				<?php
				fnc_render_card( __( 'Institutionnel', 'fnc-wordpress-theme' ), __( 'Pouvoirs publics et institutions qui portent la légitimité du Forum.', 'fnc-wordpress-theme' ) );
				fnc_render_card( __( 'Organisateur', 'fnc-wordpress-theme' ), __( 'Organisations qui conçoivent et opèrent l’événement.', 'fnc-wordpress-theme' ) );
				fnc_render_card( __( 'Soutien', 'fnc-wordpress-theme' ), __( 'Structures qui apportent un appui matériel, académique ou médiatique.', 'fnc-wordpress-theme' ) );
				fnc_render_card( __( 'Sponsor', 'fnc-wordpress-theme' ), __( 'Entreprises qui financent le Forum et bénéficient d’une visibilité dédiée.', 'fnc-wordpress-theme' ) );
				?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Ils nous font confiance', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Partenaires confirmés.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Les organisations associées au Forum, regroupées par nature d’engagement.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<?php
			$fnc_niveaux = get_terms(
				array(
					'taxonomy'   => 'fnc_niveau_partenariat',
					'object_ids' => get_posts( array( 'post_type' => 'fnc_partenaire', 'posts_per_page' => -1, 'fields' => 'ids' ) ),
				)
			);
			$fnc_has_partners = false;
			if ( ! is_wp_error( $fnc_niveaux ) && ! empty( $fnc_niveaux ) ) :
				foreach ( $fnc_niveaux as $fnc_niveau ) :
					$fnc_partners = get_posts(
						array(
							'post_type'      => 'fnc_partenaire',
							'posts_per_page' => -1,
							'tax_query'      => array(
								array(
									'taxonomy' => 'fnc_niveau_partenariat',
									'field'    => 'term_id',
									'terms'    => $fnc_niveau->term_id,
								),
							),
						)
					);
					if ( empty( $fnc_partners ) ) {
						continue;
					}
					$fnc_has_partners = true;
					?>
					<h3 style="margin:32px 0 16px;color:var(--navy);"><?php echo esc_html( $fnc_niveau->name ); ?></h3>
					<div class="grid grid-3">
						<?php foreach ( $fnc_partners as $fnc_partner ) : ?>
							<article class="card fnc-card">
								<?php
								if ( has_post_thumbnail( $fnc_partner ) ) :
									?>
									<a class="partner-logo" href="<?php echo esc_url( get_permalink( $fnc_partner ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $fnc_partner ) ); ?>">
										<?php echo get_the_post_thumbnail( $fnc_partner, 'medium', array( 'alt' => esc_attr( get_the_title( $fnc_partner ) ) ) ); ?>
									</a>
								<?php endif; ?>
								<h3><a href="<?php echo esc_url( get_permalink( $fnc_partner ) ); ?>"><?php echo esc_html( get_the_title( $fnc_partner ) ); ?></a></h3>
								<?php if ( has_excerpt( $fnc_partner ) ) : ?>
									<p><?php echo esc_html( get_the_excerpt( $fnc_partner ) ); ?></p>
								<?php endif; ?>
								<?php
								$fnc_participations = get_post_meta( $fnc_partner->ID, '_fnc_partenaire_participations', true );
								$fnc_niveau_by_edition = array();
								if ( is_array( $fnc_participations ) ) {
									foreach ( $fnc_participations as $fnc_participation ) {
										if ( ! empty( $fnc_participation['edition'] ) ) {
											$fnc_niveau_by_edition[ (int) $fnc_participation['edition'] ] = $fnc_participation['niveau'] ?? '';
										}
									}
								}
								$fnc_partner_niveaux   = fnc_content_model_partner_niveaux();
								$fnc_partner_editions = get_post_meta( $fnc_partner->ID, '_fnc_partenaire_editions', true );
								if ( is_array( $fnc_partner_editions ) && ! empty( $fnc_partner_editions ) ) :
									?>
									<p class="help"><?php esc_html_e( 'Éditions associées', 'fnc-wordpress-theme' ); ?></p>
									<?php foreach ( $fnc_partner_editions as $fnc_edition_id ) : ?>
										<p style="margin-top:6px;">
											<a class="link-more" href="<?php echo esc_url( get_permalink( $fnc_edition_id ) ); ?>"><?php echo esc_html( get_the_title( $fnc_edition_id ) ); ?></a>
											<?php
											$fnc_niveau = $fnc_niveau_by_edition[ $fnc_edition_id ] ?? '';
											if ( $fnc_niveau && isset( $fnc_partner_niveaux[ $fnc_niveau ] ) ) :
												fnc_render_badge( $fnc_partner_niveaux[ $fnc_niveau ] );
											endif;
											?>
										</p>
									<?php endforeach; ?>
								<?php endif; ?>
								<?php
								$fnc_partner_site = get_post_meta( $fnc_partner->ID, '_fnc_partenaire_site', true );
								if ( $fnc_partner_site ) :
									?>
									<p style="margin-top:12px;"><a class="link-more" href="<?php echo esc_url( $fnc_partner_site ); ?>"><?php esc_html_e( 'Visiter le site', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
					<?php
				endforeach;
			endif;
			if ( ! $fnc_has_partners ) :
				?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucun partenaire confirmé', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les partenaires apparaîtront ici dès leur confirmation.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Échanger : demande de partenariat -->
	<section class="section linen">
		<div class="split media-left">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Échanger', 'fnc-wordpress-theme' ); ?></span>
				<h2 class="lines"><?php esc_html_e( 'Discutons d’un partenariat.', 'fnc-wordpress-theme' ); ?></h2>
				<p class="body"><?php esc_html_e( 'Vous représentez une institution ou une entreprise ? Contactez l’organisation pour construire un partenariat adapté.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<form class="card form" aria-label="<?php esc_attr_e( 'Demande de partenariat', 'fnc-wordpress-theme' ); ?>">
				<h2 style="font-size:1.6rem;color:var(--navy-deep);"><?php esc_html_e( 'Demande de partenariat', 'fnc-wordpress-theme' ); ?></h2>
				<p class="help"><?php esc_html_e( 'Votre demande est enregistrée avant tout accusé de réception. Les modalités détaillées restent à confirmer par l’organisation.', 'fnc-wordpress-theme' ); ?></p>

				<?php // Pot de miel anti-spam (masque). ?>
				<div class="hp" aria-hidden="true" style="position:absolute;left:-9999px;">
					<label><?php esc_html_e( 'Ne pas remplir', 'fnc-wordpress-theme' ); ?><input type="text" name="fnc_hp" tabindex="-1" autocomplete="off" /></label>
				</div>

				<div class="form-grid">
					<div class="field">
						<label for="fnc-pa-fullname"><?php esc_html_e( 'Nom complet', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-pa-fullname" type="text" required />
					</div>
					<div class="field">
						<label for="fnc-pa-org"><?php esc_html_e( 'Organisation', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-pa-org" type="text" required />
					</div>
					<div class="field">
						<label for="fnc-pa-role"><?php esc_html_e( 'Fonction', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-pa-role" type="text" />
					</div>
					<div class="field">
						<label for="fnc-pa-email"><?php esc_html_e( 'Email', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-pa-email" type="email" required />
					</div>
					<div class="field">
						<label for="fnc-pa-phone"><?php esc_html_e( 'Téléphone', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-pa-phone" type="tel" />
					</div>
					<div class="field">
						<label for="fnc-pa-country"><?php esc_html_e( 'Pays', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-pa-country" type="text" />
					</div>
					<div class="field full">
						<label for="fnc-pa-interest"><?php esc_html_e( 'Type d’intérêt', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<select id="fnc-pa-interest" required>
							<?php foreach ( $fnc_interest_options as $fnc_opt ) : ?>
								<option value="<?php echo esc_attr( $fnc_opt ); ?>"><?php echo esc_html( $fnc_opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field full">
						<label for="fnc-pa-message"><?php esc_html_e( 'Message', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<textarea id="fnc-pa-message" required></textarea>
					</div>
				</div>

				<p class="help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires. Aucun niveau, montant ou avantage n’est présumé.', 'fnc-wordpress-theme' ); ?></p>
				<button class="btn btn-red" type="submit"><?php esc_html_e( 'Envoyer la demande', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
		</div>
	</section>
</main>

<?php get_footer(); ?>
