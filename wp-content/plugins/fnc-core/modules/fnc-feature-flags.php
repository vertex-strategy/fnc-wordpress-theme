<?php
/**
 * Plugin Name: FNC Core — Options d'affichage
 * Description: Surfaces prêtes mais FERMÉES par défaut, activables sans code :
 *              Inscriptions et Actualités. Réglage dans « Réglages → FNC (fonctionnalités) »,
 *              ou via une constante / variable d'environnement.
 * Version: 0.1.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INTÉGRATION : autonome OU à fusionner dans FNC Core. Page « Réglages → FNC
 * (fonctionnalités) ». S'accroche au filtre `fnc_submission_accepts` (réception des
 * formulaires) pour refuser honnêtement une inscription fermée.
 *
 * PRINCIPE : options SERVEUR, fausses par défaut ; jamais de bloc vide.
 * Résolution, par priorité :
 *   1. constante wp-config  (FNC_REGISTRATION_ENABLED / FNC_NEWS_ENABLED)
 *   2. variable d'environnement (REGISTRATION_ENABLED / NEWS_ENABLED)
 *   3. option WP            (case à cocher, activable sans redéploiement)
 * → toutes surchargeables par filtre. 1 et 2 « forcent » (la case devient indicative).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * Résolution des drapeaux
 * ======================================================================== */

if ( ! function_exists( 'fnc_flag_truthy' ) ) {
	function fnc_flag_truthy( $v ) {
		if ( is_bool( $v ) ) {
			return $v;
		}
		return in_array( strtolower( trim( (string) $v ) ), array( '1', 'true', 'on', 'yes' ), true );
	}
}

if ( ! function_exists( 'fnc_feature_is_forced' ) ) {
	/** Un drapeau est-il « forcé » par constante ou variable d'environnement ? */
	function fnc_feature_is_forced( $env, $const ) {
		if ( defined( $const ) ) {
			return true;
		}
		$ev = getenv( $env );
		return false !== $ev && '' !== $ev;
	}
}

if ( ! function_exists( 'fnc_feature_flag' ) ) {
	/** Valeur effective d'un drapeau (constante → env → option). */
	function fnc_feature_flag( $option_key, $env, $const ) {
		if ( defined( $const ) ) {
			return fnc_flag_truthy( constant( $const ) );
		}
		$ev = getenv( $env );
		if ( false !== $ev && '' !== $ev ) {
			return fnc_flag_truthy( $ev );
		}
		$features = get_option( 'fnc_features', array() );
		$val      = is_array( $features ) && isset( $features[ $option_key ] ) ? $features[ $option_key ] : false;
		return fnc_flag_truthy( $val );
	}
}

if ( ! function_exists( 'fnc_registration_enabled' ) ) {
	/** Inscriptions ouvertes ? Faux par défaut. */
	function fnc_registration_enabled() {
		return (bool) apply_filters(
			'fnc_registration_enabled',
			fnc_feature_flag( 'registration', 'REGISTRATION_ENABLED', 'FNC_REGISTRATION_ENABLED' )
		);
	}
}

if ( ! function_exists( 'fnc_news_enabled' ) ) {
	/** Surface publique Actualités active ? (M9 · L5). Faux par défaut. */
	function fnc_news_enabled() {
		return (bool) apply_filters(
			'fnc_news_enabled',
			fnc_feature_flag( 'news', 'NEWS_ENABLED', 'FNC_NEWS_ENABLED' )
		);
	}
}

/* ==========================================================================
 * Câblage — refus honnête d'une inscription fermée (réception des formulaires)
 * ======================================================================== */

if ( ! function_exists( 'fnc_feature_gate_submission' ) ) {
	function fnc_feature_gate_submission( $accepts, $type ) {
		if ( 'inscription' === $type && ! fnc_registration_enabled() ) {
			return false; // → la réception affiche le message « fermé » et ne stocke rien.
		}
		return $accepts;
	}
	add_filter( 'fnc_submission_accepts', 'fnc_feature_gate_submission', 10, 2 );
}

/* ==========================================================================
 * Rendu — CTA d'inscription (centralise le motif répété dans le site réel)
 * ======================================================================== */

if ( ! function_exists( 'fnc_is_en' ) ) {
	function fnc_is_en() {
		return function_exists( 'pll_current_language' )
			? ( 'en' === pll_current_language() )
			: ( 0 === strpos( get_locale(), 'en' ) );
	}
}

if ( ! function_exists( 'fnc_internal_url' ) ) {
	/** Chemin interne (« /inscription ») → permalien (Polylang-aware), sinon home + slug. */
	function fnc_internal_url( $path ) {
		$path = (string) $path;
		if ( preg_match( '#^https?://#i', $path ) ) {
			return $path;
		}
		$slug = trim( $path, '/' );
		if ( '' === $slug ) {
			return home_url( '/' );
		}
		$page = get_page_by_path( $slug );
		if ( $page ) {
			if ( function_exists( 'pll_get_post' ) ) {
				$tr = pll_get_post( $page->ID );
				if ( $tr ) {
					$page = get_post( $tr );
				}
			}
			$perma = $page ? get_permalink( $page ) : '';
			if ( $perma ) {
				return $perma;
			}
		}
		return home_url( '/' . $slug );
	}
}

if ( ! function_exists( 'fnc_registration_cta' ) ) {
	/**
	 * CTA d'inscription conditionnel — miroir de la logique répétée (home, footer,
	 * infos-pratiques, mot-du-président) : ouvert → « S'inscrire » vers /inscription ;
	 * fermé → « Découvrir l'édition » vers /edition-en-cours.
	 *
	 * @param array $args class, href_open, href_closed, label_open, label_closed.
	 * @return string
	 */
	function fnc_registration_cta( $args = array() ) {
		$en      = fnc_is_en();
		$enabled = fnc_registration_enabled();

		$a = wp_parse_args(
			$args,
			array(
				'class'        => 'btn btn-primary',
				'href_open'    => '/inscription',
				'href_closed'  => '/edition-en-cours',
				'label_open'   => $en ? 'Register' : "S'inscrire",
				'label_closed' => $en ? 'Discover the edition' : "Découvrir l'édition",
			)
		);

		$href  = $enabled ? $a['href_open'] : $a['href_closed'];
		$label = $enabled ? $a['label_open'] : $a['label_closed'];

		return sprintf(
			'<a class="%s" href="%s">%s</a>',
			esc_attr( $a['class'] ),
			esc_url( fnc_internal_url( $href ) ),
			esc_html( $label )
		);
	}
}

if ( ! function_exists( 'fnc_registration_cta_shortcode' ) ) {
	/** [fnc_registration_cta class="…"] — pour placement sans PHP. */
	function fnc_registration_cta_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'class' => 'btn btn-primary' ), $atts, 'fnc_registration_cta' );
		return fnc_registration_cta( array( 'class' => $atts['class'] ) );
	}
	add_shortcode( 'fnc_registration_cta', 'fnc_registration_cta_shortcode' );
}

/*
 * ACTUALITÉS : `fnc_news_enabled()` gouverne la surface publique. Le thème doit garder
 * la page /actualites et l'entrée de menu derrière ce test — comme le site réel qui
 * masque /actualites tant que NEWS_ENABLED n'est pas 'true' (jamais de bloc vide).
 *   Exemple : <?php if ( function_exists( 'fnc_news_enabled' ) && ! fnc_news_enabled() ) { get_template_part( '404' ); return; } ?>
 */

/* ==========================================================================
 * Admin — page « Réglages → FNC (fonctionnalités) »
 * ======================================================================== */

if ( ! function_exists( 'fnc_features_register' ) ) {
	function fnc_features_register() {
		register_setting(
			'fnc_features_group',
			'fnc_features',
			array(
				'sanitize_callback' => 'fnc_features_sanitize',
				'default'           => array(),
			)
		);
	}
	add_action( 'admin_init', 'fnc_features_register' );
}

if ( ! function_exists( 'fnc_features_sanitize' ) ) {
	function fnc_features_sanitize( $input ) {
		return array(
			'registration' => ( is_array( $input ) && ! empty( $input['registration'] ) ) ? 1 : 0,
			'news'         => ( is_array( $input ) && ! empty( $input['news'] ) ) ? 1 : 0,
		);
	}
}

if ( ! function_exists( 'fnc_features_menu' ) ) {
	function fnc_features_menu() {
		add_options_page(
			'FNC — Fonctionnalités',
			'FNC (fonctionnalités)',
			'manage_options',
			'fnc-features',
			'fnc_features_page'
		);
	}
	add_action( 'admin_menu', 'fnc_features_menu' );
}

if ( ! function_exists( 'fnc_features_checkbox_row' ) ) {
	function fnc_features_checkbox_row( $key, $label, $env, $const, $effective, $desc ) {
		$forced = fnc_feature_is_forced( $env, $const );
		printf(
			'<tr><th scope="row">%1$s</th><td>' .
			'<label><input type="checkbox" name="fnc_features[%2$s]" value="1"%3$s%4$s /> %5$s</label>' .
			'<p class="description">%6$s%7$s</p></td></tr>',
			esc_html( $label ),
			esc_attr( $key ),
			checked( $effective, true, false ),
			$forced ? ' disabled' : '',
			esc_html__( 'Activer', 'fnc' ),
			esc_html( $desc ),
			$forced
				? ' <strong>' . esc_html( sprintf( 'Forcé par %s (constante/env) — la case est indicative.', $const . ' / ' . $env ) ) . '</strong>'
				: ''
		);
	}
}

if ( ! function_exists( 'fnc_features_page' ) ) {
	function fnc_features_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'FNC — Fonctionnalités', 'fnc' ); ?></h1>
			<p class="description">
				Surfaces construites mais fermées par défaut. Une constante <code>wp-config.php</code>
				ou une variable d'environnement du même nom a priorité (activation « sans rebuild »).
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'fnc_features_group' ); ?>
				<table class="form-table" role="presentation">
					<?php
					fnc_features_checkbox_row(
						'registration',
						'Inscriptions',
						'REGISTRATION_ENABLED',
						'FNC_REGISTRATION_ENABLED',
						fnc_registration_enabled(),
						'Ouvre /inscription et bascule le CTA sur « S’inscrire ». Fermé : demandes refusées honnêtement.'
					);
					fnc_features_checkbox_row(
						'news',
						'Actualités',
						'NEWS_ENABLED',
						'FNC_NEWS_ENABLED',
						fnc_news_enabled(),
						'Affiche la surface publique /actualites. Fermé : la page reste masquée (jamais de bloc vide).'
					);
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
