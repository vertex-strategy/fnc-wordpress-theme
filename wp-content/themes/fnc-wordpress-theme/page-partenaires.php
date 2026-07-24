<?php
/**
 * Gabarit de page — "Partenaires".
 *
 * Structure alignee sur le site officiel reel (localhost:3000/fr/partenaires),
 * suite a l'amendement de la Decision 1 de l'ADR-007 : le site reel
 * regroupe ses partenaires par niveau d'engagement (Institutionnel /
 * Organisateur / Soutien / Sponsor), avec site web et editions
 * associees par partenaire — absent de la maquette statique qui ne
 * montrait qu'un mur de logos generique. Contenu de demonstration
 * reste fictif (organisations d'exemple, pas les vrais partenaires
 * confirmes du site officiel).
 *
 * Page DYNAMIQUE (etape 4) : les partenaires affiches sont les vrais
 * posts fnc_partenaire publies, groupes par la vraie taxonomie
 * fnc_niveau_partenariat.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Partenaires', 'fnc-wordpress-theme' ),
		'title'      => __( 'S’associer au Forum, c’est engager l’avenir numérique du Congo.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'Le Forum réunit institutions, organisations et entreprises autour d’une même ambition.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
		'image_alt'  => __( 'Poignée de main lors du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Partenaires', 'fnc-wordpress-theme' ),
	)
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
				<p><?php esc_html_e( 'Soutenir le Forum, c’est prendre part à un espace de dialogue durable sur le numérique en Afrique centrale.', 'fnc-wordpress-theme' ); ?></p>
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
				<p><?php esc_html_e( 'Le partenariat distingue l’engagement institutionnel de l’engagement commercial.', 'fnc-wordpress-theme' ); ?></p>
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

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
