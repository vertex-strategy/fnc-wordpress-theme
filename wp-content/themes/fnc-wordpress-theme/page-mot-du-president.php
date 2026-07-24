<?php
/**
 * Gabarit de page — "Mot du président".
 *
 * Aligne sur la page reelle localhost:3000/fr/le-forum/mot-du-president : heros
 * « Le mot du Président » (avec l'exergue), puis le message en deux colonnes
 * (portrait + identite a gauche, texte a droite) et un callout « Rejoignez le
 * Forum ». Le nom et le message sont ceux du teaser deja porte par la page
 * « Le Forum » (page-le-forum.php) — coherence entre les deux surfaces. Le
 * portrait reste l'illustration embarquee tant que la vraie photo n'est pas
 * fournie.
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
?>

<main id="main">
	<header class="opening" style="min-height:56vh;">
		<img class="media-cover" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-pupitre.png' ); ?>" alt="" aria-hidden="true" />
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <a href="<?php echo esc_url( fnc_page_url( 'le-forum' ) ); ?>"><?php esc_html_e( 'Le Forum', 'fnc-wordpress-theme' ); ?></a> · <?php esc_html_e( 'Le mot du Président', 'fnc-wordpress-theme' ); ?></p>
			<span class="eyebrow"><?php esc_html_e( 'Le Président du Forum', 'fnc-wordpress-theme' ); ?></span>
			<h1><?php esc_html_e( 'Le mot du Président', 'fnc-wordpress-theme' ); ?></h1>
			<p class="intro"><?php esc_html_e( '« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. Entre les éditions, nous transformons une ambition nationale en trajectoire concrète, au service des Congolais. »', 'fnc-wordpress-theme' ); ?></p>
		</div>
		<?php get_template_part( 'hero-pcb' ); ?>
	</header>

	<section class="section president-block">
		<div class="container">
			<div class="split">
				<div>
					<figure><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-portrait.png' ); ?>" alt="<?php esc_attr_e( 'Eugène Rufin BOUYA, Président du Forum Numérique Congo', 'fnc-wordpress-theme' ); ?>" /></figure>
					<div class="president-identity">
						<p class="president-name"><?php esc_html_e( 'Eugène Rufin BOUYA', 'fnc-wordpress-theme' ); ?></p>
						<p class="president-role"><?php esc_html_e( 'Président du Forum Numérique Congo', 'fnc-wordpress-theme' ); ?></p>
					</div>
				</div>
				<div class="president-message">
					<p><?php esc_html_e( 'Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale.', 'fnc-wordpress-theme' ); ?></p>
					<p><?php esc_html_e( 'Entre les éditions, nous poursuivons un travail continu : réunir les institutions, les entreprises, les chercheurs et la société civile pour transformer une ambition nationale en trajectoire numérique lisible et partagée.', 'fnc-wordpress-theme' ); ?></p>
					<p><?php esc_html_e( 'Notre conviction est simple : le numérique n’est pas une fin, mais un moyen — celui de rendre nos services publics plus accessibles, nos entreprises plus compétitives et notre jeunesse mieux outillée.', 'fnc-wordpress-theme' ); ?></p>
					<p><?php esc_html_e( 'Je vous invite à rejoindre cette dynamique, depuis Brazzaville, au cœur du Bassin du Congo, et tournée vers toute l’Afrique centrale.', 'fnc-wordpress-theme' ); ?></p>
					<p style="margin-top:24px;"><a class="link-more" href="<?php echo esc_url( fnc_page_url( 'le-forum' ) ); ?>">← <?php esc_html_e( 'Retour à Le Forum', 'fnc-wordpress-theme' ); ?></a></p>
				</div>
			</div>
		</div>
	</section>

	<section class="callout">
		<h2><?php esc_html_e( 'Rejoignez le Forum', 'fnc-wordpress-theme' ); ?></h2>
		<p><?php esc_html_e( 'La prochaine édition réunit décideurs, experts et société civile autour du numérique. Prenez part au dialogue.', 'fnc-wordpress-theme' ); ?></p>
		<a class="btn btn-red" href="<?php echo esc_url( fnc_page_url( 'inscription' ) ); ?>"><?php esc_html_e( 'S’inscrire', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
