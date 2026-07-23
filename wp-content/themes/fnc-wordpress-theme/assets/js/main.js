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

	// Heros de la page d'accueil : slider et video (Lot 3).
	// Le mode est choisi dans le Customizer ; le CSS gere deja l'affichage
	// statique en cas de prefers-reduced-motion, ce script ne fait que piloter
	// le mouvement — et s'abstient totalement si l'utilisateur l'a refuse.
	var heroVideo = document.querySelector('.hero-video');
	if (heroVideo && reduce) {
		// Le CSS masque la video ; on evite en plus de la laisser jouer.
		heroVideo.pause();
		heroVideo.removeAttribute('autoplay');
	}

	var slider = document.querySelector('.hero-slider');
	if (slider && !reduce) {
		var slides = slider.querySelectorAll('.hero-slide');
		if (slides.length > 1) {
			var interval = parseInt(slider.dataset.interval, 10) || 6000;
			var current = 0;
			var timer = null;

			function showSlide(index) {
				slides[current].classList.remove('is-active');
				current = index % slides.length;
				slides[current].classList.add('is-active');
			}

			function start() {
				if (!timer) {
					timer = window.setInterval(function () {
						showSlide(current + 1);
					}, interval);
				}
			}

			function stop() {
				if (timer) {
					window.clearInterval(timer);
					timer = null;
				}
			}

			// Ne pas consommer de ressources quand l'onglet est en arriere-plan.
			document.addEventListener('visibilitychange', function () {
				if (document.hidden) {
					stop();
				} else {
					start();
				}
			});

			start();
		}
	}

	// Carte des informations pratiques : chargement au clic uniquement
	// (privacy-first, comme le vrai site). Aucune requete vers le service
	// tiers n'est emise tant que l'utilisateur ne l'a pas demande.
	document.querySelectorAll('.pract-map').forEach(function (wrap) {
		var btn = wrap.querySelector('.pract-map-load');
		var url = wrap.dataset.mapUrl;
		if (!btn || !url) {
			return;
		}
		btn.addEventListener('click', function () {
			var frame = document.createElement('iframe');
			frame.src = url;
			frame.loading = 'lazy';
			frame.referrerPolicy = 'no-referrer';
			// Le titre decrit le CONTENU du cadre, pas l'action du bouton.
			frame.setAttribute('title', wrap.dataset.mapTitle || 'Carte du lieu');
			wrap.innerHTML = '';
			wrap.appendChild(frame);
		});
	});

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
