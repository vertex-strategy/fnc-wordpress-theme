<?php
/**
 * Forum Numérique Congo — gabarit de la page « Espace presse ».
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

$fnc_route_h = fnc_route_hero( 'espace-presse' );
fnc_render_opening_hero(
	array(
		'eyebrow'    => $fnc_route_h['eyebrow'],
		'title'      => $fnc_route_h['title'],
		'intro'      => $fnc_route_h['intro'],
		'image'      => $fnc_route_h['image'],
		'image_alt'  => __( 'Espace presse du Forum Numérique Congo', 'fnc-wordpress-theme' ),
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
			<p class="eyebrow" data-fnc-st="espace-presse.intro.eyebrow"><?php echo esc_html( fnc_stitle( 'espace-presse', 'intro', 'eyebrow' ) ); ?></p>
			<h2 data-fnc-st="espace-presse.intro.title"><?php echo esc_html( fnc_stitle( 'espace-presse', 'intro', 'title' ) ); ?></h2>
			<p><?php esc_html_e( 'Les communiqués, documents et contacts presse apparaissent ici lorsqu’ils sont validés. Le Forum privilégie une information traçable, sobre et utilisable.', 'fnc-wordpress-theme' ); ?></p>
			<p class="frise-note" style="margin-top:14px;"><?php esc_html_e( 'Kit presse, contacts et modalités : à confirmer lorsque non publiés.', 'fnc-wordpress-theme' ); ?></p>
		</div>
	</section>

	<section class="section linen">
		<div class="container">
			<div class="section-head">
				<div>
					<p class="eyebrow" data-fnc-st="espace-presse.resources.eyebrow"><?php echo esc_html( fnc_stitle( 'espace-presse', 'resources', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="espace-presse.resources.title"><?php echo esc_html( fnc_stitle( 'espace-presse', 'resources', 'title' ) ); ?></h2>
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
					<p class="eyebrow" data-fnc-st="espace-presse.contacts.eyebrow"><?php echo esc_html( fnc_stitle( 'espace-presse', 'contacts', 'eyebrow' ) ); ?></p>
					<h2 data-fnc-st="espace-presse.contacts.title"><?php echo esc_html( fnc_stitle( 'espace-presse', 'contacts', 'title' ) ); ?></h2>
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
			<p class="eyebrow" data-fnc-st="espace-presse.accreditation.eyebrow"><?php echo esc_html( fnc_stitle( 'espace-presse', 'accreditation', 'eyebrow' ) ); ?></p>
			<h2 data-fnc-st="espace-presse.accreditation.title"><?php echo esc_html( fnc_stitle( 'espace-presse', 'accreditation', 'title' ) ); ?></h2>
			<p><?php esc_html_e( 'Il n’existe pas de formulaire d’accréditation : les journalistes peuvent utiliser la page Contact pour signaler leur demande.', 'fnc-wordpress-theme' ); ?></p>
			<p style="margin-top:20px;"><a class="link-more" href="<?php echo esc_url( fnc_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contacter l’organisation', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
		</div>
	</section>
</main>

<?php get_footer(); ?>
