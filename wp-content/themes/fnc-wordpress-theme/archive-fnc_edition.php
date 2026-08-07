<?php
/**
 * Forum Numérique Congo — liste des éditions du Forum.
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

// Libellés de liste (front) : « Passées » au pluriel comme le site Next, pour
// les chips ET le badge de la frise (le libellé de statut admin reste au
// singulier). Ordre d'affichage : En cours · À venir · Passées.
$fnc_list_labels = array(
	'current'  => __( 'En cours', 'fnc-wordpress-theme' ),
	'upcoming' => __( 'À venir', 'fnc-wordpress-theme' ),
	'past'     => __( 'Passées', 'fnc-wordpress-theme' ),
);

// Comptes par statut (sur toutes les éditions publiées).
$fnc_counts = array( 'current' => 0, 'upcoming' => 0, 'past' => 0 );
foreach ( $fnc_editions as $fnc_e ) {
	$fnc_s = get_post_meta( $fnc_e->ID, '_fnc_edition_status', true );
	if ( isset( $fnc_counts[ $fnc_s ] ) ) {
		++$fnc_counts[ $fnc_s ];
	}
}
$fnc_total = count( $fnc_editions );

// Filtre ?status= (comme le site Next). Valeur inconnue → « toutes ».
$fnc_cur_status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $fnc_cur_status, array( 'current', 'upcoming', 'past' ), true ) ) {
	$fnc_cur_status = '';
}
$fnc_shown = $fnc_cur_status
	? array_values(
		array_filter(
			$fnc_editions,
			static function ( $e ) use ( $fnc_cur_status ) {
				return get_post_meta( $e->ID, '_fnc_edition_status', true ) === $fnc_cur_status;
			}
		)
	)
	: $fnc_editions;

$fnc_ed_pg = fnc_paginate_list( $fnc_shown, 12 );

$fnc_editions_url = get_post_type_archive_link( 'fnc_edition' );
?>

<main id="main">
	<section class="section linen">
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

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="editions.list.eyebrow"><?php echo esc_html( fnc_stitle( 'editions', 'list', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="editions.list.title"><?php echo esc_html( fnc_stitle( 'editions', 'list', 'title' ) ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Filtrez par moment : l’édition en cours, celles à venir, celles déjà passées.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<?php // Carte blanche de filtre : COMPOSANT PARTAGÉ (chips par statut + comptes + compteur) + encart. ?>
			<div class="fnc-card" style="background:#fff;border:1px solid var(--border);border-radius:4px;padding:24px;margin-bottom:32px;">
				<?php
				$fnc_chip_options = array();
				foreach ( array( 'current', 'upcoming', 'past' ) as $fnc_st ) {
					$fnc_chip_options[ $fnc_st ] = array(
						'label' => $fnc_list_labels[ $fnc_st ],
						'count' => (int) $fnc_counts[ $fnc_st ],
					);
				}
				fnc_render_filter_chips(
					array(
						'base_url'   => $fnc_editions_url,
						'param'      => 'status',
						'current'    => $fnc_cur_status,
						'all_label'  => __( 'Toutes', 'fnc-wordpress-theme' ),
						'all_count'  => $fnc_total,
						'options'    => $fnc_chip_options,
						'aria_label' => __( 'Filtrer les éditions par statut', 'fnc-wordpress-theme' ),
						/* translators: %d: nombre d'éditions affichées. */
						'counter'    => sprintf( _n( '%d édition affichée', '%d éditions affichées', count( $fnc_shown ), 'fnc-wordpress-theme' ), count( $fnc_shown ) ),
					)
				);
				?>
				<p class="frise-note" style="margin-top:8px;color:var(--texte-tert);">
					<strong><?php esc_html_e( 'Ce que vous voyez ici', 'fnc-wordpress-theme' ); ?> :</strong>
					<?php esc_html_e( 'seules les éditions confirmées sont affichées ; les dates, lieux et thèmes apparaissent à mesure qu’ils sont validés.', 'fnc-wordpress-theme' ); ?>
				</p>
			</div>

			<?php if ( ! empty( $fnc_shown ) ) : ?>
				<ol class="frise">
					<?php foreach ( $fnc_ed_pg["items"] as $fnc_edition ) : ?>
						<?php
						$fnc_year       = get_post_meta( $fnc_edition->ID, '_fnc_edition_year', true );
						$fnc_status     = get_post_meta( $fnc_edition->ID, '_fnc_edition_status', true );
						$fnc_theme      = get_post_meta( $fnc_edition->ID, '_fnc_edition_theme', true );
						$fnc_start      = get_post_meta( $fnc_edition->ID, '_fnc_edition_start_date', true );
						$fnc_end        = get_post_meta( $fnc_edition->ID, '_fnc_edition_end_date', true );
						$fnc_location   = get_post_meta( $fnc_edition->ID, '_fnc_edition_location', true );
						$fnc_is_special = get_post_meta( $fnc_edition->ID, '_fnc_edition_is_special', true );
						$fnc_special    = get_post_meta( $fnc_edition->ID, '_fnc_edition_special_note', true );

						$fnc_dates = fnc_format_date_range( $fnc_start, $fnc_end );
						?>
						<li class="frise-item<?php echo $fnc_status ? ' is-' . esc_attr( $fnc_status ) : ''; ?>">
							<div class="frise-rail" aria-hidden="true"><span class="frise-node"></span></div>
							<div class="frise-body">
								<div class="frise-head">
									<span class="frise-year"><?php echo esc_html( $fnc_year ? $fnc_year : get_the_date( 'Y', $fnc_edition ) ); ?></span>
									<?php fnc_render_badge( isset( $fnc_list_labels[ $fnc_status ] ) ? $fnc_list_labels[ $fnc_status ] : __( 'Statut à confirmer', 'fnc-wordpress-theme' ) ); ?>
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
			<?php fnc_render_pagination_nav( $fnc_ed_pg["paged"], $fnc_ed_pg["max_pages"], $fnc_editions_url, array( "status" => $fnc_cur_status ) ); ?>
				<?php else : ?>
				<div class="empty" role="status">
					<?php if ( $fnc_cur_status ) : ?>
						<h3><?php echo esc_html( sprintf( /* translators: %s: libellé de statut. */ __( 'Aucune édition « %s »', 'fnc-wordpress-theme' ), $fnc_list_labels[ $fnc_cur_status ] ) ); ?></h3>
						<p><a class="link-more" href="<?php echo esc_url( $fnc_editions_url ); ?>"><?php esc_html_e( 'Voir toutes les éditions', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
					<?php else : ?>
						<h3><?php esc_html_e( 'Aucune édition publiée', 'fnc-wordpress-theme' ); ?></h3>
						<p><?php esc_html_e( 'Les éditions apparaîtront ici dès leur publication.', 'fnc-wordpress-theme' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	// COMPOSANT PARTAGÉ : section « complément » (bande claire + blocs-liens bordés).
	fnc_render_complement_section(
		array(
			'eyebrow' => __( 'Suite', 'fnc-wordpress-theme' ),
			'title'   => fnc_stitle( 'editions', 'cta', 'title' ),
			'intro'   => __( 'Chaque édition produit des traces utiles : publications, actes, communiqués. Prolongez la lecture, ou écrivez-nous pour une ressource officielle.', 'fnc-wordpress-theme' ),
			'links'   => array(
				array( 'label' => __( 'Voir les publications', 'fnc-wordpress-theme' ), 'href' => fnc_archive_url( 'fnc_publication' ) ),
				array( 'label' => __( 'Contacter l’organisation', 'fnc-wordpress-theme' ), 'href' => fnc_page_url( 'contact' ) ),
			),
		)
	);
	?>
</main>

<?php get_footer(); ?>
