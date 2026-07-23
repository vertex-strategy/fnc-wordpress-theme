/**
 * FNC WordPress Theme — comportements de la page d'accueil.
 * Porte le script de docs/mockups/homepage-v2/index.html (ADR-007).
 */
(function () {
	'use strict';

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var nav = document.getElementById('nav');

	function onScroll() {
		if (!nav) {
			return;
		}
		if (window.scrollY > window.innerHeight * 0.6) {
			nav.classList.add('solid');
		} else {
			nav.classList.remove('solid');
		}
	}
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	// Menu mobile
	var burger = document.getElementById('burger');
	var panel = document.getElementById('mobile-panel');

	function setMenu(open) {
		if (!panel || !burger) {
			return;
		}
		panel.classList.toggle('open', open);
		burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		burger.setAttribute(
			'aria-label',
			open ? burger.dataset.labelClose || 'Fermer le menu' : burger.dataset.labelOpen || 'Ouvrir le menu'
		);
		panel.setAttribute('aria-hidden', open ? 'false' : 'true');
		document.body.classList.toggle('menu-open', open);
	}

	if (burger && panel) {
		burger.addEventListener('click', function () {
			setMenu(!panel.classList.contains('open'));
		});
		panel.querySelectorAll('a').forEach(function (a) {
			a.addEventListener('click', function () {
				setMenu(false);
			});
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				setMenu(false);
			}
		});
	}

	// Reveal au scroll (amelioration progressive)
	if (!reduce) {
		document.body.classList.add('js-reveal');
		var sel =
			'.eyebrow,.kicker,h1,.tagline,.actions,.lines,.link-more,figure,.name,.role,.org,.quote,.dots,.manifest,.sub,h2,.prog-date,.rule,.session,.tier,.rule-c,.arch,.count-label,#m8 .btn-red,.foot-grid > *';
		document.querySelectorAll('main > section, footer').forEach(function (sec) {
			sec.querySelectorAll(sel).forEach(function (el, i) {
				el.classList.add('reveal');
				el.style.setProperty('--d', Math.min(i, 7) * 70 + 'ms');
			});
		});
		var io = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (e) {
					if (e.isIntersecting) {
						e.target.classList.add('in');
						io.unobserve(e.target);
					}
				});
			},
			{ threshold: 0.1, rootMargin: '0px 0px -6% 0px' }
		);
		document.querySelectorAll('.reveal').forEach(function (el) {
			io.observe(el);
		});
	}
})();
