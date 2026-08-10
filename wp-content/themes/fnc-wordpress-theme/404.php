<?php
/**
 * Forum Numérique Congo — page « introuvable » (404).
 *
 * Reproduit la signature du site Next (src/app/(frontend)/[locale]/not-found.tsx) :
 * héros cinématographique `.opening` plein cadre avec la photographie « Les portes »
 * (portes de la plénière entrouvertes — métaphore de la page qu'on ne trouve pas),
 * overlay, fil d'Ariane, eyebrow, chapô et deux actions. Le bloc remplit la hauteur
 * disponible (main en flex + `.opening` flex-1) : l'image descend jusqu'au footer,
 * aucun vide. La navigation étant `position:fixed`, elle surimprime le haut de la
 * photo comme sur la page d'accueil.
 *
 * Localisée (FR/EN via le domaine de traduction) et non indexable (la directive
 * robots noindex est posée par inc/seo.php sur is_404()). Sert aussi d'état de
 * repli aux surfaces sous feature flag fermé (ex. actualités désactivées).
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

$fnc_404_title = __( 'Page non trouvée', 'fnc-wordpress-theme' );
$fnc_404_image = get_template_directory_uri() . '/assets/images/les-portes.png';
?>
<main id="main" class="fnc-404 flex flex-col" style="min-height:100dvh;">
	<?php
	// paddingBottom explicite : le contenu est ancré en bas (.opening = flex-end)
	// et le filet PCB occupe les 60px du bas — sans ce dégagement, les boutons
	// chevauchent la frise (même valeur que le Next).
	?>
	<header class="opening flex-1" style="padding-bottom:clamp(88px,11vh,124px);">
		<?php echo fnc_theme_image( $fnc_404_image, '', array( 'class' => 'media-cover', 'eager' => true, 'aria_hidden' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup échappé dans le helper. ?>
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php echo esc_html( $fnc_404_title ); ?></p>
			<span class="eyebrow"><?php esc_html_e( 'Erreur 404', 'fnc-wordpress-theme' ); ?></span>
			<h1><?php echo esc_html( $fnc_404_title ); ?></h1>
			<p class="intro"><?php esc_html_e( 'La page demandée n’existe pas ou n’est plus disponible.', 'fnc-wordpress-theme' ); ?></p>
			<div style="margin-top:2rem;display:flex;flex-wrap:wrap;gap:.75rem;">
				<a class="btn btn-white" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l’accueil', 'fnc-wordpress-theme' ); ?></a>
				<a class="btn btn-ghost-light" href="<?php echo esc_url( fnc_archive_url( 'fnc_edition' ) ); ?>"><?php esc_html_e( 'Voir les éditions', 'fnc-wordpress-theme' ); ?></a>
			</div>
		</div>
		<svg class="pcb-band" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
			<path class="r" d="M0 40 H420 l20 -20 H820 l20 20 H1200" />
			<path class="y" d="M0 20 H300 l24 20 H900 l18 -14 H1200" />
		</svg>
	</header>
</main>
<?php
get_footer();
