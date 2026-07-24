<?php
/**
 * Gabarit de page — "Le Forum".
 *
 * Applique automatiquement (hierarchie de templates WordPress) a toute
 * Page dont le slug est "le-forum". Porte
 * docs/mockups/homepage-v2/interior/le-forum.html — presente dans le
 * dossier du projet mais non encore committee sur la branche de l'ADR-007
 * au moment du scaffold initial (voir ADR-007, note ajoutee a la demande
 * du Decideur). Contenu statique/exemple, non officiel — marqueurs
 * "A confirmer" conserves a l'identique de la maquette source.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/*
 * Lot 2 — composition par blocs. Sur le vrai site, « Le Forum » est une page
 * institutionnelle entierement composee de blocs administrables. Des que
 * l'editorial compose cette page avec des blocs FNC, ce gabarit s'efface au
 * profit du contenu compose ; sinon il conserve le contenu de demonstration
 * porte par la maquette (comportement inchange).
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
	<header class="opening">
		<img class="media-cover" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/la-salle.png' ); ?>" alt="<?php esc_attr_e( 'Salle plénière du Forum Numérique Congo', 'fnc-wordpress-theme' ); ?>" />
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php the_title(); ?></p>
			<span class="eyebrow"><?php esc_html_e( 'Ce que nous sommes', 'fnc-wordpress-theme' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<p class="intro"><?php esc_html_e( '« Le Forum Numérique Congo installe un espace permanent de dialogue, de coopération et de décision autour du numérique en Afrique centrale. »', 'fnc-wordpress-theme' ); ?></p>
		</div>
		<svg class="pcb" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
			<path class="r" d="M0 40 H420 l20 -20 H820 l20 20 H1200"/>
			<path class="y" d="M0 20 H300 l24 20 H900 l18 -14 H1200"/>
		</svg>
	</header>

	<!-- Notre mission -->
	<section class="section">
		<div class="split">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Notre mission', 'fnc-wordpress-theme' ); ?></span>
				<h2 class="lines"><?php esc_html_e( 'Comprendre.', 'fnc-wordpress-theme' ); ?><br><?php esc_html_e( 'Rassembler.', 'fnc-wordpress-theme' ); ?><br><span class="muted"><?php esc_html_e( 'Décider ensemble.', 'fnc-wordpress-theme' ); ?></span></h2>
				<p class="body"><?php esc_html_e( 'Le Forum réunit institutions, entreprises, chercheurs et société civile pour transformer une ambition nationale en trajectoire numérique lisible. Un rendez-vous majeur tous les deux ans, un travail continu entre les éditions.', 'fnc-wordpress-theme' ); ?></p>
			</div>
			<figure><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-panel.png' ); ?>" alt="<?php esc_attr_e( 'Panel de discussion au Forum Numérique Congo', 'fnc-wordpress-theme' ); ?>" /></figure>
		</div>
	</section>

	<!-- Nos objectifs -->
	<section class="section linen">
		<span class="eyebrow"><?php esc_html_e( 'Nos objectifs', 'fnc-wordpress-theme' ); ?></span>
		<h2 style="max-width:18ch"><?php esc_html_e( 'Ce que le Forum rend possible', 'fnc-wordpress-theme' ); ?></h2>
		<div class="obj">
			<?php
			$fnc_objectifs = array(
				array(
					'titre' => __( 'Comprendre', 'fnc-wordpress-theme' ),
					'texte' => __( 'Éclairer les enjeux du numérique pour l’Afrique centrale, sans jargon ni importation de modèles.', 'fnc-wordpress-theme' ),
				),
				array(
					'titre' => __( 'Dialoguer', 'fnc-wordpress-theme' ),
					'texte' => __( 'Créer un espace de parole partagé entre décideurs publics, acteurs privés et citoyens.', 'fnc-wordpress-theme' ),
				),
				array(
					'titre' => __( 'Coopérer', 'fnc-wordpress-theme' ),
					'texte' => __( 'Faire émerger des coopérations concrètes, régionales et durables.', 'fnc-wordpress-theme' ),
				),
				array(
					'titre' => __( 'Décider', 'fnc-wordpress-theme' ),
					'texte' => __( 'Transformer les échanges en décisions et en engagements traçables.', 'fnc-wordpress-theme' ),
				),
			);
			foreach ( $fnc_objectifs as $fnc_i => $fnc_objectif ) :
				?>
				<div class="row">
					<span class="n"><?php echo esc_html( str_pad( (string) ( $fnc_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<div>
						<h3><?php echo esc_html( $fnc_objectif['titre'] ); ?></h3>
						<p><?php echo esc_html( $fnc_objectif['texte'] ); ?></p>
					</div>
				</div>
				<?php
			endforeach;
			?>
		</div>
	</section>

	<!-- Territoire -->
	<section class="territory">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/le-territoire-brazzaville.png' ); ?>" alt="<?php esc_attr_e( 'Brazzaville, République du Congo', 'fnc-wordpress-theme' ); ?>" />
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<span class="eyebrow"><?php esc_html_e( 'Notre ancrage', 'fnc-wordpress-theme' ); ?></span>
			<h2 class="manifest"><?php esc_html_e( 'Depuis Brazzaville,', 'fnc-wordpress-theme' ); ?> <span class="light"><?php esc_html_e( 'au cœur du Bassin du Congo.', 'fnc-wordpress-theme' ); ?></span></h2>
			<p class="sub"><?php esc_html_e( 'Une parole ancrée dans son territoire, tournée vers toute l’Afrique centrale.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<!-- Callout -->
	<section class="callout">
		<h2><?php esc_html_e( 'Participer au Forum', 'fnc-wordpress-theme' ); ?></h2>
		<p><?php esc_html_e( 'Rejoignez la prochaine édition. L’ouverture des accréditations sera annoncée prochainement.', 'fnc-wordpress-theme' ); ?> <span class="tbc" style="color:#fff"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></p>
		<a class="btn btn-red" href="<?php echo esc_url( fnc_page_url( 'inscription' ) ); ?>"><?php esc_html_e( 'S’inscrire', 'fnc-wordpress-theme' ); ?>
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</section>
</main>

<?php get_footer(); ?>
