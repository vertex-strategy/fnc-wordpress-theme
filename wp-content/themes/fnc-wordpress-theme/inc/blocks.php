<?php
/**
 * Blocs éditoriaux — composition de pages (Lot 2).
 *
 * Pendant WordPress natif des palettes de blocs Payload :
 *   - institutionalSections.ts → blocs « fnc/inst-* » (Le Forum, Contact…)
 *   - pageSections.ts          → blocs « fnc/* » génériques (page composable)
 *
 * Principe repris à l'identique du vrai site : **la DA reste dans le code**.
 * Chaque bloc est un bloc dynamique (rendu serveur via `render_callback`) qui
 * produit le markup DA figé du thème ; l'éditeur n'administre que le contenu,
 * les médias et les CTA — jamais la mise en forme ni la structure.
 *
 * Implémentation « pilotée par schéma » : les champs de chaque bloc sont
 * déclarés une seule fois ci-dessous, puis (a) convertis en attributs de bloc,
 * (b) rendus en PHP, (c) transmis au script d'édition générique
 * (assets/js/blocks.js) qui construit l'interface. Cela évite d'écrire 14
 * composants d'édition distincts et respecte le « zéro dépendance tierce »
 * (ADR-007, Décision 2) — aucun build JS, aucun plugin de champs.
 *
 * Frontière assumée : les champs « richtext » sont des zones de texte simples
 * rendues avec `wpautop()`. Le vrai site utilise un éditeur riche Lexical ;
 * répliquer un éditeur riche multi-champs sans build JS n'apporterait pas assez
 * pour la complexité induite sur un produit vitrine.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Schémas de blocs
   ============================================================ */

/**
 * Définition de tous les blocs éditoriaux du thème.
 *
 * @return array<string,array<string,mixed>>
 */
function fnc_block_schemas() {
	$cta_fields = array(
		array( 'name' => 'ctaLabel', 'type' => 'text', 'label' => __( 'Libellé du bouton', 'fnc-wordpress-theme' ) ),
		array( 'name' => 'ctaHref', 'type' => 'url', 'label' => __( 'Lien du bouton', 'fnc-wordpress-theme' ) ),
	);

	$faq_fields = array(
		array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
		array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
		array( 'name' => 'intro', 'type' => 'textarea', 'label' => __( 'Introduction', 'fnc-wordpress-theme' ) ),
		array(
			'name'      => 'items',
			'type'      => 'repeater',
			'label'     => __( 'Questions', 'fnc-wordpress-theme' ),
			'subfields' => array(
				array( 'name' => 'question', 'type' => 'text', 'label' => __( 'Question', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'answer', 'type' => 'textarea', 'label' => __( 'Réponse', 'fnc-wordpress-theme' ) ),
			),
		),
	);

	return array(
		/* ---------------- Palette institutionnelle ---------------- */
		'inst-hero'       => array(
			'title'       => __( 'Ouverture (institutionnel)', 'fnc-wordpress-theme' ),
			'icon'        => 'cover-image',
			'description' => __( 'Héros pleine hauteur avec image, fil d’Ariane et introduction.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'image', 'type' => 'image', 'label' => __( 'Image d’ouverture', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'breadcrumb', 'type' => 'text', 'label' => __( 'Fil d’Ariane (libellé page)', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'titleA', 'type' => 'text', 'label' => __( 'Titre — ligne 1', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'titleB', 'type' => 'text', 'label' => __( 'Titre — ligne 2', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'intro', 'type' => 'textarea', 'label' => __( 'Introduction', 'fnc-wordpress-theme' ) ),
			),
		),
		'inst-president'  => array(
			'title'       => __( 'Mot du Président', 'fnc-wordpress-theme' ),
			'icon'        => 'admin-users',
			'description' => __( 'Portrait, identité et extrait du mot du Président.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'photo', 'type' => 'image', 'label' => __( 'Portrait', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'name', 'type' => 'text', 'label' => __( 'Nom', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'role', 'type' => 'text', 'label' => __( 'Fonction', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'excerpt', 'type' => 'textarea', 'label' => __( 'Extrait (affiché sur la page)', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'message', 'type' => 'richtext', 'label' => __( 'Mot complet', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'ctaLabel', 'type' => 'text', 'label' => __( 'Libellé du lien « lire tout »', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'ctaHref', 'type' => 'url', 'label' => __( 'Lien « lire tout »', 'fnc-wordpress-theme' ) ),
			),
		),
		'inst-split'      => array(
			'title'       => __( 'Mission (média + texte)', 'fnc-wordpress-theme' ),
			'icon'        => 'align-pull-left',
			'description' => __( 'Titre sur trois lignes, texte et image.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'l1', 'type' => 'text', 'label' => __( 'Titre — ligne 1', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'l2', 'type' => 'text', 'label' => __( 'Titre — ligne 2', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'l3', 'type' => 'text', 'label' => __( 'Titre — ligne 3 (atténuée)', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'body', 'type' => 'textarea', 'label' => __( 'Texte', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'image', 'type' => 'image', 'label' => __( 'Image', 'fnc-wordpress-theme' ) ),
			),
		),
		'inst-objectives' => array(
			'title'       => __( 'Objectifs (séquence numérotée)', 'fnc-wordpress-theme' ),
			'icon'        => 'editor-ol',
			'description' => __( 'Séquence numérotée ; la numérotation et les couleurs DA sont automatiques.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
				array(
					'name'      => 'items',
					'type'      => 'repeater',
					'label'     => __( 'Objectifs', 'fnc-wordpress-theme' ),
					'subfields' => array(
						array( 'name' => 't', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
						array( 'name' => 'd', 'type' => 'textarea', 'label' => __( 'Description', 'fnc-wordpress-theme' ) ),
					),
				),
			),
		),
		'inst-faq'        => array(
			'title'       => __( 'FAQ (institutionnel)', 'fnc-wordpress-theme' ),
			'icon'        => 'editor-help',
			'description' => __( 'Questions fréquentes, dépliables.', 'fnc-wordpress-theme' ),
			'fields'      => $faq_fields,
		),
		'inst-manifesto'  => array(
			'title'       => __( 'Ancrage (manifeste pleine largeur)', 'fnc-wordpress-theme' ),
			'icon'        => 'format-image',
			'description' => __( 'Bande pleine largeur avec image de fond et manifeste.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'image', 'type' => 'image', 'label' => __( 'Image pleine largeur', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'manifestA', 'type' => 'text', 'label' => __( 'Manifeste — début', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'manifestB', 'type' => 'text', 'label' => __( 'Manifeste — accent', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'sub', 'type' => 'textarea', 'label' => __( 'Sous-texte', 'fnc-wordpress-theme' ) ),
			),
		),
		'inst-callout'    => array(
			'title'       => __( 'Callout (CTA)', 'fnc-wordpress-theme' ),
			'icon'        => 'megaphone',
			'description' => __( 'Bandeau d’appel à action sur fond navy.', 'fnc-wordpress-theme' ),
			'fields'      => array_merge(
				array(
					array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'desc', 'type' => 'textarea', 'label' => __( 'Texte', 'fnc-wordpress-theme' ) ),
				),
				$cta_fields
			),
		),

		/* ---------------- Palette générique (page composable) ---------------- */
		'hero'            => array(
			'title'       => __( 'Héros', 'fnc-wordpress-theme' ),
			'icon'        => 'align-full-width',
			'description' => __( 'Héros secondaire avec image de fond et CTA.', 'fnc-wordpress-theme' ),
			'fields'      => array_merge(
				array(
					array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'subtitle', 'type' => 'text', 'label' => __( 'Sous-titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'body', 'type' => 'textarea', 'label' => __( 'Texte', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'image', 'type' => 'image', 'label' => __( 'Image de fond', 'fnc-wordpress-theme' ) ),
				),
				$cta_fields
			),
		),
		'richtext'        => array(
			'title'       => __( 'Texte riche', 'fnc-wordpress-theme' ),
			'icon'        => 'editor-paragraph',
			'description' => __( 'Bloc de lecture (colonne étroite).', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
				array( 'name' => 'content', 'type' => 'richtext', 'label' => __( 'Contenu', 'fnc-wordpress-theme' ) ),
			),
		),
		'split'           => array(
			'title'       => __( 'Média + texte', 'fnc-wordpress-theme' ),
			'icon'        => 'align-pull-right',
			'description' => __( 'Texte et image côte à côte, image à droite ou à gauche.', 'fnc-wordpress-theme' ),
			'fields'      => array_merge(
				array(
					array( 'name' => 'eyebrow', 'type' => 'text', 'label' => __( 'Sur-titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'body', 'type' => 'richtext', 'label' => __( 'Texte', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'image', 'type' => 'image', 'label' => __( 'Image', 'fnc-wordpress-theme' ) ),
					array(
						'name'    => 'mediaSide',
						'type'    => 'select',
						'label'   => __( 'Côté de l’image', 'fnc-wordpress-theme' ),
						'default' => 'right',
						'options' => array(
							'right' => __( 'Droite', 'fnc-wordpress-theme' ),
							'left'  => __( 'Gauche', 'fnc-wordpress-theme' ),
						),
					),
				),
				$cta_fields
			),
		),
		'stats'           => array(
			'title'       => __( 'Chiffres clés', 'fnc-wordpress-theme' ),
			'icon'        => 'chart-bar',
			'description' => __( 'Bande de chiffres clés.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
				array(
					'name'      => 'items',
					'type'      => 'repeater',
					'label'     => __( 'Chiffres', 'fnc-wordpress-theme' ),
					'subfields' => array(
						array( 'name' => 'value', 'type' => 'text', 'label' => __( 'Valeur', 'fnc-wordpress-theme' ) ),
						array( 'name' => 'label', 'type' => 'text', 'label' => __( 'Libellé', 'fnc-wordpress-theme' ) ),
					),
				),
			),
		),
		'cta'             => array(
			'title'       => __( 'Appel à action', 'fnc-wordpress-theme' ),
			'icon'        => 'external',
			'description' => __( 'Bandeau CTA pleine largeur.', 'fnc-wordpress-theme' ),
			'fields'      => array_merge(
				array(
					array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
					array( 'name' => 'text', 'type' => 'textarea', 'label' => __( 'Texte', 'fnc-wordpress-theme' ) ),
				),
				$cta_fields
			),
		),
		'faq'             => array(
			'title'       => __( 'FAQ', 'fnc-wordpress-theme' ),
			'icon'        => 'editor-help',
			'description' => __( 'Questions fréquentes, dépliables.', 'fnc-wordpress-theme' ),
			'fields'      => $faq_fields,
		),
		'documents'       => array(
			'title'       => __( 'Documents', 'fnc-wordpress-theme' ),
			'icon'        => 'media-document',
			'description' => __( 'Liste de fichiers à télécharger.', 'fnc-wordpress-theme' ),
			'fields'      => array(
				array( 'name' => 'title', 'type' => 'text', 'label' => __( 'Titre', 'fnc-wordpress-theme' ) ),
				array(
					'name'      => 'items',
					'type'      => 'repeater',
					'label'     => __( 'Fichiers', 'fnc-wordpress-theme' ),
					'subfields' => array(
						array( 'name' => 'label', 'type' => 'text', 'label' => __( 'Libellé', 'fnc-wordpress-theme' ) ),
						array( 'name' => 'file', 'type' => 'file', 'label' => __( 'Fichier', 'fnc-wordpress-theme' ) ),
					),
				),
			),
		),
	);
}

/**
 * Convertit un schéma de champs en attributs de bloc Gutenberg.
 *
 * @param array<int,array<string,mixed>> $fields
 * @return array<string,array<string,mixed>>
 */
function fnc_block_attributes_from_fields( $fields ) {
	$attributes = array();
	foreach ( $fields as $field ) {
		switch ( $field['type'] ) {
			case 'image':
			case 'file':
				$attributes[ $field['name'] ] = array( 'type' => 'number', 'default' => 0 );
				break;
			case 'repeater':
				$attributes[ $field['name'] ] = array( 'type' => 'array', 'default' => array() );
				break;
			default:
				$attributes[ $field['name'] ] = array(
					'type'    => 'string',
					'default' => isset( $field['default'] ) ? $field['default'] : '',
				);
		}
	}
	return $attributes;
}

/* ============================================================
   2. Enregistrement
   ============================================================ */

/**
 * Catégorie de blocs dédiée au thème.
 *
 * @param array<int,array<string,mixed>> $categories
 * @return array<int,array<string,mixed>>
 */
function fnc_block_category( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'fnc',
				'title' => __( 'Forum Numérique Congo', 'fnc-wordpress-theme' ),
				'icon'  => null,
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'fnc_block_category' );

/**
 * Enregistre tous les blocs comme blocs dynamiques (rendu serveur).
 */
function fnc_register_blocks() {
	foreach ( fnc_block_schemas() as $slug => $schema ) {
		register_block_type(
			'fnc/' . $slug,
			array(
				'api_version'     => 2,
				'title'           => $schema['title'],
				'category'        => 'fnc',
				'icon'            => $schema['icon'],
				'description'     => $schema['description'],
				'attributes'      => fnc_block_attributes_from_fields( $schema['fields'] ),
				'render_callback' => 'fnc_render_block',
				'supports'        => array( 'html' => false, 'reusable' => true ),
			)
		);
	}
}
add_action( 'init', 'fnc_register_blocks' );

/**
 * Charge le moteur d'édition générique et lui transmet les schémas.
 */
function fnc_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'fnc-blocks',
		get_template_directory_uri() . '/assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-data' ),
		FNC_THEME_VERSION,
		true
	);

	// Schémas transmis au JS : une seule source de vérité (ce fichier).
	$payload = array();
	foreach ( fnc_block_schemas() as $slug => $schema ) {
		$payload[ 'fnc/' . $slug ] = array(
			'title'       => $schema['title'],
			'icon'        => $schema['icon'],
			'description' => $schema['description'],
			'fields'      => $schema['fields'],
			'attributes'  => fnc_block_attributes_from_fields( $schema['fields'] ),
		);
	}
	wp_add_inline_script(
		'fnc-blocks',
		'window.fncBlockSchemas = ' . wp_json_encode( $payload ) . ';',
		'before'
	);

	wp_enqueue_style(
		'fnc-blocks-editor',
		get_template_directory_uri() . '/assets/css/blocks-editor.css',
		array(),
		FNC_THEME_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'fnc_enqueue_block_editor_assets' );

/* ============================================================
   3. Rendu serveur (markup DA figé)
   ============================================================ */

/**
 * Dispatcher de rendu : délègue au renderer du bloc concerné.
 *
 * @param array<string,mixed>  $attributes
 * @param string               $content
 * @param WP_Block|null        $block
 * @return string
 */
function fnc_render_block( $attributes, $content = '', $block = null ) {
	$name = ( $block && isset( $block->name ) ) ? $block->name : '';
	$slug = str_replace( 'fnc/', '', $name );
	$fn   = 'fnc_render_block_' . str_replace( '-', '_', $slug );

	if ( ! function_exists( $fn ) ) {
		return '';
	}
	return call_user_func( $fn, $attributes );
}

/**
 * La page courante (ou le contenu fourni) est-elle composée avec au moins un
 * bloc éditorial FNC ?
 *
 * Permet aux gabarits dédiés (page-le-forum.php, page-contact.php…) de céder la
 * place au contenu composé dès que l'éditorial a commencé à l'administrer, tout
 * en conservant leur contenu de démonstration tant que ce n'est pas le cas.
 *
 * @param int|WP_Post|null $post
 * @return bool
 */
function fnc_page_has_blocks( $post = null ) {
	foreach ( array_keys( fnc_block_schemas() ) as $slug ) {
		if ( has_block( 'fnc/' . $slug, $post ) ) {
			return true;
		}
	}
	return false;
}

/** Valeur d'attribut sécurisée. */
function fnc_attr( $attributes, $key, $default = '' ) {
	return isset( $attributes[ $key ] ) && '' !== $attributes[ $key ] ? $attributes[ $key ] : $default;
}

/** Items d'un répéteur, filtrés des lignes vides. */
function fnc_attr_items( $attributes, $key = 'items' ) {
	$items = isset( $attributes[ $key ] ) && is_array( $attributes[ $key ] ) ? $attributes[ $key ] : array();
	return array_values(
		array_filter(
			$items,
			static function ( $item ) {
				return is_array( $item ) && '' !== trim( implode( '', array_map( 'strval', $item ) ) );
			}
		)
	);
}

/** URL d'une image d'attachement. */
function fnc_attachment_url( $id, $size = 'full' ) {
	$id = (int) $id;
	if ( $id <= 0 ) {
		return '';
	}
	$src = wp_get_attachment_image_src( $id, $size );
	return $src ? $src[0] : '';
}

/** Rendu d'un texte multi-ligne en paragraphes. */
function fnc_render_rich( $text ) {
	return wp_kses_post( wpautop( $text ) );
}

/** Bande décorative PCB partagée par les ouvertures. */
function fnc_pcb_band() {
	return '<svg class="pcb" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">'
		. '<path class="r" d="M0 40 H420 l20 -20 H820 l20 20 H1200"/>'
		. '<path class="y" d="M0 20 H300 l24 20 H900 l18 -14 H1200"/>'
		. '</svg>';
}

/** Bouton CTA partagé (rendu uniquement si libellé ET lien sont fournis). */
function fnc_render_cta_button( $label, $href, $class = 'btn btn-red' ) {
	if ( ! $label || ! $href ) {
		return '';
	}
	return sprintf(
		'<a class="%1$s" href="%2$s">%3$s<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>',
		esc_attr( $class ),
		esc_url( $href ),
		esc_html( $label )
	);
}

/* ---------------- Palette institutionnelle ---------------- */

function fnc_render_block_inst_hero( $a ) {
	$image      = fnc_attachment_url( fnc_attr( $a, 'image', 0 ) );
	$breadcrumb = fnc_attr( $a, 'breadcrumb' );
	$eyebrow    = fnc_attr( $a, 'eyebrow' );
	$title_a    = fnc_attr( $a, 'titleA' );
	$title_b    = fnc_attr( $a, 'titleB' );
	$intro      = fnc_attr( $a, 'intro' );

	ob_start();
	?>
	<header class="opening">
		<?php if ( $image ) : ?>
			<img src="<?php echo esc_url( $image ); ?>" alt="" />
		<?php endif; ?>
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<?php if ( $breadcrumb ) : ?>
				<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'fnc-wordpress-theme' ); ?></a> · <?php echo esc_html( $breadcrumb ); ?></p>
			<?php endif; ?>
			<?php if ( $eyebrow ) : ?>
				<span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $title_a || $title_b ) : ?>
				<h1><?php echo esc_html( $title_a ); ?><?php echo $title_b ? '<br />' . esc_html( $title_b ) : ''; ?></h1>
			<?php endif; ?>
			<?php if ( $intro ) : ?>
				<p class="intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
		<?php echo fnc_pcb_band(); // phpcs:ignore WordPress.Security.EscapeOutput -- markup statique. ?>
	</header>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_inst_split( $a ) {
	$image = fnc_attachment_url( fnc_attr( $a, 'image', 0 ) );
	$l1    = fnc_attr( $a, 'l1' );
	$l2    = fnc_attr( $a, 'l2' );
	$l3    = fnc_attr( $a, 'l3' );

	ob_start();
	?>
	<section class="section">
		<div class="split">
			<div>
				<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></span>
				<?php endif; ?>
				<?php if ( $l1 || $l2 || $l3 ) : ?>
					<h2 class="lines">
						<?php echo esc_html( $l1 ); ?>
						<?php echo $l2 ? '<br />' . esc_html( $l2 ) : ''; ?>
						<?php echo $l3 ? '<br /><span class="muted">' . esc_html( $l3 ) . '</span>' : ''; ?>
					</h2>
				<?php endif; ?>
				<?php if ( fnc_attr( $a, 'body' ) ) : ?>
					<p class="body"><?php echo esc_html( fnc_attr( $a, 'body' ) ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $image ) : ?>
				<figure><img src="<?php echo esc_url( $image ); ?>" alt="" /></figure>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_inst_objectives( $a ) {
	$items = fnc_attr_items( $a );
	if ( empty( $items ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="section linen">
		<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
			<span class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></span>
		<?php endif; ?>
		<?php if ( fnc_attr( $a, 'title' ) ) : ?>
			<h2 style="max-width:18ch"><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
		<?php endif; ?>
		<div class="obj">
			<?php foreach ( $items as $i => $item ) : ?>
				<div class="row">
					<span class="n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<div>
						<?php if ( ! empty( $item['t'] ) ) : ?>
							<h3><?php echo esc_html( $item['t'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['d'] ) ) : ?>
							<p><?php echo esc_html( $item['d'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_inst_manifesto( $a ) {
	$image = fnc_attachment_url( fnc_attr( $a, 'image', 0 ) );

	ob_start();
	?>
	<section class="territory">
		<?php if ( $image ) : ?>
			<img src="<?php echo esc_url( $image ); ?>" alt="" />
		<?php endif; ?>
		<div class="ov" aria-hidden="true"></div>
		<div class="inner">
			<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
				<span class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></span>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'manifestA' ) || fnc_attr( $a, 'manifestB' ) ) : ?>
				<h2 class="manifest">
					<?php echo esc_html( fnc_attr( $a, 'manifestA' ) ); ?>
					<?php echo fnc_attr( $a, 'manifestB' ) ? ' <span class="light">' . esc_html( fnc_attr( $a, 'manifestB' ) ) . '</span>' : ''; ?>
				</h2>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'sub' ) ) : ?>
				<p class="sub"><?php echo esc_html( fnc_attr( $a, 'sub' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_inst_callout( $a ) {
	ob_start();
	?>
	<section class="callout">
		<?php if ( fnc_attr( $a, 'title' ) ) : ?>
			<h2><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
		<?php endif; ?>
		<?php if ( fnc_attr( $a, 'desc' ) ) : ?>
			<p><?php echo esc_html( fnc_attr( $a, 'desc' ) ); ?></p>
		<?php endif; ?>
		<?php echo fnc_render_cta_button( fnc_attr( $a, 'ctaLabel' ), fnc_attr( $a, 'ctaHref' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans le helper. ?>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_inst_president( $a ) {
	$photo   = fnc_attachment_url( fnc_attr( $a, 'photo', 0 ) );
	$excerpt = fnc_attr( $a, 'excerpt' );
	$message = fnc_attr( $a, 'message' );

	ob_start();
	?>
	<section class="section president-block">
		<div class="split">
			<div>
				<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></span>
				<?php endif; ?>
				<?php if ( $excerpt ) : ?>
					<p class="president-excerpt">« <?php echo esc_html( $excerpt ); ?> »</p>
				<?php endif; ?>
				<?php if ( $message ) : ?>
					<div class="president-message"><?php echo fnc_render_rich( $message ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé par wp_kses_post. ?></div>
				<?php endif; ?>
				<?php if ( fnc_attr( $a, 'name' ) || fnc_attr( $a, 'role' ) ) : ?>
					<div class="president-identity">
						<?php if ( fnc_attr( $a, 'name' ) ) : ?>
							<p class="president-name"><?php echo esc_html( fnc_attr( $a, 'name' ) ); ?></p>
						<?php endif; ?>
						<?php if ( fnc_attr( $a, 'role' ) ) : ?>
							<p class="president-role"><?php echo esc_html( fnc_attr( $a, 'role' ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php
				$cta = fnc_render_cta_button( fnc_attr( $a, 'ctaLabel' ), fnc_attr( $a, 'ctaHref' ), 'link-more' );
				echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans le helper.
				?>
			</div>
			<?php if ( $photo ) : ?>
				<figure><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( fnc_attr( $a, 'name' ) ); ?>" /></figure>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/** FAQ — markup partagé par inst-faq et faq. */
function fnc_render_faq_section( $a, $linen = false ) {
	$items = fnc_attr_items( $a );
	if ( empty( $items ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="section<?php echo $linen ? ' linen' : ''; ?>">
		<div class="container">
			<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></p>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'title' ) ) : ?>
				<h2><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'intro' ) ) : ?>
				<p class="faq-intro"><?php echo esc_html( fnc_attr( $a, 'intro' ) ); ?></p>
			<?php endif; ?>
			<div class="faq-list">
				<?php foreach ( $items as $item ) : ?>
					<?php if ( empty( $item['question'] ) ) { continue; } ?>
					<details class="faq-item">
						<summary><?php echo esc_html( $item['question'] ); ?></summary>
						<?php if ( ! empty( $item['answer'] ) ) : ?>
							<div class="faq-answer"><?php echo fnc_render_rich( $item['answer'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé par wp_kses_post. ?></div>
						<?php endif; ?>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_inst_faq( $a ) {
	return fnc_render_faq_section( $a, true );
}

/* ---------------- Palette générique ---------------- */

function fnc_render_block_hero( $a ) {
	$image = fnc_attachment_url( fnc_attr( $a, 'image', 0 ) );

	ob_start();
	?>
	<section class="hero secondary">
		<?php if ( $image ) : ?>
			<img src="<?php echo esc_url( $image ); ?>" alt="" />
		<?php endif; ?>
		<div class="hero-inner">
			<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></p>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'title' ) ) : ?>
				<h1><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h1>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'subtitle' ) ) : ?>
				<p class="lead"><?php echo esc_html( fnc_attr( $a, 'subtitle' ) ); ?></p>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'body' ) ) : ?>
				<p class="lead"><?php echo esc_html( fnc_attr( $a, 'body' ) ); ?></p>
			<?php endif; ?>
			<?php
			$cta = fnc_render_cta_button( fnc_attr( $a, 'ctaLabel' ), fnc_attr( $a, 'ctaHref' ), 'btn btn-red' );
			if ( $cta ) {
				echo '<div class="hero-actions">' . $cta . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans le helper.
			}
			?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_richtext( $a ) {
	$content = fnc_attr( $a, 'content' );
	if ( ! $content && ! fnc_attr( $a, 'title' ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="section">
		<div class="container reading">
			<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></p>
			<?php endif; ?>
			<?php if ( fnc_attr( $a, 'title' ) ) : ?>
				<h2><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
			<?php endif; ?>
			<?php echo fnc_render_rich( $content ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé par wp_kses_post. ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_split( $a ) {
	$image = fnc_attachment_url( fnc_attr( $a, 'image', 0 ) );
	$left  = 'left' === fnc_attr( $a, 'mediaSide', 'right' );

	ob_start();
	?>
	<section class="section">
		<div class="split<?php echo $left ? ' media-left' : ''; ?>">
			<?php if ( $image && $left ) : ?>
				<figure><img src="<?php echo esc_url( $image ); ?>" alt="" /></figure>
			<?php endif; ?>
			<div>
				<?php if ( fnc_attr( $a, 'eyebrow' ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( fnc_attr( $a, 'eyebrow' ) ); ?></span>
				<?php endif; ?>
				<?php if ( fnc_attr( $a, 'title' ) ) : ?>
					<h2 class="lines"><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
				<?php endif; ?>
				<?php echo fnc_render_rich( fnc_attr( $a, 'body' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé par wp_kses_post. ?>
				<?php
				$cta = fnc_render_cta_button( fnc_attr( $a, 'ctaLabel' ), fnc_attr( $a, 'ctaHref' ), 'link-more' );
				echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans le helper.
				?>
			</div>
			<?php if ( $image && ! $left ) : ?>
				<figure><img src="<?php echo esc_url( $image ); ?>" alt="" /></figure>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_stats( $a ) {
	$items = fnc_attr_items( $a );
	if ( empty( $items ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="section linen">
		<div class="container">
			<?php if ( fnc_attr( $a, 'title' ) ) : ?>
				<h2><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
			<?php endif; ?>
			<div class="metric-strip">
				<?php foreach ( $items as $item ) : ?>
					<div class="metric">
						<b><?php echo esc_html( isset( $item['value'] ) ? $item['value'] : '' ); ?></b>
						<span><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_cta( $a ) {
	ob_start();
	?>
	<section class="cta-band">
		<?php if ( fnc_attr( $a, 'title' ) ) : ?>
			<h2><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
		<?php endif; ?>
		<?php if ( fnc_attr( $a, 'text' ) ) : ?>
			<p><?php echo esc_html( fnc_attr( $a, 'text' ) ); ?></p>
		<?php endif; ?>
		<?php echo fnc_render_cta_button( fnc_attr( $a, 'ctaLabel' ), fnc_attr( $a, 'ctaHref' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans le helper. ?>
	</section>
	<?php
	return (string) ob_get_clean();
}

function fnc_render_block_faq( $a ) {
	return fnc_render_faq_section( $a, false );
}

function fnc_render_block_documents( $a ) {
	$items = fnc_attr_items( $a );
	if ( empty( $items ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="section">
		<div class="container">
			<?php if ( fnc_attr( $a, 'title' ) ) : ?>
				<h2><?php echo esc_html( fnc_attr( $a, 'title' ) ); ?></h2>
			<?php endif; ?>
			<div class="grid grid-3">
				<?php
				foreach ( $items as $item ) :
					$file_id  = isset( $item['file'] ) ? (int) $item['file'] : 0;
					$file_url = $file_id > 0 ? wp_get_attachment_url( $file_id ) : '';
					$label    = isset( $item['label'] ) ? $item['label'] : '';
					if ( ! $label ) {
						continue;
					}
					?>
					<article class="card">
						<p class="card-kicker"><?php esc_html_e( 'Document', 'fnc-wordpress-theme' ); ?></p>
						<h3><?php echo esc_html( $label ); ?></h3>
						<?php if ( $file_url ) : ?>
							<p style="margin-top:12px;"><a class="link-more" href="<?php echo esc_url( $file_url ); ?>"><?php esc_html_e( 'Télécharger', 'fnc-wordpress-theme' ); ?> <span class="arrow">→</span></a></p>
						<?php else : ?>
							<p class="help"><?php esc_html_e( 'Fichier à venir', 'fnc-wordpress-theme' ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}
