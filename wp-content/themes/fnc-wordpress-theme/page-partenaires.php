<?php
/**
 * Forum Numérique Congo — gabarit de la page « Partenaires ».
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

/*
 * Composition par blocs : des que l'editorial compose cette page avec des
 * blocs FNC, ce gabarit s'efface au profit du contenu compose (editable et
 * reagenceable) ; sinon il conserve son contenu de demonstration (comportement
 * inchange). Meme convention que page.php et page-le-forum.php.
 */
if ( function_exists( 'fnc_page_has_blocks' ) && fnc_page_has_blocks() ) {
	while ( have_posts() ) {
		the_post();
		echo '<main id="main">';
		the_content();
		echo '</main>';
	}
	get_footer();
	return;
}

$fnc_route_h = fnc_route_hero( 'partenaires' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Partenaires du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Partenaires', 'fnc-wordpress-theme' ),
	)
);

// Champs du formulaire de demande de partenariat (ceux du site du Forum).
$fnc_pa_flash = function_exists( 'fnc_take_flash' ) ? fnc_take_flash( 'partenariat' ) : null;
$fnc_interest_options = array(
	__( 'Partenariat institutionnel', 'fnc-wordpress-theme' ),
	__( 'Sponsoring', 'fnc-wordpress-theme' ),
	__( 'Soutien média', 'fnc-wordpress-theme' ),
	__( 'Coopération technique', 'fnc-wordpress-theme' ),
	__( 'Autre', 'fnc-wordpress-theme' ),
);
?>

<main id="main">
	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="partenaires.why.eyebrow"><?php echo esc_html( fnc_stitle( 'partenaires', 'why', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="partenaires.why.title"><?php echo esc_html( fnc_stitle( 'partenaires', 'why', 'title' ) ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Soutenir le Forum, c’est prendre part à un espace de dialogue durable sur le numérique. Chaque partenaire renforce la crédibilité collective de la démarche et gagne en visibilité auprès des décideurs.', 'fnc-wordpress-theme' ); ?></p>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="partenaires.types.eyebrow"><?php echo esc_html( fnc_stitle( 'partenaires', 'types', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="partenaires.types.title"><?php echo esc_html( fnc_stitle( 'partenaires', 'types', 'title' ) ); ?></h2>
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

	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="partenaires.confirmed.eyebrow"><?php echo esc_html( fnc_stitle( 'partenaires', 'confirmed', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="partenaires.confirmed.title"><?php echo esc_html( fnc_stitle( 'partenaires', 'confirmed', 'title' ) ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Les organisations associées au Forum, regroupées par nature d’engagement.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<?php
			// Groupement dans l'ordre FIXE du site du Forum (institution ->
			// organisateur -> soutien -> sponsor), partenaires tries par ordre
			// d'affichage (_fnc_partenaire_sort_index).
			$fnc_has_partners = false;
			foreach ( array( 'institutionnel', 'organisateur', 'soutien', 'sponsor' ) as $fnc_niveau_slug ) :
				$fnc_niveau = get_term_by( 'slug', $fnc_niveau_slug, 'fnc_niveau_partenariat' );
				if ( ! $fnc_niveau || is_wp_error( $fnc_niveau ) ) {
					continue;
				}
					$fnc_partners = get_posts(
						array(
							'post_type'      => 'fnc_partenaire',
							'posts_per_page' => -1,
							'orderby'        => 'meta_value_num title',
							'meta_key'       => '_fnc_partenaire_sort_index',
							'order'          => 'ASC',
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
								<?php else :
									// Repli DA-cohérent (registre RÈGLE 7) : monogramme, jamais une carte nom-seul brute.
									$fnc_p_words = preg_split( '/\s+/', trim( wp_strip_all_tags( get_the_title( $fnc_partner ) ) ) );
									$fnc_p_mono  = '';
									foreach ( array_slice( (array) $fnc_p_words, 0, 2 ) as $fnc_p_w ) {
										$fnc_p_mono .= mb_strtoupper( mb_substr( $fnc_p_w, 0, 1 ) );
									}
									?>
									<a class="partner-logo partner-logo--mono" href="<?php echo esc_url( get_permalink( $fnc_partner ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $fnc_partner ) ); ?>">
										<span aria-hidden="true"><?php echo esc_html( $fnc_p_mono ); ?></span>
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
	<section class="section">
		<div class="split media-left">
			<div>
				<span class="eyebrow" data-fnc-st="partenaires.cta.eyebrow"><?php echo esc_html( fnc_stitle( 'partenaires', 'cta', 'eyebrow' ) ); ?></span>
				<h2 class="lines" data-fnc-st="partenaires.cta.title"><?php echo esc_html( fnc_stitle( 'partenaires', 'cta', 'title' ) ); ?></h2>
				<p class="body"><?php esc_html_e( 'Vous représentez une institution ou une entreprise ? Contactez l’organisation pour construire un partenariat adapté.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<form class="card form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" aria-label="<?php esc_attr_e( 'Demande de partenariat', 'fnc-wordpress-theme' ); ?>" aria-describedby="fnc-partenariat-help">
				<h2 style="font-size:1.6rem;color:var(--navy-deep);"><?php esc_html_e( 'Demande de partenariat', 'fnc-wordpress-theme' ); ?></h2>
				<p class="help"><?php esc_html_e( 'Votre demande est enregistrée avant tout accusé de réception. Les modalités détaillées restent à confirmer par l’organisation.', 'fnc-wordpress-theme' ); ?></p>

				<?php
				if ( function_exists( 'fnc_form_fields' ) ) { fnc_form_fields( 'partenariat' ); }
				if ( function_exists( 'fnc_submission_banner' ) ) { echo fnc_submission_banner( 'partenariat', $fnc_pa_flash ); } // phpcs:ignore WordPress.Security.EscapeOutput
				?>

				<div class="form-grid">
					<div class="field">
						<label for="fnc-pa-fullname"><?php esc_html_e( 'Nom complet', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-pa-fullname" name="fullName" type="text" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_pa_flash, 'fullName' ) : '' ); ?>" required />
					</div>
					<div class="field">
						<label for="fnc-pa-org"><?php esc_html_e( 'Organisation', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-pa-org" name="organization" type="text" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_pa_flash, 'organization' ) : '' ); ?>" required />
					</div>
					<div class="field">
						<label for="fnc-pa-role"><?php esc_html_e( 'Fonction', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-pa-role" name="role" type="text" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_pa_flash, 'role' ) : '' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-pa-email"><?php esc_html_e( 'Email', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<input id="fnc-pa-email" name="email" type="email" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_pa_flash, 'email' ) : '' ); ?>" required />
					</div>
					<div class="field">
						<label for="fnc-pa-phone"><?php esc_html_e( 'Téléphone', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-pa-phone" name="phone" type="tel" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_pa_flash, 'phone' ) : '' ); ?>" />
					</div>
					<div class="field">
						<label for="fnc-pa-country"><?php esc_html_e( 'Pays', 'fnc-wordpress-theme' ); ?></label>
						<input id="fnc-pa-country" name="country" type="text" value="<?php echo esc_attr( function_exists( 'fnc_old' ) ? fnc_old( $fnc_pa_flash, 'country' ) : '' ); ?>" />
					</div>
					<div class="field full">
						<label for="fnc-pa-interest"><?php esc_html_e( 'Type d’intérêt', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<select id="fnc-pa-interest" name="interestType" required>
							<?php foreach ( $fnc_interest_options as $fnc_opt ) : ?>
								<option value="<?php echo esc_attr( $fnc_opt ); ?>"><?php echo esc_html( $fnc_opt ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field full">
						<label for="fnc-pa-message"><?php esc_html_e( 'Message', 'fnc-wordpress-theme' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<textarea id="fnc-pa-message" name="message" required></textarea>
					</div>
				</div>

				<p class="help" id="fnc-partenariat-help"><?php esc_html_e( 'Les champs marqués d’un astérisque sont obligatoires. Aucun niveau, montant ou avantage n’est présumé.', 'fnc-wordpress-theme' ); ?></p>
				<button class="btn" type="submit"><?php esc_html_e( 'Envoyer la demande', 'fnc-wordpress-theme' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
		</div>
	</section>
</main>

<?php get_footer(); ?>
