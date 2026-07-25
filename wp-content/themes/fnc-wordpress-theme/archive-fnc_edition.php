<?php
/**
 * Archive du custom post type "fnc_edition".
 *
 * Porte docs/mockups/homepage-v2/editions.html (contenu genere par
 * site.js: editionsPage(), copy.editions). Contrairement a
 * page-le-forum.php et page-contact.php, ce gabarit est branche sur les
 * VRAIES donnees du plugin fnc-content-model (etape 4 de l'ADR-007,
 * amorcee ici) : la liste affichee reflete les editions reellement
 * publiees dans WordPress, pas du contenu d'exemple statique.
 *
 * Passe gabarits (reconciliation du modele de contenu) : rendu en frise
 * chronologique (annee, statut, theme, dates, lieu, edition speciale),
 * aligne sur la vraie page /editions du site officiel — qui montre une
 * frise, pas une grille de cartes. Trie par annee decroissante (champ
 * meta _fnc_edition_year), avec repli sur la date de publication WP si
 * l'annee n'est pas renseignee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fnc_route_h = fnc_route_hero( 'editions' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Éditions du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Éditions', 'fnc-wordpress-theme' ),
	)
);

$fnc_editions = get_posts(
	array(
		'post_type'      => 'fnc_edition',
		'posts_per_page' => -1,
		'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
		'meta_key'       => '_fnc_edition_year',
	)
);
$fnc_statuses = fnc_content_model_edition_statuses();
?>

<main id="main">
	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="editions.intro.eyebrow"><?php echo esc_html( fnc_stitle( 'editions', 'intro', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="editions.intro.title"><?php echo esc_html( fnc_stitle( 'editions', 'intro', 'title' ) ); ?></h2>
				</div>
				<div>
					<p><?php esc_html_e( 'Chaque édition est un chapitre : ce qui s’y est dit, décidé, engagé. Vous y retrouvez ce qui est déjà documenté, ce qui se prépare, et ce qui reste à confirmer.', 'fnc-wordpress-theme' ); ?></p>
					<p class="frise-note" style="margin-top:14px;"><?php esc_html_e( 'Dates, lieux, thèmes et ressources sont publiés à mesure qu’ils sont confirmés.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="editions.list.eyebrow"><?php echo esc_html( fnc_stitle( 'editions', 'list', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="editions.list.title"><?php echo esc_html( fnc_stitle( 'editions', 'list', 'title' ) ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Filtrez par moment : l’édition en cours, celles à venir, celles déjà passées.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<?php if ( ! empty( $fnc_editions ) ) : ?>
				<ol class="frise">
					<?php foreach ( $fnc_editions as $fnc_edition ) : ?>
						<?php
						$fnc_year       = get_post_meta( $fnc_edition->ID, '_fnc_edition_year', true );
						$fnc_status     = get_post_meta( $fnc_edition->ID, '_fnc_edition_status', true );
						$fnc_theme      = get_post_meta( $fnc_edition->ID, '_fnc_edition_theme', true );
						$fnc_start      = get_post_meta( $fnc_edition->ID, '_fnc_edition_start_date', true );
						$fnc_end        = get_post_meta( $fnc_edition->ID, '_fnc_edition_end_date', true );
						$fnc_location   = get_post_meta( $fnc_edition->ID, '_fnc_edition_location', true );
						$fnc_is_special = get_post_meta( $fnc_edition->ID, '_fnc_edition_is_special', true );
						$fnc_special    = get_post_meta( $fnc_edition->ID, '_fnc_edition_special_note', true );

						$fnc_dates = '';
						if ( $fnc_start ) {
							$fnc_dates = date_i18n( 'j F Y', strtotime( $fnc_start ) );
							if ( $fnc_end && $fnc_end !== $fnc_start ) {
								$fnc_dates .= ' – ' . date_i18n( 'j F Y', strtotime( $fnc_end ) );
							}
						}
						?>
						<li class="frise-item<?php echo 'current' === $fnc_status ? ' is-current' : ''; ?>">
							<div class="frise-rail" aria-hidden="true"><span class="frise-node"></span></div>
							<div class="frise-body">
								<div class="frise-head">
									<span class="frise-year"><?php echo esc_html( $fnc_year ? $fnc_year : get_the_date( 'Y', $fnc_edition ) ); ?></span>
									<?php fnc_render_badge( isset( $fnc_statuses[ $fnc_status ] ) ? $fnc_statuses[ $fnc_status ] : __( 'Statut à confirmer', 'fnc-wordpress-theme' ) ); ?>
								</div>
								<h3 class="frise-title"><a href="<?php echo esc_url( get_permalink( $fnc_edition ) ); ?>"><?php echo esc_html( get_the_title( $fnc_edition ) ); ?></a></h3>
								<?php if ( $fnc_theme ) : ?>
									<p class="frise-theme"><?php echo esc_html( $fnc_theme ); ?></p>
								<?php endif; ?>
								<?php if ( $fnc_dates || $fnc_location ) : ?>
									<p class="frise-meta">
										<?php if ( $fnc_dates ) : ?><b><?php echo esc_html( $fnc_dates ); ?></b><?php endif; ?>
										<?php if ( $fnc_dates && $fnc_location ) : ?> · <?php endif; ?>
										<?php echo esc_html( $fnc_location ); ?>
									</p>
								<?php endif; ?>
								<?php if ( $fnc_is_special && $fnc_special ) : ?>
									<p class="frise-note"><?php echo esc_html( $fnc_special ); ?></p>
								<?php endif; ?>
								<a class="frise-more" href="<?php echo esc_url( get_permalink( $fnc_edition ) ); ?>">
									<?php esc_html_e( 'Voir l’édition', 'fnc-wordpress-theme' ); ?>
									<span class="arrow" aria-hidden="true">→</span>
								</a>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucune édition publiée', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les éditions apparaîtront ici dès leur publication.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="editions.cta.eyebrow"><?php echo esc_html( fnc_stitle( 'editions', 'cta', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="editions.cta.title"><?php echo esc_html( fnc_stitle( 'editions', 'cta', 'title' ) ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Chaque édition produit des traces utiles : publications, actes, communiqués. Prolongez la lecture, ou écrivez-nous pour une ressource officielle.', 'fnc-wordpress-theme' ); ?></p>
			</div>
			<div class="toolbar" style="gap:28px;margin-top:8px;">
				<a class="link-more" href="<?php echo esc_url( fnc_archive_url( 'fnc_publication' ) ); ?>"><?php esc_html_e( 'Voir les publications', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
				<a class="link-more" href="<?php echo esc_url( fnc_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contacter l’organisation', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
