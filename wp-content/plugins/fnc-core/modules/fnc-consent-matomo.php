<?php
/**
 * Plugin Name: FNC Core — Consentement & mesure d'audience
 * Description: Mesure d'audience ANONYME et SANS COOKIE pour tous (exemptée de
 *              consentement, position CNIL) ; le cookie de mesure n'est déposé qu'après
 *              un « oui » explicite. Bandeau où refuser est aussi simple qu'autoriser,
 *              avec réouverture depuis le pied de page.
 * Version: 0.1.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INTÉGRATION : autonome OU à fusionner dans FNC Core. S'accroche à wp_head (tracker)
 * et wp_footer (bandeau). Aucune dépendance aux autres modules.
 *
 * DEUX NIVEAUX À NE PAS CONFONDRE :
 *   1. La MESURE est anonyme et sans cookie par défaut (`requireCookieConsent` côté
 *      tracker). Exemptée de consentement → tourne pour tout le monde, même après refus.
 *   2. Le COOKIE de mesure — seul à reconnaître une visite récurrente — n'est déposé
 *      qu'après un « oui » explicite. C'est le SEUL objet du bandeau.
 *
 * Le choix est conservé en `localStorage` (PAS en cookie) : refuser ne doit rien écrire
 * de traçant sur le poste du visiteur. Les deux actions ont strictement le même poids —
 * exigence CNIL / RGPD art. 7-3, pas un choix esthétique négociable.
 *
 * Le CSS du bandeau (.consent, .pcb-band, .consent-inner/-text/-actions/-btn/-link,
 * .consent-reopen) est fourni par le kit de rattrapage (wordpress-catchup-complet.css).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * Configuration (surchargée par constante OU filtre)
 * ======================================================================== */

if ( ! function_exists( 'fnc_matomo_url' ) ) {
	/** URL du tracker Matomo (avec slash final). Défaut = site réel. */
	function fnc_matomo_url() {
		// Vide par défaut : la mesure reste inactive tant que l'URL du traqueur n'est pas
		// configurée (constante FNC_MATOMO_URL ou filtre 'fnc_matomo_url').
		$url = defined( 'FNC_MATOMO_URL' ) ? FNC_MATOMO_URL : '';
		$url = trim( (string) apply_filters( 'fnc_matomo_url', $url ) );
		return $url ? trailingslashit( $url ) : '';
	}
}

if ( ! function_exists( 'fnc_matomo_site_id' ) ) {
	/** Identifiant de site Matomo (à configurer ; vide = mesure inactive). */
	function fnc_matomo_site_id() {
		$sid = defined( 'FNC_MATOMO_SITE_ID' ) ? FNC_MATOMO_SITE_ID : '';
		return trim( (string) apply_filters( 'fnc_matomo_site_id', $sid ) );
	}
}

if ( ! function_exists( 'fnc_matomo_is_enabled' ) ) {
	/**
	 * La mesure est-elle active ? Requiert URL + site id. Le filtre permet au thème
	 * d'exclure, p. ex., les éditeurs connectés (parité avec le site réel : aucune
	 * exclusion par défaut, tout le monde est mesuré sans cookie).
	 */
	function fnc_matomo_is_enabled() {
		$on = ( '' !== fnc_matomo_url() ) && ( '' !== fnc_matomo_site_id() );
		return (bool) apply_filters( 'fnc_matomo_should_track', $on );
	}
}

if ( ! function_exists( 'fnc_privacy_url' ) ) {
	/** URL de la politique de confidentialité (Polylang-aware), sinon l'accueil. */
	function fnc_privacy_url() {
		$url  = home_url( '/' );
		$page = get_page_by_path( 'politique-confidentialite' );
		if ( $page ) {
			if ( function_exists( 'pll_get_post' ) ) {
				$tr = pll_get_post( $page->ID );
				if ( $tr ) {
					$page = get_post( $tr );
				}
			}
			$perma = $page ? get_permalink( $page ) : '';
			if ( $perma ) {
				$url = $perma;
			}
		}
		return apply_filters( 'fnc_privacy_url', $url );
	}
}

if ( ! function_exists( 'fnc_consent_strings' ) ) {
	/** Copie du bandeau, FR par défaut / EN selon la langue. Miroir de messages.consent. */
	function fnc_consent_strings() {
		$en = function_exists( 'pll_current_language' )
			? ( 'en' === pll_current_language() )
			: ( 0 === strpos( get_locale(), 'en' ) );

		$fr = array(
			'label'   => 'Confidentialité',
			'eyebrow' => 'Confidentialité',
			'body'    => "La mesure d'audience de ce site est anonyme et fonctionne sans cookie. Vous pouvez autoriser un cookie de mesure pour reconnaître les visites récurrentes — choix modifiable à tout moment.",
			'policy'  => 'Lire la politique de confidentialité',
			'decline' => 'Refuser',
			'accept'  => 'Autoriser',
			'manage'  => 'Cookie de mesure',
		);
		$en_strings = array(
			'label'   => 'Privacy',
			'eyebrow' => 'Privacy',
			'body'    => "This site's audience measurement is anonymous and works without cookies. You can allow a measurement cookie to recognise return visits — you can change this at any time.",
			'policy'  => 'Read the privacy policy',
			'decline' => 'Decline',
			'accept'  => 'Allow',
			'manage'  => 'Measurement cookie',
		);

		return apply_filters( 'fnc_consent_strings', $en ? $en_strings : $fr, $en );
	}
}

/* ==========================================================================
 * Tracker Matomo — wp_head
 * ======================================================================== */

if ( ! function_exists( 'fnc_matomo_head' ) ) {
	function fnc_matomo_head() {
		if ( ! fnc_matomo_is_enabled() ) {
			return;
		}
		$url = fnc_matomo_url();
		$sid = fnc_matomo_site_id();
		$key = 'fnc.consent.analytics'; // clé du choix de consentement (stockage local)
		?>
<!-- Mesure d'audience (FNC Core) : anonyme sans cookie ; cookie après « oui » -->
<script>
var _paq = window._paq = window._paq || [];
_paq.push(['requireCookieConsent']);
try { if (window.localStorage.getItem(<?php echo wp_json_encode( $key ); ?>) === 'granted') { _paq.push(['setCookieConsentGiven']); } } catch (e) {}
_paq.push(['setDoNotTrack', true]);
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
  var u = <?php echo wp_json_encode( $url ); ?>;
  _paq.push(['setTrackerUrl', u + 'matomo.php']);
  _paq.push(['setSiteId', <?php echo wp_json_encode( $sid ); ?>]);
  var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
  g.async = true; g.src = u + 'matomo.js'; s.parentNode.insertBefore(g, s);
})();
</script>
<!-- End Matomo -->
		<?php
	}
	add_action( 'wp_head', 'fnc_matomo_head', 20 );
}

/* ==========================================================================
 * Bandeau de consentement — wp_footer
 * ======================================================================== */

if ( ! function_exists( 'fnc_consent_inline_js' ) ) {
	/**
	 * Comportement du bandeau de consentement (choix stocké en local, sans cookie).
	 * read/write en localStorage, apply → _paq (setCookieConsentGiven /
	 * forgetCookieConsentGiven), réouverture via l'évènement `fnc:consent-reopen`.
	 * Le bandeau est masqué par un style inline (robuste quel que soit le display du
	 * kit) et révélé par JS si aucun choix n'est encore stocké.
	 */
	function fnc_consent_inline_js() {
		$key = wp_json_encode( 'fnc.consent.analytics' ); // clé du choix
		$evt = wp_json_encode( 'fnc:consent-reopen' );     // évènement de réouverture
		return <<<JS
(function(){
  var KEY=$key, EVT=$evt;
  var el=document.getElementById('fnc-consent');
  function read(){try{var v=window.localStorage.getItem(KEY);return (v==='granted'||v==='denied')?v:null;}catch(e){return null;}}
  function write(c){try{window.localStorage.setItem(KEY,c);}catch(e){}}
  function apply(c){var q=window._paq;if(!q)return;q.push([c==='granted'?'setCookieConsentGiven':'forgetCookieConsentGiven']);}
  function show(){if(el)el.style.display='';}
  function hide(){if(el)el.style.display='none';}
  function decide(c){write(c);apply(c);hide();}
  if(el){
    var a=el.querySelector('.consent-accept'), d=el.querySelector('.consent-decline');
    if(a)a.addEventListener('click',function(){decide('granted');});
    if(d)d.addEventListener('click',function(){decide('denied');});
    if(read()===null)show();
  }
  window.addEventListener(EVT,show);
  document.addEventListener('click',function(e){
    var t=(e.target&&e.target.closest)?e.target.closest('.consent-reopen'):null;
    if(t){e.preventDefault();window.dispatchEvent(new CustomEvent(EVT));}
  });
})();
JS;
	}
}

if ( ! function_exists( 'fnc_consent_ui_enabled' ) ) {
	/**
	 * Faut-il AFFICHER le bandeau + le bouton de consentement ? DÉCOUPLÉ de la mesure :
	 * l'UI de consentement s'affiche par défaut (démo/vitrine — le visiteur voit
	 * l'expérience CNIL), même sans Matomo configuré. La MESURE réelle, elle, reste
	 * conditionnée à `fnc_matomo_is_enabled()` (voir fnc_matomo_head). Filtrable :
	 *   add_filter( 'fnc_consent_ui_enabled', '__return_false' ); // masquer partout
	 */
	function fnc_consent_ui_enabled() {
		return (bool) apply_filters( 'fnc_consent_ui_enabled', true );
	}
}

if ( ! function_exists( 'fnc_consent_banner' ) ) {
	function fnc_consent_banner() {
		if ( ! fnc_consent_ui_enabled() ) {
			return; // UI de consentement désactivée (filtre).
		}
		$s      = fnc_consent_strings();
		$policy = fnc_privacy_url();
		?>
<aside id="fnc-consent" class="consent" role="region" aria-label="<?php echo esc_attr( $s['label'] ); ?>" style="display:none">
	<svg class="pcb-band" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
		<path class="r" d="M0 40 H420 l20 -20 H820 l20 20 H1200" />
		<path class="y" d="M0 20 H300 l24 20 H900 l18 -14 H1200" />
	</svg>
	<div class="consent-inner">
		<div class="consent-text">
			<span class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
			<p><?php echo esc_html( $s['body'] ); ?></p>
			<a class="consent-link" href="<?php echo esc_url( $policy ); ?>"><?php echo esc_html( $s['policy'] ); ?></a>
		</div>
		<div class="consent-actions">
			<button type="button" class="btn consent-btn consent-decline"><?php echo esc_html( $s['decline'] ); ?></button>
			<button type="button" class="btn consent-btn consent-accept"><?php echo esc_html( $s['accept'] ); ?></button>
		</div>
	</div>
</aside>
<script><?php echo fnc_consent_inline_js(); // JS statique, sans données utilisateur. ?></script>
		<?php
	}
	add_action( 'wp_footer', 'fnc_consent_banner', 20 );
}

/* ==========================================================================
 * Réouverture — « le retrait doit être aussi simple que l'accord » (RGPD 7-3)
 * ======================================================================== */

if ( ! function_exists( 'fnc_consent_reopen_button' ) ) {
	/**
	 * Bouton de réouverture du bandeau, à placer dans le pied de page :
	 *   <?php if ( function_exists( 'fnc_consent_reopen_button' ) ) echo fnc_consent_reopen_button(); ?>
	 * Renvoie une chaîne vide si la mesure n'est pas configurée.
	 *
	 * @param string $label Libellé personnalisé (sinon « Cookie de mesure »).
	 * @return string
	 */
	function fnc_consent_reopen_button( $label = '' ) {
		if ( ! fnc_consent_ui_enabled() ) {
			return '';
		}
		$s     = fnc_consent_strings();
		$label = ( '' !== $label ) ? $label : $s['manage'];
		return sprintf( '<button type="button" class="consent-reopen">%s</button>', esc_html( $label ) );
	}
}

if ( ! function_exists( 'fnc_consent_reopen_shortcode' ) ) {
	/** Variante shortcode : [fnc_consent_reopen label="…"] — pour widget/menu sans PHP. */
	function fnc_consent_reopen_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'label' => '' ), $atts, 'fnc_consent_reopen' );
		return fnc_consent_reopen_button( $atts['label'] );
	}
	add_shortcode( 'fnc_consent_reopen', 'fnc_consent_reopen_shortcode' );
}
