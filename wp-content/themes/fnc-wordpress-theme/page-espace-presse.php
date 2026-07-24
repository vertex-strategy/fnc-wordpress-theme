<?php
/**
 * Gabarit de page — "Espace presse" (Dossier presse).
 *
 * Aligne sur la page reelle localhost:3000/fr/espace-presse : heros, section
 * « Information », communiques (Ressources filtrees par la categorie
 * « Communiques »), contacts presse (issus des Reglages FNC), et
 * « Accreditation » -> page Contact. Les communiques sont un type de Ressource,
 * pas une collection separee (comme le vrai site). Les contacts et communiques
 * restent en etat d'attente sobre tant qu'ils ne sont pas renseignes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

fnc_render_hero(
	array(
		'eyebrow'    => __( 'Presse', 'fnc-wordpress-theme' ),
		'title'      => __( 'Un point d’accès clair pour couvrir le Forum.', 'fnc-wordpress-theme' ),
		'lead'       => __( 'L’Espace presse rassemble uniquement les ressources publiées et les contacts confirmés, afin de faciliter un traitement médiatique fiable.', 'fnc-wordpress-theme' ),
		'image'      => get_template_directory_uri() . '/assets/images/la-main.png',
		'image_alt'  => __( 'Documents préparés dans une atmosphère institutionnelle calme', 'fnc-wordpress-theme' ),
		'breadcrumb' => __( 'Espace presse', 'fnc-wordpress-theme' ),
	)
);

$fnc_communiques_term = get_term_by( 'slug', 'communiques', 'fnc_categorie' );
$fnc_communiques      = $fnc_communiques_term
	? get_posts(
		array(
			'post_type'      => 'fnc_publication',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'fnc_categorie',
					'field'    => 'term_id',
					'terms'    => $fnc_communiques_term->term_id,
				),
			),
		)
	)
	: array();
?>

<main id="main">
	<section class="section">
		<div class="container reading">
			<p class="eyebrow"><?php esc_html_e( 'Information', 'fnc-wordpress-theme' ); ?></p>
			<h2><?php esc_html_e( 'Des ressources vérifiables, pas un dossier de façade.', 'fnc-wordpress-theme' ); ?></h2>
			<p><?php esc_html_e( 'Les communiqués, documents et contacts presse apparaissent ici lorsqu’ils sont validés. Le Forum privilégie une information traçable, sobre et utilisable.', 'fnc-wordpress-theme' ); ?></p>
			<p class="frise-note" style="margin-top:14px;"><?php esc_html_e( 'Kit presse, contacts et modalités : à confirmer lorsque non publiés.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Communiqués', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Ressources presse publiées.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Les communiqués de presse restent des ressources du portail. Chaque document publié conserve son lien vers la bibliothèque.', 'fnc-wordpress-theme' ); ?></p>
			</div>
			<?php if ( ! empty( $fnc_communiques ) ) : ?>
				<div class="grid grid-3">
					<?php foreach ( $fnc_communiques as $fnc_communique ) : ?>
						<article class="card fnc-card">
							<p class="card-kicker"><?php esc_html_e( 'Communiqué', 'fnc-wordpress-theme' ); ?></p>
							<h3><a href="<?php echo esc_url( get_permalink( $fnc_communique ) ); ?>"><?php echo esc_html( get_the_title( $fnc_communique ) ); ?></a></h3>
							<?php if ( has_excerpt( $fnc_communique ) ) : ?>
								<p><?php echo esc_html( get_the_excerpt( $fnc_communique ) ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Aucun communiqué n’est publié pour le moment.', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Les premiers communiqués apparaîtront ici dès leur validation officielle.', 'fnc-wordpress-theme' ); ?></p>
					<div class="meta" style="justify-content:center;"><span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Contacts', 'fnc-wordpress-theme' ); ?></p>
					<h2><?php esc_html_e( 'Contacts presse confirmés.', 'fnc-wordpress-theme' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Les contacts presse sont portés par les Réglages du site et réutilisables par les surfaces éditoriales.', 'fnc-wordpress-theme' ); ?></p>
			</div>
			<?php
			// Contacts presse issus des Réglages FNC (onglet « Contacts presse »).
			// Affichés uniquement s'ils sont renseignés — aucun nom/adresse fictif.
			$fnc_press_contacts = fnc_parse_press_contacts();
			if ( ! empty( $fnc_press_contacts ) ) :
				?>
				<div class="grid grid-3">
					<?php foreach ( $fnc_press_contacts as $fnc_contact ) : ?>
						<article class="card fnc-card">
							<?php if ( $fnc_contact['role'] ) : ?>
								<p class="card-kicker"><?php echo esc_html( fnc_pll( $fnc_contact['role'] ) ); ?></p>
							<?php endif; ?>
							<?php if ( $fnc_contact['name'] ) : ?>
								<h3><?php echo esc_html( $fnc_contact['name'] ); ?></h3>
							<?php endif; ?>
							<?php if ( $fnc_contact['organization'] ) : ?>
								<p><?php echo esc_html( fnc_pll( $fnc_contact['organization'] ) ); ?></p>
							<?php endif; ?>
							<?php if ( $fnc_contact['email'] ) : ?>
								<p style="margin-top:10px;"><a class="link-more" href="mailto:<?php echo esc_attr( antispambot( $fnc_contact['email'] ) ); ?>"><?php echo esc_html( antispambot( $fnc_contact['email'] ) ); ?></a></p>
							<?php endif; ?>
							<?php if ( $fnc_contact['phone'] ) : ?>
								<p><a class="link-more" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $fnc_contact['phone'] ) ); ?>"><?php echo esc_html( $fnc_contact['phone'] ); ?></a></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="empty" role="status">
					<h3><?php esc_html_e( 'Les contacts presse ne sont pas encore publiés.', 'fnc-wordpress-theme' ); ?></h3>
					<p><?php esc_html_e( 'Aucune adresse, aucun téléphone et aucun nom ne sont affichés tant qu’ils ne sont pas validés.', 'fnc-wordpress-theme' ); ?></p>
					<div class="meta" style="justify-content:center;"><span class="tbc"><?php esc_html_e( 'À confirmer', 'fnc-wordpress-theme' ); ?></span></div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section linen">
		<div class="container reading">
			<p class="eyebrow"><?php esc_html_e( 'Accréditation', 'fnc-wordpress-theme' ); ?></p>
			<h2><?php esc_html_e( 'L’accréditation reste un échange direct.', 'fnc-wordpress-theme' ); ?></h2>
			<p><?php esc_html_e( 'Il n’existe pas de formulaire d’accréditation : les journalistes peuvent utiliser la page Contact pour signaler leur demande.', 'fnc-wordpress-theme' ); ?></p>
			<p style="margin-top:20px;"><a class="link-more" href="<?php echo esc_url( fnc_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contacter l’organisation', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
		</div>
	</section>
</main>

<?php get_footer(); ?>
