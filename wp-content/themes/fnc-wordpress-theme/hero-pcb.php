<?php
/**
 * Filet PCB animé — partial unique des héros registre A (.opening) et B
 * (.page-head), conforme à internal-pages-hero-spec.md §2.
 *
 * Deux tracés (rouge .r, jaune .y) SANS attribut stroke inline : l'animation
 * « dessin de circuit » (dad-trace) vient entièrement du CSS (.pcb-band path,
 * .pcb-band .r/.y). Ne JAMAIS ajouter de stroke inline ici (cela figerait
 * l'animation — c'est le filet statique du registre C, différent).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<svg class="pcb-band" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
	<path class="r" d="M0 40 H420 l20 -20 H820 l20 20 H1200" />
	<path class="y" d="M0 20 H300 l24 20 H900 l18 -14 H1200" />
</svg>
