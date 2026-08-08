<?php
/**
 * Plugin Name: FNC Core — Données du site
 * Description: Fonctions métier portables (indépendantes du thème) : édition active +
 *              statut, compte à rebours, participants dérivés des sessions publiées,
 *              voix mises en avant, programme par jour, filtres d'intervenants et garde
 *              « droit à l'image » (portrait affiché seulement si l'autorisation est acquise).
 * Version: 0.1.0
 * Author: Grinso & Associés
 * Author URI: https://www.grinso.io
 * Copyright: © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 *            Développé par Vanel NGOYO ADOUMA, Lead développeur.
 *
 * INTÉGRATION : autonome OU à fusionner dans FNC Core. Toutes les fonctions sont
 * gardées par function_exists() → aucun conflit avec le thème existant.
 *
 * CLÉS MÉTA (relevées du plugin fnc-content-model) :
 *   Édition  : _fnc_edition_status ('current'|'upcoming'|'past'), _fnc_edition_active,
 *              _fnc_edition_start_date, _fnc_edition_end_date, _fnc_edition_year, …
 *   Session  : _fnc_session_edition (rel), _fnc_session_moderator (int rel),
 *              _fnc_session_speakers (array rel), _fnc_session_jour, _fnc_session_start
 *              (« 9h30 »), _fnc_session_end, _fnc_session_time (libellé affiché),
 *              _fnc_session_room, _fnc_session_type, …
 *   Speaker  : _fnc_speaker_country, _fnc_speaker_org, _fnc_speaker_title,
 *              _fnc_speaker_protocol_order ; PROFIL = taxonomie « fnc_profil »
 *              (official/expert/host, rattachée à fnc_intervenant), pas une méta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * ÉDITION — résolution de l'édition active + statut + compte à rebours
 * (édition active + compte à rebours)
 * ======================================================================== */

if ( ! function_exists( 'fnc_resolve_current_edition' ) ) {
	/**
	 * Édition EN COURS (status='current') UNIQUEMENT — jamais de repli « à venir ».
	 * Départage DÉTERMINISTE par année DÉCROISSANTE si plusieurs éditions sont
	 * marquées « en cours » (la plus récente gagne). Vues current-only : accueil,
	 * hub, agenda, participants, voix, infos-pratiques, JSON-LD. Null si aucune.
	 *
	 * @return WP_Post|null
	 */
	function fnc_resolve_current_edition() {
		static $cache = false;
		if ( false !== $cache ) {
			return $cache;
		}
		$current = get_posts( array(
			'post_type'      => 'fnc_edition',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_fnc_edition_year',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array(
				array( 'key' => '_fnc_edition_status', 'value' => 'current' ),
			),
			'no_found_rows'  => true,
		) );
		return $cache = ( ! empty( $current ) ? $current[0] : null );
	}
}

if ( ! function_exists( 'fnc_resolve_active_edition' ) ) {
	/**
	 * Édition active AVEC REPLI : « en cours », à défaut la prochaine « à venir »
	 * (année la plus proche). RÉSERVÉE au rattachement d'inscription (parité
	 * resolveActiveEdition du Next) — PAS aux vitrines current-only.
	 *
	 * @return WP_Post|null
	 */
	function fnc_resolve_active_edition() {
		static $cache = false;
		if ( false !== $cache ) {
			return $cache;
		}
		$current = fnc_resolve_current_edition();
		if ( $current ) {
			return $cache = $current;
		}
		$upcoming = get_posts( array(
			'post_type'      => 'fnc_edition',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_fnc_edition_year',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_fnc_edition_status', 'value' => 'upcoming' ),
			),
			'no_found_rows'  => true,
		) );
		return $cache = ( ! empty( $upcoming ) ? $upcoming[0] : null );
	}
}

if ( ! function_exists( 'fnc_current_edition_id' ) ) {
	/** ID de l'édition EN COURS (current-strict ; 0 si aucune). Vues current-only. */
	function fnc_current_edition_id() {
		$ed = fnc_resolve_current_edition();
		return $ed ? (int) $ed->ID : 0;
	}
}

if ( ! function_exists( 'fnc_registration_edition_id' ) ) {
	/** ID pour le RATTACHEMENT d'inscription : en cours → à venir (repli). */
	function fnc_registration_edition_id() {
		$ed = fnc_resolve_active_edition();
		return $ed ? (int) $ed->ID : 0;
	}
}

if ( ! function_exists( 'fnc_edition_status' ) ) {
	/** Statut d'une édition : 'current' | 'upcoming' | 'past'. */
	function fnc_edition_status( $edition_id ) {
		$s = (string) get_post_meta( (int) $edition_id, '_fnc_edition_status', true );
		return in_array( $s, array( 'current', 'upcoming', 'past' ), true ) ? $s : 'upcoming';
	}
}

if ( ! function_exists( 'fnc_days_until_edition' ) ) {
	/**
	 * Compte à rebours : nombre de jours avant le début de l'édition (jamais figé,
	 * calculé au rendu). 0 si la date est passée ou absente.
	 */
	function fnc_days_until_edition( $edition_id = 0 ) {
		$edition_id = $edition_id ? (int) $edition_id : fnc_current_edition_id();
		if ( ! $edition_id ) {
			return 0;
		}
		$start = (string) get_post_meta( $edition_id, '_fnc_edition_start_date', true );
		$ts    = $start ? strtotime( $start ) : false;
		if ( ! $ts ) {
			return 0;
		}
		return max( 0, (int) ceil( ( $ts - current_time( 'timestamp' ) ) / DAY_IN_SECONDS ) );
	}
}

/* ==========================================================================
 * PARTICIPANTS — dérivés des sessions PUBLIÉES de l'édition
 * (participants = modérateur + intervenants d'≥1 session publiée,
 *  ordonnés par protocole)
 * ======================================================================== */

if ( ! function_exists( 'fnc_session_speaker_ids' ) ) {
	/** IDs d'intervenants d'une session (modérateur + speakers), normalisés. */
	function fnc_session_speaker_ids( $session_id ) {
		$ids = array();
		$mod = (int) get_post_meta( $session_id, '_fnc_session_moderator', true );
		if ( $mod ) {
			$ids[] = $mod;
		}
		$spk = get_post_meta( $session_id, '_fnc_session_speakers', true );
		if ( is_array( $spk ) ) {
			foreach ( $spk as $s ) {
				$ids[] = (int) $s;
			}
		} elseif ( $spk ) {
			$ids[] = (int) $spk;
		}
		return array_values( array_filter( array_unique( $ids ) ) );
	}
}

if ( ! function_exists( 'fnc_edition_participants' ) ) {
	/**
	 * Personnes présentes dans ≥1 session PUBLIÉE de l'édition, dédupliquées et
	 * ordonnées par _fnc_speaker_protocol_order (puis titre). Renvoie des IDs.
	 *
	 * @return int[]
	 */
	function fnc_edition_participants( $edition_id = 0 ) {
		$edition_id = $edition_id ? (int) $edition_id : fnc_current_edition_id();
		if ( ! $edition_id ) {
			return array();
		}

		$sessions = get_posts( array(
			'post_type'      => 'fnc_session',
			'post_status'    => 'publish',
			'posts_per_page' => 300,
			'fields'         => 'ids',
			'meta_key'       => '_fnc_session_edition',
			'meta_value'     => $edition_id,
			'no_found_rows'  => true,
		) );

		$seen = array();
		foreach ( $sessions as $sid ) {
			foreach ( fnc_session_speaker_ids( $sid ) as $pid ) {
				$seen[ $pid ] = true;
			}
		}
		$ids = array_keys( $seen );
		if ( empty( $ids ) ) {
			return array();
		}

		// Ne garder que les intervenants publiés + trier par ordre protocolaire.
		$speakers = get_posts( array(
			'post_type'      => 'fnc_intervenant',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'no_found_rows'  => true,
		) );

		// Ordre GLOBAL (A1) : protocolOrder ASC (absent -> dernier), puis
		// sort_index ASC (ordre editorial, editable), puis titre (tri stable).
		usort( $speakers, function ( $a, $b ) {
			$oa = (int) get_post_meta( $a->ID, '_fnc_speaker_protocol_order', true );
			$ob = (int) get_post_meta( $b->ID, '_fnc_speaker_protocol_order', true );
			$oa = $oa ? $oa : PHP_INT_MAX;
			$ob = $ob ? $ob : PHP_INT_MAX;
			if ( $oa !== $ob ) {
				return $oa - $ob;
			}
			$sa = (int) get_post_meta( $a->ID, '_fnc_speaker_sort_index', true );
			$sb = (int) get_post_meta( $b->ID, '_fnc_speaker_sort_index', true );
			if ( $sa !== $sb ) {
				return $sa - $sb;
			}
			return strcmp( get_the_title( $a ), get_the_title( $b ) );
		} );

		return wp_list_pluck( $speakers, 'ID' );
	}
}

if ( ! function_exists( 'fnc_home_voices' ) ) {
	/**
	 * Voix de la home : participants de l'édition active, avec PROMOTION
	 * éditoriale, plafonnés à $count (défaut 10).
	 *
	 * Ordre (A2) : les voix « mises en avant » (_fnc_speaker_home_featured)
	 * d'abord, triées par _fnc_speaker_home_featured_order (absent -> rang dans
	 * l'ordre global), puis le reste dans l'ordre global (A1), le tout plafonné.
	 * Une voix promue hors édition en cours n'apparaît pas (elle n'est pas
	 * participante), ce qui préserve la cohérence « les voix de cette édition ».
	 *
	 * @return int[]
	 */
	function fnc_home_voices( $count = 10, $edition_id = 0 ) {
		$count = max( 0, (int) $count );
		$ids   = fnc_edition_participants( $edition_id ); // ordre global A1
		if ( empty( $ids ) || 0 === $count ) {
			return array_slice( $ids, 0, $count );
		}

		$featured = array();
		$rest     = array();
		foreach ( $ids as $rank => $pid ) {
			if ( get_post_meta( $pid, '_fnc_speaker_home_featured', true ) ) {
				$order      = get_post_meta( $pid, '_fnc_speaker_home_featured_order', true );
				$featured[] = array(
					'id'    => $pid,
					'order' => ( '' === $order ) ? $rank : (int) $order,
					'rank'  => $rank,
				);
			} else {
				$rest[] = $pid;
			}
		}

		usort( $featured, function ( $a, $b ) {
			if ( $a['order'] !== $b['order'] ) {
				return $a['order'] - $b['order'];
			}
			return $a['rank'] - $b['rank'];
		} );

		$ordered = array_merge( wp_list_pluck( $featured, 'id' ), $rest );
		return array_slice( $ordered, 0, $count );
	}
}

/* ==========================================================================
 * PAYS REPRÉSENTÉS — dérivés du champ pays des participants
 * (pays des participants. L'ORDRE et les DRAPEAUX peuvent venir des réglages :
 *  fnc_order_countries / fnc_country_flag — à consolider ICI à terme. On ne les
 *  redéfinit pas pour éviter tout conflit ; on les réutilise s'ils existent.)
 * ======================================================================== */

if ( ! function_exists( 'fnc_edition_countries' ) ) {
	/**
	 * Liste dédupliquée des pays des participants (champ multi-valeurs « A / B »).
	 * Ordre : via fnc_order_countries() si le thème/plugin le fournit, sinon Congo en tête.
	 *
	 * @return string[]
	 */
	function fnc_edition_countries( $edition_id = 0 ) {
		$list = array();
		$seen = array();
		foreach ( fnc_edition_participants( $edition_id ) as $sid ) {
			$raw = (string) get_post_meta( $sid, '_fnc_speaker_country', true );
			foreach ( array_map( 'trim', explode( '/', $raw ) ) as $c ) {
				if ( '' === $c ) {
					continue;
				}
				// Dédup sur la clé NORMALISÉE (« Congo » ≡ « congo »), en gardant le
				// PREMIER libellé vu pour l'affichage.
				$key = function_exists( 'fnc_country_key' ) ? fnc_country_key( $c ) : remove_accents( strtolower( $c ) );
				if ( ! isset( $seen[ $key ] ) ) {
					$seen[ $key ] = true;
					$list[]       = $c;
				}
			}
		}

		if ( function_exists( 'fnc_order_countries' ) ) {
			return fnc_order_countries( $list );
		}
		usort( $list, function ( $x, $y ) {
			if ( 'Congo' === $x ) { return -1; }
			if ( 'Congo' === $y ) { return 1; }
			return 0;
		} );
		return $list;
	}
}

/* ==========================================================================
 * GARDE « DROIT À L'IMAGE »
 * Un portrait n'est affiché publiquement QUE si le droit à l'image est « obtenu »
 * et non expiré. Aucune autorisation n'est présumée.
 *
 * ⚠️ IMPORTANT : les métas ci-dessous N'EXISTENT PAS encore dans le content-model WP.
 *    À AJOUTER à la collection Intervenant (post-types.php) :
 *      _fnc_speaker_image_right   : select 'non_verifie'|'obtenu'|'refuse'|'expire'
 *      _fnc_speaker_image_expires : date (optionnelle)
 *    Tant qu'elles ne sont pas saisies, la garde renvoie FALSE (conforme à la règle :
 *    ne pas présumer le droit). Un filtre permet d'assouplir PENDANT la migration.
 * ======================================================================== */

if ( ! function_exists( 'fnc_speaker_image_allowed' ) ) {
	function fnc_speaker_image_allowed( $speaker_id ) {
		$status  = (string) get_post_meta( (int) $speaker_id, '_fnc_speaker_image_right', true );
		$expires = (string) get_post_meta( (int) $speaker_id, '_fnc_speaker_image_expires', true );
		$not_expired = ( '' === $expires ) || ( strtotime( $expires ) > current_time( 'timestamp' ) );
		$allowed = ( 'obtenu' === $status ) && $not_expired;

		/**
		 * Filtre de TRANSITION. Garde = false par défaut (aucune présomption).
		 * Pour afficher les portraits pendant la saisie des droits :
		 *   add_filter('fnc_speaker_image_allowed', '__return_true');
		 * — à RETIRER une fois les droits renseignés.
		 */
		return (bool) apply_filters( 'fnc_speaker_image_allowed', $allowed, (int) $speaker_id, $status );
	}
}

if ( ! function_exists( 'fnc_speaker_portrait' ) ) {
	/**
	 * Balise <img> du portrait — UNIQUEMENT si le droit à l'image est acquis.
	 * Sinon chaîne vide : le gabarit rend alors le monogramme (initiales).
	 */
	function fnc_speaker_portrait( $speaker_id, $size = 'medium', $attr = array() ) {
		if ( ! fnc_speaker_image_allowed( $speaker_id ) ) {
			return '';
		}
		$thumb = get_post_thumbnail_id( (int) $speaker_id );
		if ( ! $thumb ) {
			return '';
		}
		$attr = wp_parse_args( $attr, array( 'class' => 'spk-portrait', 'loading' => 'lazy' ) );
		return wp_get_attachment_image( $thumb, $size, false, $attr );
	}
}

/* ==========================================================================
 * M1 — Protection d'accès aux MÉDIAS non diffusables (droit à l'image / brouillons)
 *
 * La garde d'AFFICHAGE (fnc_speaker_portrait → monogramme) n'empêche pas d'ATTEINDRE le
 * FICHIER : une attachment reste servie depuis /wp-content/uploads et listée par l'API
 * REST média. On restreint donc, pour les visiteurs NON habilités :
 *   (a) le portrait d'un intervenant dont le droit à l'image n'est pas obtenu / a expiré ;
 *   (b) tout média rattaché à un contenu NON publié (brouillon / en attente / privé).
 *
 * Couverture PHP : masquage en REST (source_url / guid / media_details retirés) + 404 sur
 * la page d'attachment. ⚠️ L'accès DIRECT au fichier (URL uploads devinée) relève du
 * serveur web — voir INSTALL.md (règle .htaccess / nginx) : PHP ne peut pas l'intercepter.
 * ======================================================================== */
if ( ! function_exists( 'fnc_attachment_is_restricted' ) ) {
	function fnc_attachment_is_restricted( $att_id ) {
		$att_id = (int) $att_id;
		$att    = get_post( $att_id );
		if ( ! $att || 'attachment' !== $att->post_type ) {
			return false;
		}
		// (a) Portrait utilisé comme image à la une d'un intervenant sans droit acquis.
		//     Le lien se fait par la méta `_thumbnail_id`, PAS par post_parent (un portrait
		//     déposé dans la médiathèque a souvent post_parent=0) → recherche inverse.
		if ( function_exists( 'fnc_speaker_image_allowed' ) ) {
			$owners = get_posts(
				array(
					'post_type'   => 'fnc_intervenant',
					'post_status' => 'any',
					'numberposts' => -1,
					'fields'      => 'ids',
					'meta_key'    => '_thumbnail_id',   // phpcs:ignore WordPress.DB.SlowDBQuery -- lookup ciblé hors chemin chaud (REST média / page attachment).
					'meta_value'  => (string) $att_id,  // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
			// Conservateur : si UN SEUL intervenant utilisant ce fichier n'a pas le droit
			// à l'image, le fichier est restreint (un portrait ne devrait pas être partagé ;
			// en cas de partage, on protège par défaut).
			foreach ( $owners as $owner_id ) {
				if ( ! fnc_speaker_image_allowed( (int) $owner_id ) ) {
					return true;
				}
			}
		}

		// (b) Média rattaché (post_parent) à un contenu NON publié (brouillon/en attente/privé).
		$parent_id = (int) $att->post_parent;
		if ( $parent_id ) {
			$parent = get_post( $parent_id );
			if ( $parent && 'publish' !== $parent->post_status ) {
				return true;
			}
		}
		return false;
	}
}

// Masque l'URL de fichier des médias restreints dans l'API REST (visiteurs non habilités).
add_filter(
	'rest_prepare_attachment',
	function ( $response, $post ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			return $response; // Éditeur / Admin : accès légitime.
		}
		if ( ! fnc_attachment_is_restricted( $post->ID ) ) {
			return $response;
		}
		$data = $response->get_data();
		foreach ( array( 'source_url', 'guid', 'media_details', 'description', 'caption' ) as $fnc_k ) {
			unset( $data[ $fnc_k ] );
		}
		$response->set_data( $data );
		return $response;
	},
	10,
	2
);

// 404 sur la page d'attachment d'un média restreint (visiteurs non habilités).
add_action(
	'template_redirect',
	function () {
		if ( ! is_attachment() ) {
			return;
		}
		$fnc_att_id = get_queried_object_id();
		if ( $fnc_att_id && fnc_attachment_is_restricted( $fnc_att_id ) && ! current_user_can( 'edit_post', $fnc_att_id ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}
);

/* ==========================================================================
 * AGENDA — sessions groupées par jour (jour puis horaire)
 * (sessions groupées par jour, triées par horaire.)
 * Dérivation PURE : renvoie une structure prête, ZÉRO rendu (la scénographie —
 * onglets, cartes — reste au thème). Rend l'agenda portable d'un thème à l'autre.
 * ======================================================================== */

if ( ! function_exists( 'fnc_session_start_minutes' ) ) {
	/** « 9h30 » → 570 (minutes depuis minuit) ; 0 si non reconnu. Port de startMinutes. */
	function fnc_session_start_minutes( $value ) {
		if ( preg_match( '/(\d{1,2})h(\d{2})?/', (string) $value, $m ) ) {
			return (int) $m[1] * 60 + (int) ( isset( $m[2] ) ? $m[2] : 0 );
		}
		return 0;
	}
}

if ( ! function_exists( 'fnc_edition_sessions_by_day' ) ) {
	/**
	 * Sessions PUBLIÉES de l'édition, groupées par jour et triées (jour puis horaire).
	 *
	 * @return array[] [ [ 'day' => int, 'sessions' => array[] ], … ]
	 *   Chaque session : id, title, time (libellé), start, end, room, type,
	 *   moderator (int|0), speakers (int[] hors modérateur). Un jour 0 (non planifié)
	 *   est renvoyé en dernier plutôt que perdu.
	 */
	function fnc_edition_sessions_by_day( $edition_id = 0 ) {
		$edition_id = $edition_id ? (int) $edition_id : fnc_current_edition_id();
		if ( ! $edition_id ) {
			return array();
		}

		$sessions = get_posts( array(
			'post_type'      => 'fnc_session',
			'post_status'    => 'publish',
			'posts_per_page' => 300,
			'meta_key'       => '_fnc_session_edition',
			'meta_value'     => $edition_id,
			'no_found_rows'  => true,
		) );

		$by_day = array();
		foreach ( $sessions as $s ) {
			$sid   = (int) $s->ID;
			$day   = (int) get_post_meta( $sid, '_fnc_session_jour', true );
			$start = (string) get_post_meta( $sid, '_fnc_session_start', true );

			$mod      = (int) get_post_meta( $sid, '_fnc_session_moderator', true );
			$spk      = get_post_meta( $sid, '_fnc_session_speakers', true );
			$speakers = array();
			if ( is_array( $spk ) ) {
				foreach ( $spk as $x ) {
					$speakers[] = (int) $x;
				}
			} elseif ( $spk ) {
				$speakers[] = (int) $spk;
			}
			$speakers = array_values( array_filter( array_unique( $speakers ) ) );

			$by_day[ $day ][] = array(
				'id'        => $sid,
				'title'     => get_the_title( $s ),
				'time'      => (string) get_post_meta( $sid, '_fnc_session_time', true ),
				'start'     => $start,
				'end'       => (string) get_post_meta( $sid, '_fnc_session_end', true ),
				'room'      => (string) get_post_meta( $sid, '_fnc_session_room', true ),
				'type'      => (string) get_post_meta( $sid, '_fnc_session_type', true ),
				'moderator' => $mod,
				'speakers'  => $speakers,
				'_sort'     => fnc_session_start_minutes( $start ),
			);
		}

		// Jours croissants ; un jour 0 (non planifié) est repoussé en fin.
		$days = array_keys( $by_day );
		usort( $days, function ( $x, $y ) {
			$rx = $x <= 0 ? PHP_INT_MAX : $x;
			$ry = $y <= 0 ? PHP_INT_MAX : $y;
			return $rx - $ry;
		} );

		$out = array();
		foreach ( $days as $day ) {
			$list = $by_day[ $day ];
			usort( $list, function ( $a, $b ) {
				return $a['_sort'] - $b['_sort'];
			} );
			foreach ( $list as &$row ) {
				unset( $row['_sort'] );
			}
			unset( $row );
			$out[] = array( 'day' => (int) $day, 'sessions' => $list );
		}
		return $out;
	}
}

/* ==========================================================================
 * INTERVENANTS — facettes de filtre (profil + pays) parmi les participants
 * (cf. SpeakersExplorer : axes profil = taxo `fnc_profil`, pays = _fnc_speaker_country.
 *  La LISTE des valeurs filtrables est de la donnée → plugin ; le WIDGET de filtre —
 *  chips, recherche, pagination — reste du JS de thème.)
 * ======================================================================== */

if ( ! function_exists( 'fnc_speaker_facets' ) ) {
	/**
	 * Valeurs filtrables présentes parmi les participants de l'édition, avec comptes.
	 *
	 * @return array{
	 *   total: int,
	 *   profils: array[],   // [ ['slug','name','count'], … ] (termes fnc_profil présents)
	 *   countries: array[]  // [ ['name','count'], … ] (ordre = frise fnc_edition_countries)
	 * }
	 */
	function fnc_speaker_facets( $edition_id = 0 ) {
		$ids = fnc_edition_participants( $edition_id );

		$profils     = array(); // slug => ['slug','name','count']
		$country_cnt = array(); // clé pays NORMALISÉE => count
		foreach ( $ids as $sid ) {
			$terms = get_the_terms( $sid, 'fnc_profil' );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $t ) {
					if ( ! isset( $profils[ $t->slug ] ) ) {
						$profils[ $t->slug ] = array( 'slug' => $t->slug, 'name' => $t->name, 'count' => 0 );
					}
					$profils[ $t->slug ]['count']++;
				}
			}
			$raw = (string) get_post_meta( $sid, '_fnc_speaker_country', true );
			foreach ( array_map( 'trim', explode( '/', $raw ) ) as $c ) {
				if ( '' === $c ) {
					continue;
				}
				// Compte sur la clé NORMALISÉE (« Congo » ≡ « congo ») pour que le
				// compteur corresponde à la frise dédupliquée.
				$ckey                = function_exists( 'fnc_country_key' ) ? fnc_country_key( $c ) : remove_accents( strtolower( $c ) );
				$country_cnt[ $ckey ] = isset( $country_cnt[ $ckey ] ) ? $country_cnt[ $ckey ] + 1 : 1;
			}
		}

		// Pays ordonnés comme la frise (réutilise l'ordre éditorial déjà en place).
		// Le compte se lit sur la clé normalisée du libellé retenu.
		$countries = array();
		foreach ( fnc_edition_countries( $edition_id ) as $name ) {
			$nkey        = function_exists( 'fnc_country_key' ) ? fnc_country_key( $name ) : remove_accents( strtolower( $name ) );
			$countries[] = array(
				'name'  => $name,
				'count' => isset( $country_cnt[ $nkey ] ) ? $country_cnt[ $nkey ] : 0,
			);
		}

		return array(
			'total'     => count( $ids ),
			'profils'   => array_values( $profils ),
			'countries' => $countries,
		);
	}
}

/* ==========================================================================
 * NOTES D'INTÉGRATION (gabarits du thème)
 *
 *  • Compte à rebours (home) :
 *      <b><?php echo esc_html( fnc_days_until_edition() ); ?></b> <span>jours</span>
 *
 *  • Portraits d'intervenants (droit à l'image) — remplacer tout affichage direct de la
 *    vignette par :
 *      <?php echo fnc_speaker_portrait( $speaker_id, 'medium' ) ?: fnc_render_monogram( $speaker_id ); ?>
 *      (si le droit n'est pas acquis, la fonction renvoie '' -> afficher le monogramme, initiales)
 *
 *  • Frise « pays représentés » :
 *      <?php foreach ( fnc_edition_countries() as $pays ) : ?>
 *        <li class="flag-chip"><?php echo fnc_country_flag( $pays ); ?> <?php echo esc_html( $pays ); ?></li>
 *      <?php endforeach; ?>
 *
 *  • Programme (agenda) — le plugin groupe/trie, le thème dessine :
 *      <?php foreach ( fnc_edition_sessions_by_day() as $jour ) : ?>
 *        <section class="day"><h2>Jour <?php echo esc_html( $jour['day'] ); ?></h2>
 *          <?php foreach ( $jour['sessions'] as $s ) : ?>
 *            <article class="session">
 *              <span class="hour"><?php echo esc_html( $s['time'] ?: $s['start'] ); ?></span>
 *              <h3><?php echo esc_html( $s['title'] ); ?></h3>
 *              <?php // aussi disponibles : $s['room'], $s['moderator'], $s['speakers'] (IDs) ?>
 *            </article>
 *          <?php endforeach; ?>
 *        </section>
 *      <?php endforeach; ?>
 *
 *  • Filtres intervenants — la LISTE vient du plugin, le WIDGET reste au thème :
 *      <?php $f = fnc_speaker_facets(); // total, profils[], countries[] ?>
 *      <?php foreach ( $f['profils'] as $p ) : ?>
 *        <button data-profil="<?php echo esc_attr( $p['slug'] ); ?>">
 *          <?php echo esc_html( $p['name'] ); ?> (<?php echo (int) $p['count']; ?>)
 *        </button>
 *      <?php endforeach; ?>
 *
 *  • Inscription — résoudre l'édition active côté serveur :
 *      add_action( 'fnc_submission_stored', function ( $post_id, $type ) {
 *        if ( 'inscription' === $type ) {
 *          update_post_meta( $post_id, 'edition', fnc_current_edition_id() );
 *        }
 *      }, 10, 2 );
 * ======================================================================== */
