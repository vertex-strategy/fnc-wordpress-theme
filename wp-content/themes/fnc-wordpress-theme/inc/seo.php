<?php
/**
 * SEO par document + rendu des métadonnées (Lot 5).
 *
 * Pendant WordPress natif des `seoFields` de Payload (groupe `seo` avec titre
 * et description, étalé dans les collections du vrai site) et de la logique de
 * `generateMetadata()` : chaque document peut surcharger le titre et la
 * description, sinon on retombe sur le titre du document, puis sur les valeurs
 * par défaut du site (onglet « SEO par défaut » des Réglages FNC, Lot 1).
 *
 * Choix d'implémentation : ces champs vivent dans le THÈME et non dans le
 * plugin de modèle de contenu, alors que le vrai site les porte dans ses
 * collections. Raison : c'est le thème qui produit les balises, et le besoin
 * couvre uniformément les Pages/articles WordPress natifs *et* les contenus du
 * plugin — garder toute la cascade (document → défaut global) au même endroit
 * la rend beaucoup plus simple à raisonner.
 *
 * L'image de partage par document suit la convention WordPress : l'image mise
 * en avant sert d'`og:image`, avec repli sur l'image OpenGraph par défaut. Pas
 * de champ média supplémentaire à saisir.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FNC_META_SEO_TITLE       = '_fnc_seo_title';
const FNC_META_SEO_DESCRIPTION = '_fnc_seo_description';
const FNC_META_SEO_NOINDEX     = '_fnc_seo_noindex';

/**
 * Types de contenu recevant les champs SEO : Pages et articles natifs, plus
 * les contenus publics du plugin s'il est actif.
 *
 * @return array<int,string>
 */
function fnc_seo_post_types() {
	$types = array( 'page', 'post' );
	foreach ( array( 'fnc_edition', 'fnc_session', 'fnc_intervenant', 'fnc_partenaire', 'fnc_publication', 'fnc_actualite' ) as $cpt ) {
		if ( post_type_exists( $cpt ) ) {
			$types[] = $cpt;
		}
	}
	return $types;
}

/**
 * Expose les champs SEO en REST (éditeur de blocs / interopérabilité).
 */
function fnc_seo_register_meta() {
	foreach ( fnc_seo_post_types() as $type ) {
		register_post_meta( $type, FNC_META_SEO_TITLE, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
		register_post_meta( $type, FNC_META_SEO_DESCRIPTION, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
		register_post_meta( $type, FNC_META_SEO_NOINDEX, array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true ) );
	}
}
add_action( 'init', 'fnc_seo_register_meta', 20 );

/* ============================================================
   Métaboîte d'administration
   ============================================================ */

function fnc_seo_add_meta_box() {
	foreach ( fnc_seo_post_types() as $type ) {
		add_meta_box(
			'fnc_seo',
			__( 'SEO', 'fnc-wordpress-theme' ),
			'fnc_seo_render_meta_box',
			$type,
			'normal',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'fnc_seo_add_meta_box' );

function fnc_seo_render_meta_box( $post ) {
	wp_nonce_field( 'fnc_seo_save', 'fnc_seo_nonce' );

	$title       = get_post_meta( $post->ID, FNC_META_SEO_TITLE, true );
	$description = get_post_meta( $post->ID, FNC_META_SEO_DESCRIPTION, true );
	$noindex     = get_post_meta( $post->ID, FNC_META_SEO_NOINDEX, true );
	?>
	<p style="color:#61637a;margin-top:0;">
		<?php esc_html_e( 'Laissez vide pour utiliser le titre du contenu et les valeurs par défaut du site (Personnaliser → Réglages FNC → SEO par défaut).', 'fnc-wordpress-theme' ); ?>
	</p>

	<p>
		<label for="fnc_seo_title"><strong><?php esc_html_e( 'Titre SEO', 'fnc-wordpress-theme' ); ?></strong></label><br />
		<input type="text" id="fnc_seo_title" name="fnc_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%;" />
		<span class="description"><?php esc_html_e( 'Recommandé : 50–60 caractères.', 'fnc-wordpress-theme' ); ?></span>
	</p>

	<p>
		<label for="fnc_seo_description"><strong><?php esc_html_e( 'Description SEO', 'fnc-wordpress-theme' ); ?></strong></label><br />
		<textarea id="fnc_seo_description" name="fnc_seo_description" rows="3" style="width:100%;"><?php echo esc_textarea( $description ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Recommandé : 150–160 caractères.', 'fnc-wordpress-theme' ); ?></span>
	</p>

	<p>
		<label>
			<input type="checkbox" name="fnc_seo_noindex" value="1" <?php checked( $noindex, '1' ); ?> />
			<?php esc_html_e( 'Demander aux moteurs de ne pas indexer ce contenu (noindex, nofollow)', 'fnc-wordpress-theme' ); ?>
		</label>
	</p>

	<p style="color:#61637a;">
		<?php esc_html_e( 'L’image mise en avant de ce contenu sert d’image de partage (OpenGraph) ; à défaut, l’image par défaut du site est utilisée.', 'fnc-wordpress-theme' ); ?>
	</p>
	<?php
}

function fnc_seo_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['fnc_seo_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fnc_seo_nonce'] ) ), 'fnc_seo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, FNC_META_SEO_TITLE, isset( $_POST['fnc_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['fnc_seo_title'] ) ) : '' );
	update_post_meta( $post_id, FNC_META_SEO_DESCRIPTION, isset( $_POST['fnc_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fnc_seo_description'] ) ) : '' );
	update_post_meta( $post_id, FNC_META_SEO_NOINDEX, ! empty( $_POST['fnc_seo_noindex'] ) ? '1' : '' );
}
add_action( 'save_post', 'fnc_seo_save_meta', 10, 2 );

/* ============================================================
   Résolution des valeurs (cascade)
   ============================================================ */

/**
 * Titre SEO résolu : SEO du document → titre du document → titre par défaut
 * du site → nom du site. Même cascade que `generateMetadata()` du vrai site.
 *
 * @return string
 */
function fnc_seo_title() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_the_ID(), FNC_META_SEO_TITLE, true );
		if ( $custom ) {
			return $custom;
		}
		return get_the_title();
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	if ( is_search() ) {
		/* translators: %s: terme recherché. */
		return sprintf( __( 'Recherche : %s', 'fnc-wordpress-theme' ), get_search_query() );
	}

	$default = fnc_get_setting( 'seo_default_title', '' );
	return $default ? $default : fnc_site_name();
}

/**
 * Description SEO résolue : SEO du document → extrait du document →
 * description par défaut du site → description d'identité.
 *
 * @return string
 */
function fnc_seo_description() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_the_ID(), FNC_META_SEO_DESCRIPTION, true );
		if ( $custom ) {
			return $custom;
		}
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}
	}
	$default = fnc_get_setting( 'seo_default_description', '' );
	return $default ? $default : fnc_get_setting( 'description', '' );
}

/**
 * Image de partage résolue : image mise en avant du document → image
 * OpenGraph par défaut du site.
 *
 * @return string URL, ou chaîne vide.
 */
function fnc_seo_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return fnc_get_setting_image_url( 'og_default_image', 'full' );
}

/**
 * Ce document demande-t-il explicitement à ne pas être indexé ?
 *
 * @return bool
 */
function fnc_seo_is_noindex() {
	return is_singular() && '1' === (string) get_post_meta( get_the_ID(), FNC_META_SEO_NOINDEX, true );
}

/* ============================================================
   Rendu
   ============================================================ */

/**
 * Surcharge du <title> par le titre SEO du document, quand il est renseigné.
 * On laisse WordPress composer le reste (nom du site, séparateur, pagination).
 *
 * @param array<string,string> $parts
 * @return array<string,string>
 */
function fnc_seo_document_title_parts( $parts ) {
	if ( is_singular() ) {
		$custom = get_post_meta( get_the_ID(), FNC_META_SEO_TITLE, true );
		if ( $custom ) {
			$parts['title'] = $custom;
		}
	}
	return $parts;
}
add_filter( 'document_title_parts', 'fnc_seo_document_title_parts' );

/**
 * Métadonnées SEO/OpenGraph dans le <head>.
 *
 * Alimentées par la cascade ci-dessus : valeurs du document si renseignées,
 * sinon valeurs par défaut du site (Réglages FNC → SEO par défaut).
 */
function fnc_head_meta() {
	$site_name   = fnc_site_name();
	$description = fnc_seo_description();
	$title       = fnc_seo_title();
	$image       = fnc_seo_image();
	$twitter     = fnc_get_setting( 'twitter_card', 'summary_large_image' );

	$url = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$url = $url ? $url : home_url( '/' );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
	}
	if ( $twitter ) {
		printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( $twitter ) );
	}
}
add_action( 'wp_head', 'fnc_head_meta', 5 );

/**
 * Directive robots : `noindex` du document si demandé, sinon directive par
 * défaut du site. Passe par le filtre natif `wp_robots` pour ne pas dupliquer
 * la balise que WordPress émet déjà.
 *
 * @param array<string,mixed> $robots
 * @return array<string,mixed>
 */
function fnc_filter_robots( $robots ) {
	// Le refus d'indexation porté par le document prime sur le réglage global.
	if ( fnc_seo_is_noindex() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['index'], $robots['follow'] );
		return $robots;
	}

	$directive = fnc_get_setting( 'robots', '' );
	if ( ! $directive ) {
		return $robots;
	}
	$tokens = array_map( 'trim', explode( ',', strtolower( $directive ) ) );
	if ( in_array( 'noindex', $tokens, true ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}
	if ( in_array( 'index', $tokens, true ) ) {
		unset( $robots['noindex'] );
	}
	if ( in_array( 'nofollow', $tokens, true ) ) {
		$robots['nofollow'] = true;
		unset( $robots['follow'] );
	}
	if ( in_array( 'follow', $tokens, true ) ) {
		unset( $robots['nofollow'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'fnc_filter_robots' );
