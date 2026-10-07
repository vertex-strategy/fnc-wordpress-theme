<?php
/**
 * Forum Numérique Congo — gabarit générique de repli.
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
?>

<main id="main">
	<div class="moment" style="min-height:40vh;">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
					<h2><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
					<div><?php the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun contenu à afficher.', 'fnc-wordpress-theme' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
