<?php
/**
 * Forum Numérique Congo — gabarit de la page « Informations pratiques ».
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

$fnc_route_h = fnc_route_hero( 'infos-pratiques' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Informations pratiques du Forum Numérique Congo', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Informations pratiques', 'fnc-wordpress-theme' ),
	)
);

// Edition en cours : porteuse des rubriques pratiques.
$fnc_ip_edition_id = function_exists( 'fnc_current_edition_id' ) ? fnc_current_edition_id() : 0;
$fnc_ip_edition    = $fnc_ip_edition_id ? get_post( $fnc_ip_edition_id ) : null;
$fnc_ip_rubriques = $fnc_ip_edition ? fnc_render_practical_info( $fnc_ip_edition ) : '';
?>

<main id="main">
	<?php if ( '' !== trim( $fnc_ip_rubriques ) ) : ?>
		<section class="section" aria-labelledby="practical-info-title">
			<div class="container">
				<span class="eyebrow" id="practical-info-title" data-fnc-st="infos-pratiques.onsite.eyebrow"><?php echo esc_html( fnc_stitle( 'infos-pratiques', 'onsite', 'eyebrow' ) ); ?></span>
				<div class="rule" aria-hidden="true" style="margin-top:12px;"></div>
				<div class="practical-list">
					<?php echo $fnc_ip_rubriques; // phpcs:ignore WordPress.Security.EscapeOutput -- markup produit par les renderers de blocs, deja echappe. ?>
				</div>
			</div>
		</section>
	<?php else : ?>
		<section class="section">
			<div class="container reading">
				<p class="eyebrow"><?php echo esc_html( $fnc_ip_edition ? get_the_title( $fnc_ip_edition ) : fnc_site_name() ); ?></p>
				<p class="body"><?php esc_html_e( 'Les informations pratiques de la prochaine édition sont en cours de finalisation et seront publiées ici dès leur confirmation.', 'fnc-wordpress-theme' ); ?></p>
			</div>
		</section>
	<?php endif; ?>

	<section class="callout">
		<h2 data-fnc-st="infos-pratiques.cta.title"><?php echo esc_html( fnc_stitle( 'infos-pratiques', 'cta', 'title' ) ); ?></h2>
		<p><?php esc_html_e( 'Rejoignez la prochaine édition du Forum Numérique Congo à Brazzaville.', 'fnc-wordpress-theme' ); ?></p>
		<?php
		// Bascule du CTA selon l'ouverture des inscriptions (parité Next) :
		// ouvertes → « S'inscrire » → /inscription ; fermées → « Découvrir l'édition »
		// → hub de l'édition en cours.
		$fnc_reg_open = ! function_exists( 'fnc_registration_enabled' ) || fnc_registration_enabled();
		?>
		<a class="btn btn-red" href="<?php echo esc_url( $fnc_reg_open ? fnc_page_url( 'inscription' ) : fnc_page_url( 'edition-en-cours' ) ); ?>"><?php echo esc_html( $fnc_reg_open ? __( 'S’inscrire', 'fnc-wordpress-theme' ) : __( 'Découvrir l’édition', 'fnc-wordpress-theme' ) ); ?>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
