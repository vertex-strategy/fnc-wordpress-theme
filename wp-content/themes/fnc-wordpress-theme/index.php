<?php
/**
 * Gabarit de repli generique (obligatoire dans la hierarchie de templates
 * WordPress). La page d'accueil utilise front-page.php ; ce fichier ne sert
 * que pour les contextes qui n'ont pas encore de gabarit dedie (etape 2 du
 * plan de mise en oeuvre de l'ADR-007 ne couvre que la page d'accueil).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>
	<div class="moment" style="min-height:40vh;">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
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
