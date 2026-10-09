/* Client Logos — one-by-one infinite logo slider. Works with any number of sections per page. */
(function () {
	'use strict';

	var EASE = 'cubic-bezier(.45, .05, .25, 1)'; // soft ease-in-out
	var reduceMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

	function readSettings(root) {
		var s = {};
		try { s = JSON.parse(root.getAttribute('data-slider') || '{}'); } catch (e) {}
		return {
			autoplay: s.autoplay !== 0,
			delay: Math.max(1, parseFloat(s.delay) || 3) * 1000,
			speed: Math.max(200, parseInt(s.speed, 10) || 700),
			pauseHover: s.pause_hover !== 0,
			desktop: parseInt(s.per_desktop, 10) || 4,
			tablet: parseInt(s.per_tablet, 10) || 2,
			mobile: parseInt(s.per_mobile, 10) || 1
		};
	}

	function init(root) {
		var track = root.querySelector('.cl-track');
		var carousel = root.querySelector('.cl-carousel');
		var card = root.querySelector('.cl-card');
		if (!track || !carousel || !card) return;

		var cfg = readSettings(root);
		var dotsEl = root.querySelector('.cl-dots');
		var prev = root.querySelector('.cl-arrow--prev');
		var next = root.querySelector('.cl-arrow--next');

		var originals = Array.prototype.slice.call(track.children);
		var count = originals.length;
		var per = 0;          // logos visible at once
		var pos = 0;          // index in the track, clones included
		var sliding = false;  // true while a move is animating
		var enabled = false;  // false when every logo fits on screen
		var timer = null;
		var hovering = false, focused = false, inView = true;

		function perView() {
			var w = carousel.clientWidth;
			var p = w < 560 ? cfg.mobile : (w < 900 ? cfg.tablet : cfg.desktop);
			return Math.max(1, p);
		}

		function animated() {
			return !(reduceMotion && reduceMotion.matches);
		}

		function setPosition(animate) {
			track.style.transition = (animate && animated()) ? 'transform ' + cfg.speed + 'ms ' + EASE : 'none';
			track.style.transform = 'translate3d(' + (-pos * 100 / per) + '%, 0, 0)';
		}

		// Index of the first visible real logo (0 … count-1).
		function current() {
			return ((pos - per) % count + count) % count;
		}

		function updateState() {
			if (dotsEl) {
				var active = Math.floor(current() / per);
				Array.prototype.forEach.call(dotsEl.children, function (d, j) {
					d.setAttribute('aria-current', j === active ? 'true' : 'false');
				});
			}
			// Only visible, real logos are reachable by screen readers and Tab.
			Array.prototype.forEach.call(track.children, function (slot, j) {
				var visible = !slot.classList.contains('cl-clone') && j >= pos && j < pos + per;
				if (!enabled) visible = true;
				slot.setAttribute('aria-hidden', visible ? 'false' : 'true');
				var a = slot.querySelector('a');
				if (a) a.tabIndex = visible ? 0 : -1;
			});
		}

		// After moving onto a clone, jump silently to the matching real logo.
		function settle() {
			sliding = false;
			if (pos >= per + count) { pos -= count; setPosition(false); }
			else if (pos < per) { pos += count; setPosition(false); }
			updateState();
		}

		function moveTo(target) {
			if (!enabled || sliding || target === pos) return;
			pos = target;
			if (animated()) {
				sliding = true;
				setPosition(true);
				// Fallback in case transitionend doesn't fire (e.g. tab in background).
				clearTimeout(track._clFallback);
				track._clFallback = setTimeout(function () { if (sliding) settle(); }, cfg.speed + 80);
			} else {
				setPosition(false);
				settle();
			}
		}

		track.addEventListener('transitionend', function (e) {
			if (e.target === track && e.propertyName === 'transform' && sliding) settle();
		});

		/* ---------- autoplay ---------- */
		function stop() { clearTimeout(timer); timer = null; }

		function schedule() {
			stop();
			if (!cfg.autoplay || !enabled || !animated() || !inView || document.hidden) return;
			if (hovering || focused) return;
			timer = setTimeout(function () { moveTo(pos + 1); schedule(); }, cfg.delay);
		}

		function userMove(target) {
			moveTo(target);
			schedule(); // restart the countdown after a manual move
		}

		/* ---------- build / rebuild ---------- */
		// One dot per row of visible logos: 5 logos, 4 visible => 2 dots.
		function renderDots() {
			if (!dotsEl) return;
			dotsEl.innerHTML = '';
			if (!enabled) return;
			var pages = Math.ceil(count / per);
			for (var i = 0; i < pages; i++) {
				var d = document.createElement('button');
				d.type = 'button';
				d.className = 'cl-dot';
				d.setAttribute('aria-label', 'Group ' + (i + 1) + ' of ' + pages);
				d.addEventListener('click', goToGroup.bind(null, i));
				dotsEl.appendChild(d);
			}
		}

		// Slide to the first logo of group i, taking the shorter way round the loop.
		function goToGroup(i) {
			var base = per + i * per;
			var best = base;
			[base - count, base + count].forEach(function (c) {
				if (c >= 0 && c <= count + per && Math.abs(c - pos) < Math.abs(best - pos)) best = c;
			});
			userMove(best);
		}

		function build() {
			var p = Math.min(perView(), Math.max(count, 1));
			var keep = per ? current() : 0;
			if (p === per && track.children.length) return;
			per = p;
			enabled = count > per;

			// Rebuild as: [clones of last `per`] [originals] [clones of first `per`]
			// so the loop can wrap without a visible jump.
			var clone = function (el) {
				var c = el.cloneNode(true);
				c.classList.add('cl-clone');
				c.removeAttribute('id');
				return c;
			};
			var frag = document.createDocumentFragment();
			var i;
			if (enabled) for (i = count - per; i < count; i++) frag.appendChild(clone(originals[i]));
			originals.forEach(function (el) { frag.appendChild(el); });
			if (enabled) for (i = 0; i < per; i++) frag.appendChild(clone(originals[i]));
			track.innerHTML = '';
			track.appendChild(frag);

			track.style.setProperty('--cl-per', per);
			root.classList.toggle('cl-single', !enabled);
			pos = enabled ? per + keep : 0;
			sliding = false;
			setPosition(false);
			renderDots();
			updateState();
			schedule();
		}

		/* ---------- events ---------- */
		if (prev) prev.addEventListener('click', function () { userMove(pos - 1); });
		if (next) next.addEventListener('click', function () { userMove(pos + 1); });

		carousel.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') userMove(pos - 1);
			if (e.key === 'ArrowRight') userMove(pos + 1);
		});

		// Pause while the mouse is over the logos.
		if (cfg.pauseHover) {
			card.addEventListener('mouseenter', function () { hovering = true; schedule(); });
			card.addEventListener('mouseleave', function () { hovering = false; schedule(); });
		}
		// Pause while a keyboard user is inside the slider.
		carousel.addEventListener('focusin', function () { focused = true; schedule(); });
		carousel.addEventListener('focusout', function (e) {
			if (!e.relatedTarget || !carousel.contains(e.relatedTarget)) { focused = false; schedule(); }
		});

		// Swipe / drag
		var x0 = null;
		track.addEventListener('pointerdown', function (e) { x0 = e.clientX; stop(); });
		window.addEventListener('pointerup', function (e) {
			if (x0 === null) return;
			var dx = e.clientX - x0;
			x0 = null;
			if (Math.abs(dx) > 40) userMove(pos + (dx < 0 ? 1 : -1)); else schedule();
		});
		track.addEventListener('dragstart', function (e) { e.preventDefault(); });

		// Only run while visible on screen and in an active tab.
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				inView = entries[0].isIntersecting;
				schedule();
			}, { threshold: 0.25 }).observe(root);
		}
		document.addEventListener('visibilitychange', schedule);

		if ('ResizeObserver' in window) {
			new ResizeObserver(build).observe(carousel);
		} else {
			window.addEventListener('resize', build);
		}
		build();
	}

	function boot() {
		Array.prototype.forEach.call(document.querySelectorAll('.cl-section'), init);
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
