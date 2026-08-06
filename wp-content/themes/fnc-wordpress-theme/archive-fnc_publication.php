<?php
/**
 * Forum Numérique Congo — ressources (liste des publications).
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

$fnc_route_h = fnc_route_hero( 'ressources' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Ressources du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Ressources', 'fnc-wordpress-theme' ),
	)
);

$fnc_archive_url       = get_post_type_archive_link( 'fnc_publication' );
$fnc_current_type      = isset( $_GET['fnc_type'] ) ? sanitize_text_field( wp_unslash( $_GET['fnc_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$fnc_search_query      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$fnc_publication_types = fnc_content_model_publication_types();
// La validation du type se fait plus bas, contre les types RÉELLEMENT présents
// (le seed peut employer des slugs hors registre — cf. réserve MOA).

// Libellés PLURIELS pour les chips de filtre (le badge de carte garde le
// singulier précis). Comme le site Next : « Rapports », « Vidéos »…
$fnc_type_plural = array(
	// Slugs du registre.
	'report'            => __( 'Rapports', 'fnc-wordpress-theme' ),
	'whitepaper'        => __( 'Livres blancs', 'fnc-wordpress-theme' ),
	'proceedings'       => __( 'Actes', 'fnc-wordpress-theme' ),
	'pressRelease'      => __( 'Communiqués', 'fnc-wordpress-theme' ),
	'noteConceptuelle'  => __( 'Notes conceptuelles', 'fnc-wordpress-theme' ),
	'video'             => __( 'Vidéos', 'fnc-wordpress-theme' ),
	'interview'         => __( 'Interviews', 'fnc-wordpress-theme' ),
	'other'             => __( 'Autres', 'fnc-wordpress-theme' ),
	// Slugs employés par le seed courant (affichage seulement ; l'alignement
	// registre/seed reste une décision de contenu — cf. réserve MOA).
	'compte_rendu'      => __( 'Comptes rendus', 'fnc-wordpress-theme' ),
	'document_pratique' => __( 'Documents pratiques', 'fnc-wordpress-theme' ),
	'note_conceptuelle' => __( 'Notes conceptuelles', 'fnc-wordpress-theme' ),
	'programme'         => __( 'Programmes', 'fnc-wordpress-theme' ),
);

// On interroge toutes les publications correspondant à la RECHERCHE (sans filtre
// de type), pour compter par type ET filtrer ensuite en PHP : les comptes des
// chips reflètent ainsi la recherche courante.
$fnc_all = get_posts(
	array(
		'post_type'      => 'fnc_publication',
		'posts_per_page' => -1,
		's'              => $fnc_search_query,
	)
);
$fnc_type_counts = array();
foreach ( $fnc_all as $fnc_p ) {
	$fnc_t = get_post_meta( $fnc_p->ID, '_fnc_publication_type', true );
	if ( $fnc_t ) {
		$fnc_type_counts[ $fnc_t ] = isset( $fnc_type_counts[ $fnc_t ] ) ? $fnc_type_counts[ $fnc_t ] + 1 : 1;
	}
}
$fnc_total = count( $fnc_all );

// Validation du filtre : on n'accepte que les types réellement présents.
if ( '' !== $fnc_current_type && ! isset( $fnc_type_counts[ $fnc_current_type ] ) ) {
	$fnc_current_type = '';
}

// Liste affichée : filtrée par type si demandé.
$fnc_publications = $fnc_current_type
	? array_values(
		array_filter(
			$fnc_all,
			static function ( $p ) use ( $fnc_current_type ) {
				return get_post_meta( $p->ID, '_fnc_publication_type', true ) === $fnc_current_type;
			}
		)
	)
	: $fnc_all;

// Options de chips : tous les types réellement présents (compte > 0), quel que
// soit leur slug. Ordre : slugs du registre d'abord, puis les autres. Libellé :
// pluriel connu, sinon libellé du registre, sinon « humanisation » du slug.
$fnc_chip_label = static function ( $slug ) use ( $fnc_type_plural, $fnc_publication_types ) {
	if ( isset( $fnc_type_plural[ $slug ] ) ) {
		return $fnc_type_plural[ $slug ];
	}
	if ( isset( $fnc_publication_types[ $slug ] ) ) {
		return $fnc_publication_types[ $slug ];
	}
	return ucfirst( str_replace( array( '_', '-' ), ' ', $slug ) );
};
$fnc_chip_options = array();
foreach ( $fnc_publication_types as $fnc_tv => $fnc_tl ) {
	if ( ! empty( $fnc_type_counts[ $fnc_tv ] ) ) {
		$fnc_chip_options[ $fnc_tv ] = array( 'label' => $fnc_chip_label( $fnc_tv ), 'count' => (int) $fnc_type_counts[ $fnc_tv ] );
	}
}
foreach ( $fnc_type_counts as $fnc_tv => $fnc_cnt ) {
	if ( ! isset( $fnc_chip_options[ $fnc_tv ] ) ) {
		$fnc_chip_options[ $fnc_tv ] = array( 'label' => $fnc_chip_label( $fnc_tv ), 'count' => (int) $fnc_cnt );
	}
}
?>

<main id="main">
	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="ressources.intro.eyebrow"><?php echo esc_html( fnc_stitle( 'ressources', 'intro', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="ressources.intro.title"><?php echo esc_html( fnc_stitle( 'ressources', 'intro', 'title' ) ); ?></h2>
				</div>
				<div>
					<p><?php esc_html_e( 'Une bibliothèque claire et sobre : les ressources disponibles d’un côté, les parutions à venir de l’autre. Chaque document publié engage le Forum.', 'fnc-wordpress-theme' ); ?></p>
					<p class="frise-note" style="margin-top:14px;"><?php esc_html_e( 'Auteurs, dates et fichiers apparaissent dès qu’ils sont confirmés.', 'fnc-wordpress-theme' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="ressources.search.eyebrow"><?php echo esc_html( fnc_stitle( 'ressources', 'search', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="ressources.search.title"><?php echo esc_html( fnc_stitle( 'ressources', 'search', 'title' ) ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Retrouvez une ressource par mot-clé ou par type de document.', 'fnc-wordpress-theme' ); ?></p>
			</div>

			<form class="field" method="get" action="<?php echo esc_url( $fnc_archive_url ); ?>" role="search" style="max-width:420px;margin-bottom:20px;">
				<label for="fnc-publication-search"><?php esc_html_e( 'Recherche', 'fnc-wordpress-theme' ); ?></label>
				<input id="fnc-publication-search" type="search" name="s" value="<?php echo esc_attr( $fnc_search_query ); ?>" placeholder="<?php esc_attr_e( 'Rechercher une publication', 'fnc-wordpress-theme' ); ?>" />
				<?php if ( $fnc_current_type ) : ?>
					<input type="hidden" name="fnc_type" value="<?php echo esc_attr( $fnc_current_type ); ?>" />
				<?php endif; ?>
				<button class="btn" type="submit" style="margin-top:10px;"><?php esc_html_e( 'Rechercher', 'fnc-wordpress-theme' ); ?></button>
			</form>

			<?php
			// COMPOSANT PARTAGÉ : chips par type (pluriel + comptes) + compteur.
			fnc_render_filter_chips(
				array(
					'base_url'   => $fnc_archive_url,
					'param'      => 'fnc_type',
					'current'    => $fnc_current_type,
					'all_label'  => __( 'Tous', 'fnc-wordpress-theme' ),
					'all_count'  => $fnc_total,
					'options'    => $fnc_chip_options,
					'preserve'   => array( 's' => $fnc_search_query ),
					'aria_label' => __( 'Filtrer par type', 'fnc-wordpress-theme' ),
					/* translators: %d: nombre de publications affichées. */
					'counter'    => sprintf( _n( '%d publication affichée', '%d publications affichées', count( $fnc_publications ), 'fnc-wordpress-theme' ), count( $fnc_publications ) ),
				)
			);
			?>

			<?php if ( ! empty( $fnc_publications ) ) : ?>
				<div class="pubs" style="margin-top:32px;">
					<?php
					foreach ( $fnc_publications as $fnc_publication ) {
						fnc_render_publication_card( $fnc_publication->ID );
					}
					?>
				</div>
			<?php else : ?>
				<div class="empty" role="status">
					<?php if ( $fnc_current_type || $fnc_search_query ) : ?>
						<h3><?php esc_html_e( 'Aucune publication ne correspond', 'fnc-wordpress-theme' ); ?></h3>
						<p><a class="link-more" href="<?php echo esc_url( $fnc_archive_url ); ?>"><?php esc_html_e( 'Réinitialiser les filtres', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
					<?php else : ?>
						<h3><?php esc_html_e( 'Aucune publication validée', 'fnc-wordpress-theme' ); ?></h3>
						<p><?php esc_html_e( 'L’état vide reste sobre, sans faux contenu.', 'fnc-wordpress-theme' ); ?></p>
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
			'title'   => fnc_stitle( 'ressources', 'cta', 'title' ),
			'intro'   => __( 'Les publications nourrissent le dialogue public. Découvrez le rôle du Forum, ou écrivez-nous pour une ressource officielle.', 'fnc-wordpress-theme' ),
			'links'   => array(
				array( 'label' => __( 'Comprendre le Forum', 'fnc-wordpress-theme' ), 'href' => fnc_page_url( 'le-forum' ) ),
				array( 'label' => __( 'Contacter l’organisation', 'fnc-wordpress-theme' ), 'href' => fnc_page_url( 'contact' ) ),
			),
		)
	);
	?>
</main>

<?php get_footer(); ?>
