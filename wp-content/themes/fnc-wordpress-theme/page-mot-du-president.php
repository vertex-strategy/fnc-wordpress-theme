<?php
/**
 * Gabarit de page — "Mot du président".
 *
 * Structure alignee sur le site officiel reel
 * (localhost:3000/fr/le-forum/mot-du-president), suite a l'amendement
 * de la Decision 1 de l'ADR-007.
 *
 * IMPORTANT : la page reelle attribue ce message a une personne
 * nommee et cite un texte institutionnel reel. Ni le nom ni le texte
 * ne sont repris ici — coherent avec le reste de ce theme (aucune
 * vraie identite de responsable public reprise dans ce produit
 * distinct) et avec la maquette source elle-meme
 * (site.js, copy.president : "Aucune citation n'est inventee").
 * Le contenu ci-dessous est un placeholder explicite, pas une
 * paraphrase du texte reel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main">
	<header class="opening" style="min-height:56vh;">
		<img class="media-cover" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-portrait.png' ); ?>" alt="<?php esc_attr_e( 'Portrait institutionnel', 'fnc-wordpress-theme' ); ?>" />
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php esc_html_e( 'Le Forum', 'fnc-wordpress-theme' ); ?> · <?php esc_html_e( 'Mot du Président', 'fnc-wordpress-theme' ); ?></p>
			<span class="eyebrow"><?php esc_html_e( 'Mot du Président', 'fnc-wordpress-theme' ); ?></span>
			<h1><?php esc_html_e( 'Le mot du Président', 'fnc-wordpress-theme' ); ?></h1>
		</div>
	</header>

	<section class="section">
		<div class="container reading">
			<p><?php esc_html_e( 'Nom du Président', 'fnc-wordpress-theme' ); ?> <span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
			<p style="color:var(--texte-tert);font-size:.95rem;"><?php esc_html_e( 'Fonction officielle — à valider par la MOA', 'fnc-wordpress-theme' ); ?></p>
			<p style="margin-top:24px;"><?php esc_html_e( '« La prise de parole institutionnelle apparaîtra ici une fois validée par la MOA. Aucune citation n’est inventée. »', 'fnc-wordpress-theme' ); ?></p>
			<p style="margin-top:24px;"><a class="link-more" href="<?php echo esc_url( home_url( '/le-forum/' ) ); ?>">← <?php esc_html_e( 'Retour à Le Forum', 'fnc-wordpress-theme' ); ?></a></p>
		</div>
	</section>

	<?php fnc_render_cta_band(); ?>
</main>

<?php get_footer(); ?>
