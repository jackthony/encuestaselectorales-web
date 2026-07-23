/**
 * Shared front-end logic (php-architecture spec / tasks.md 4.1): clock,
 * mobile menu, scroll-reveal — consolidated from the 5-6 copy-pasted
 * per-page versions in canvas-gemini/. Every DOM handler guards its target
 * (`if (!el) return;`, docs/engineering-standards.md §3) because these
 * partials now load on pages that don't contain every element.
 *
 * Differences found while consolidating (recorded per tasks.md 4.1):
 *  - Clock locale/format: 5 of 8 prototypes used `es-PE`, 1 used `es-ES`
 *    (portal_de_encuestas.html). `es-PE` wins (majority). The two markup
 *    shapes (`<div id="reloj"><i class="far fa-clock"></i> ...</div>` vs a
 *    plain-text `<div id="reloj">...</div>`) are both preserved per page —
 *    the icon, if present in a given page's own ticker markup, is kept and
 *    only the trailing date/time text is refreshed, so no page gains or
 *    loses an icon it didn't already have.
 *  - Mobile menu toggle: 7 of 8 prototypes use a plain `classList.toggle('hidden')`;
 *    only portal_de_encuestas.html used an extra CSS-transform/opacity
 *    animation with a setTimeout. The plain toggle wins (majority); the
 *    animated variant is dropped (BL-11 owns any UX/motion polish).
 *  - IntersectionObserver options: `{ threshold: 0.1, rootMargin: '0px 0px -20px 0px' }`
 *    wins — used verbatim by 3 of 8 prototypes (directorio_de_encuestadoras,
 *    metodolog_a, qui_nes_somos_autoridad); the others used minor variants
 *    of the same idea (0px, or -50px). The visual difference between these
 *    values is a few pixels of scroll-trigger timing, not a rendered class
 *    or element.
 *  - detalle_de_encuesta.html's observer additionally force-reflows any
 *    `.data-bar` inside a just-revealed section (reset width to 0, then
 *    restore) so the CSS transition actually plays instead of snapping to
 *    its final width. Kept, scoped to `.data-bar` only (matches the
 *    original — `.bar-fill` never had this treatment in any prototype, so
 *    it's left as-is, matching each page's original, including any
 *    pre-existing non-animating defect).
 */

(function () {
    'use strict';

    function updateClock() {
        var el = document.getElementById('reloj');
        if (!el) return;

        var now = new Date();
        var dateString = now.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' })
            + ' ' + now.toLocaleTimeString('es-PE', { hour12: false });

        var icon = el.querySelector('i');
        if (icon) {
            el.innerHTML = icon.outerHTML + ' ' + dateString;
        } else {
            el.textContent = dateString;
        }
    }

    function setupMobileMenu() {
        var btn = document.getElementById('mobile-menu-btn');
        var menu = document.getElementById('mobile-menu');
        if (!btn || !menu) return;

        btn.addEventListener('click', function () {
            menu.classList.toggle('hidden');
        });
    }

    function setupScrollAnimations() {
        var targets = document.querySelectorAll('.scroll-animate');
        if (!targets.length) return;

        var observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-visible');

                var bars = entry.target.querySelectorAll('.data-bar');
                bars.forEach(function (bar) {
                    var width = bar.style.width;
                    bar.style.width = '0';
                    setTimeout(function () { bar.style.width = width; }, 50);
                });

                obs.unobserve(entry.target);
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -20px 0px' });

        targets.forEach(function (el) { observer.observe(el); });
    }

    function setupHomeVotingFilters() {
        var list = document.getElementById('home-voting-list');
        var body = document.getElementById('home-voting-body');
        var toggleButton = document.getElementById('home-voting-toggle');
        var searchInput = document.getElementById('home-voting-search');
        var sortSelect = document.getElementById('home-voting-sort');
        var roundSelect = document.getElementById('home-voting-round');
        var resetButton = document.getElementById('home-voting-reset');
        var emptyState = document.getElementById('home-voting-empty');

        if (!list || !sortSelect || !roundSelect) return;

        function syncToggleState() {
            if (!body || !toggleButton) return;

            var isCollapsed = body.classList.contains('hidden');
            toggleButton.textContent = isCollapsed ? 'Mostrar listado' : 'Minimizar listado';
            toggleButton.setAttribute('aria-expanded', String(!isCollapsed));
        }

        function compareRows(a, b) {
            var sortValue = sortSelect.value || 'geo-asc';
            var aScope = parseInt(a.dataset.scopeRank || '3', 10);
            var bScope = parseInt(b.dataset.scopeRank || '3', 10);
            var aCode = (a.dataset.territoryCode || '').toString();
            var bCode = (b.dataset.territoryCode || '').toString();
            var aRound = parseInt(a.dataset.roundNumber || '1', 10);
            var bRound = parseInt(b.dataset.roundNumber || '1', 10);
            var scopeDiff = aScope - bScope;

            if (sortValue === 'geo-desc') {
                if (scopeDiff !== 0) return -scopeDiff;
                if (aCode !== bCode) return bCode.localeCompare(aCode);
                return bRound - aRound;
            }

            if (scopeDiff !== 0) return scopeDiff;
            if (aCode !== bCode) return aCode.localeCompare(bCode);
            return aRound - bRound;
        }

        function applyFilters() {
            var searchValue = searchInput ? searchInput.value.trim().toLowerCase() : '';
            var roundValue = roundSelect.value || 'all';
            var rows = Array.prototype.slice.call(list.querySelectorAll('[data-voting-row]'));

            rows.forEach(function (row) {
                var roundNumber = row.dataset.roundNumber || '';
                var matchesRound = roundValue === 'all' || String(roundNumber) === roundValue;
                var rowText = (row.textContent || '').toLowerCase();
                var matchesSearch = searchValue === '' || rowText.indexOf(searchValue) !== -1;
                row.classList.toggle('hidden', !matchesRound || !matchesSearch);
            });

            rows.sort(compareRows).forEach(function (row) {
                list.appendChild(row);
            });

            var visibleCount = rows.filter(function (row) {
                return !row.classList.contains('hidden');
            }).length;

            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount !== 0);
            }
        }

        sortSelect.addEventListener('change', applyFilters);
        roundSelect.addEventListener('change', applyFilters);
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                }
                sortSelect.value = 'geo-asc';
                roundSelect.value = 'all';
                applyFilters();
            });
        }

        if (body && toggleButton) {
            toggleButton.addEventListener('click', function () {
                body.classList.toggle('hidden');
                syncToggleState();
            });
            syncToggleState();
        }

        applyFilters();
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateClock();
        setInterval(updateClock, 1000);
        setupMobileMenu();
        setupScrollAnimations();
        setupHomeVotingFilters();
    });
})();
