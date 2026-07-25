/**
 * Forum Numérique Congo — interactions front (navigation, animations, consentement).
 *
 * @package    Forum Numérique Congo
 * @author     Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright  © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 * @link       https://www.grinso.io
 */

(function () {
	'use strict';

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var nav = document.getElementById('nav');
	// Pages a bandeau lin (registre C, ex. pages legales) : pas de hero sombre
	// sous la barre → la nav reste solide en permanence (texte navy lisible).
	var linenHeader = document.body.classList.contains('linen-header');

	function onScroll() {
		if (!nav) {
			return;
		}
		if (linenHeader || window.scrollY > window.innerHeight * 0.6) {
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
	// (privacy-first, comme sur le site du Forum). Aucune requete vers le service
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
		// Le hero #m1 est EXCLU du reveal JS : le kit lui donne une entrée
		// cinématographique en CSS pur (#m1 .hero-inner > * → hero-enter), fiable
		// au chargement, là où le reveal JS « claque » au premier paint. Aligné
		// sur HomeMotion du site du Forum (voir wordpress-catchup-complet.css).
		document.querySelectorAll('main > section:not(#m1), footer').forEach(function (sec) {
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

	// Calque d'ambiance du hero (#m1) : nappe de points en vague dessinee sur
	// canvas. Port fidele de la variante « dots » du composant HeroBackdrop du
	// site reel. Statique (une passe) sous prefers-reduced-motion.
	(function heroBackdrop() {
		var canvas = document.querySelector('#m1 .hb-canvas');
		var ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;
		if (!canvas || !ctx) {
			return;
		}
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var w = 0;
		var h = 0;
		var raf = 0;
		var t = 0;
		var gap = 40;

		function resize() {
			var r = canvas.getBoundingClientRect();
			w = r.width;
			h = r.height;
			canvas.width = Math.max(1, Math.round(w * dpr));
			canvas.height = Math.max(1, Math.round(h * dpr));
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		}

		function draw() {
			ctx.clearRect(0, 0, w, h);
			if (!reduce) {
				t += 0.016;
			}
			for (var y = gap / 2; y < h; y += gap) {
				for (var x = gap / 2; x < w; x += gap) {
					var wave = Math.sin(x * 0.011 + y * 0.013 + t);
					var a = 0.11 + (wave + 1) * 0.11;
					var r = 0.55 + (wave + 1) * 0.5;
					ctx.fillStyle = 'rgba(214,218,255,' + a + ')';
					ctx.beginPath();
					ctx.arc(x, y, r, 0, Math.PI * 2);
					ctx.fill();
				}
			}
			if (!reduce) {
				raf = window.requestAnimationFrame(draw);
			}
		}

		resize();
		window.addEventListener('resize', function () {
			window.cancelAnimationFrame(raf);
			resize();
			draw();
		});
		draw();
	})();

	/* ------------------------------------------------------------------ *
	 * Carrousel « Les voix » (M3) : defilement auto (4 s), pause au survol
	 * et au focus, fleches, pastilles et lecture/pause. Toutes les voix sont
	 * montees dans le DOM ; on bascule l'active (fondu enchaine en CSS).
	 * Respecte prefers-reduced-motion (pas de defilement auto).
	 * ------------------------------------------------------------------ */
	(function () {
		var root = document.querySelector('[data-voices]');
		if (!root) { return; }
		var slides = root.querySelectorAll('.voice-slide');
		var ids = root.querySelectorAll('[data-voice-identity]');
		var dots = root.querySelectorAll('[data-voice-dot]');
		var prev = root.querySelector('[data-voice-prev]');
		var next = root.querySelector('[data-voice-next]');
		var play = root.querySelector('[data-voice-play]');
		var iconPause = root.querySelector('[data-voice-icon-pause]');
		var iconPlay = root.querySelector('[data-voice-icon-play]');
		var count = ids.length;
		if (count < 1) { return; }

		var active = 0;
		var paused = false;
		var playing = true;

		// aria-hidden : uniquement sur les portraits <img> (le monogramme reste
		// decoratif et toujours masque aux lecteurs d'ecran).
		function ariaHide(el, hide) {
			if (el && el.tagName === 'IMG') {
				if (hide) { el.setAttribute('aria-hidden', 'true'); } else { el.removeAttribute('aria-hidden'); }
			}
		}

		function show(i) {
			i = (i % count + count) % count;
			if (slides[active]) { slides[active].classList.remove('on'); ariaHide(slides[active], true); }
			if (ids[active]) { ids[active].classList.remove('on'); ids[active].setAttribute('hidden', ''); }
			if (dots[active]) { dots[active].classList.remove('on'); dots[active].removeAttribute('aria-current'); }

			active = i;

			if (slides[active]) { slides[active].classList.add('on'); ariaHide(slides[active], false); }
			if (ids[active]) {
				ids[active].removeAttribute('hidden');
				void ids[active].offsetWidth; // relance l'animation de fondu
				ids[active].classList.add('on');
			}
			if (dots[active]) { dots[active].classList.add('on'); dots[active].setAttribute('aria-current', 'true'); }
		}

		function step(delta) { show(active + delta); }

		if (prev) { prev.addEventListener('click', function () { step(-1); }); }
		if (next) { next.addEventListener('click', function () { step(1); }); }
		for (var d = 0; d < dots.length; d++) {
			(function (idx) { dots[idx].addEventListener('click', function () { show(idx); }); })(d);
		}
		if (play) {
			play.addEventListener('click', function () {
				playing = !playing;
				play.setAttribute('aria-pressed', playing ? 'false' : 'true');
				if (iconPause) { if (playing) { iconPause.removeAttribute('hidden'); } else { iconPause.setAttribute('hidden', ''); } }
				if (iconPlay) { if (playing) { iconPlay.setAttribute('hidden', ''); } else { iconPlay.removeAttribute('hidden'); } }
			});
		}

		root.addEventListener('mouseenter', function () { paused = true; });
		root.addEventListener('mouseleave', function () { paused = false; });
		root.addEventListener('focusin', function () { paused = true; });
		root.addEventListener('focusout', function () { paused = false; });

		window.setInterval(function () {
			if (paused || !playing || reduce || count < 2) { return; }
			step(1);
		}, 4000);
	})();
})();
