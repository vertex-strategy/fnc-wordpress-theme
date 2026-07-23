<?php
/**
 * Gabarit generique des Pages WordPress.
 *
 * Indispensable a la composition par blocs (Lot 2) : sans ce fichier, les
 * Pages sans gabarit dedie retombaient sur index.php, qui affiche
 * `the_excerpt()` et non `the_content()` — le contenu compose en blocs
 * n'etait donc jamais rendu.
 *
 * Deux modes :
 *  - Page composee de blocs editoriaux FNC : le contenu est rendu tel quel,
 *    car chaque bloc produit deja ses propres <section> pleine largeur avec
 *    le markup DA figé (voir inc/blocks.php). Aucun titre n'est ajoute : il
 *    appartient au bloc « Ouverture ».
 *  - Page de contenu simple (sans bloc FNC) : habillage sobre reprenant
 *    l'en-tete des pages legales, puis le contenu en colonne de lecture.
 *
 * Les pages disposant d'un gabarit dedie (page-le-forum.php, page-contact.php…)
 * conservent la priorite via la hierarchie de templates WordPress.
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
				<div class="container reading">
					<?php the_content(); ?>
				</div>
			</section>
			<?php fnc_render_cta_band(); ?>
		</main>
		<?php
	endif;

endwhile;

get_footer();
