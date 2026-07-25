<?php
/**
 * Forum Numérique Congo — gabarit de page (contenu composé ou texte simple pour les pages légales).
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

// Une page « composee » est une page qui utilise au moins un bloc editorial FNC.
$fnc_is_composed = function_exists( 'fnc_page_has_blocks' ) && fnc_page_has_blocks();

while ( have_posts() ) :
	the_post();

	if ( $fnc_is_composed ) :
		?>
		<main id="main">
			<?php the_content(); ?>
		</main>
		<?php
	else :
		fnc_render_legal_header(
			get_the_title(),
			sprintf(
				/* translators: %s: date de derniere mise a jour. */
				__( 'Mise à jour : %s', 'fnc-wordpress-theme' ),
				get_the_modified_date()
			),
			get_the_title()
		);
		?>
		<main id="main">
			<section class="section">
				<div class="container reading prose-legal">
					<?php the_content(); ?>
				</div>
			</section>
			<?php fnc_render_cta_band(); ?>
		</main>
		<?php
	endif;

endwhile;

get_footer();
