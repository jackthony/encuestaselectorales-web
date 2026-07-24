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
        var selectedEmpty = document.getElementById('selected-vote-empty');
        var selectedContent = document.getElementById('selected-vote-content');
        var selectedScope = document.getElementById('selected-vote-scope');
        var selectedTerritory = document.getElementById('selected-vote-territory');
        var selectedTitle = document.getElementById('selected-vote-title');
        var selectedState = document.getElementById('selected-vote-state');
        var selectedTotal = document.getElementById('selected-vote-total');
        var selectedTotal2 = document.getElementById('selected-vote-total-2');
        var selectedAction = document.getElementById('selected-vote-action');
        var selectedZero = document.getElementById('selected-vote-zero');
        var selectedOptions = document.getElementById('selected-vote-options');
        var homeSelectedTerritory = document.getElementById('home-selected-territory');
        var homeSelectedRound = document.getElementById('home-selected-round');
        var initialSelectionEl = document.getElementById('home-initial-selection');
        var currentSelection = null;

        if (!list || !sortSelect || !roundSelect) return;

        try {
            currentSelection = initialSelectionEl ? JSON.parse(initialSelectionEl.textContent || 'null') : null;
        } catch (error) {
            currentSelection = null;
        }

        function formatNumber(value) {
            var numeric = Number(value || 0);
            return new Intl.NumberFormat('es-PE').format(numeric);
        }

        function scopeLabel(scope) {
            if (scope === 'region') return 'Región';
            if (scope === 'province') return 'Provincia';
            return 'Distrito';
        }

        function setVisibility(el, visible) {
            if (!el) return;
            el.classList.toggle('hidden', !visible);
        }

        function renderSelection(roundData) {
            if (!roundData || !roundData.territory || !roundData.round) return;

            var territory = roundData.territory;
            var round = roundData.round;
            var options = Array.isArray(roundData.top_options) ? roundData.top_options.slice(0, 5) : [];
            var totalVotes = Number(roundData.total_votes || 0);

            if (selectedScope) selectedScope.textContent = scopeLabel(territory.scope_type);
            if (selectedTerritory) selectedTerritory.textContent = territory.name || 'Territorio';
            if (selectedTitle) selectedTitle.textContent = round.title || 'Encuesta activa';
            if (selectedState) {
                selectedState.innerHTML = '<span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>' + (roundData.state === 'active' ? 'Abierta' : 'En revisión');
            }
            if (selectedTotal) selectedTotal.textContent = formatNumber(totalVotes);
            if (selectedTotal2) selectedTotal2.textContent = formatNumber(totalVotes);
            if (selectedAction) {
                var scope = territory.scope_type || 'district';
                var slug = territory.slug || '';
                selectedAction.href = '/encuestas/' + scope + '/' + slug;
            }
            if (homeSelectedTerritory) homeSelectedTerritory.textContent = territory.name || 'Sin selección';
            if (homeSelectedRound) homeSelectedRound.textContent = round.title || 'Selecciona una votación';

            setVisibility(selectedEmpty, false);
            setVisibility(selectedContent, true);

            if (selectedZero) setVisibility(selectedZero, totalVotes === 0);
            if (selectedOptions) setVisibility(selectedOptions, true);

            var optionCards = selectedOptions ? selectedOptions.querySelectorAll('[data-selected-option-slot]') : [];
            Array.prototype.forEach.call(optionCards, function (card, index) {
                var option = options[index] || null;
                var isPlaceholder = !option;
                var voteCount = isPlaceholder ? 0 : Number(option.vote_count || 0);
                var share = totalVotes > 0 ? (voteCount / totalVotes) * 100 : 0;

                var nameEl = card.querySelector('[data-selected-option-name]');
                var partyEl = card.querySelector('[data-selected-option-party]');
                var votesEl = card.querySelector('[data-selected-option-votes]');
                var labelEl = card.querySelector('[data-selected-option-label]');
                var shareEl = card.querySelector('[data-selected-option-share]');
                var barEl = card.querySelector('[data-selected-option-bar]');

                if (nameEl) nameEl.textContent = isPlaceholder ? 'Sin candidatura' : (option.candidate && option.candidate.name ? option.candidate.name : '');
                if (partyEl) partyEl.textContent = isPlaceholder ? 'Espacio reservado' : (option.party && option.party.name ? option.party.name : '');
                if (votesEl) votesEl.textContent = formatNumber(voteCount);
                if (labelEl) labelEl.textContent = isPlaceholder ? 'Pendiente' : (option.candidate && option.candidate.name ? option.candidate.name : '');
                if (shareEl) shareEl.textContent = share.toFixed(1) + '%';
                if (barEl) barEl.style.width = share + '%';
            });

            var rows = Array.prototype.slice.call(list.querySelectorAll('[data-voting-row]'));
            rows.forEach(function (row) {
                var payloadText = row.getAttribute('data-voting-payload') || '';
                try {
                    var payload = JSON.parse(window.atob(payloadText));
                    var sameScope = payload && payload.territory && territory && payload.territory.slug === territory.slug && payload.territory.scope_type === territory.scope_type;
                    row.setAttribute('aria-current', sameScope ? 'page' : 'false');
                    row.classList.toggle('ring-2', sameScope);
                    row.classList.toggle('ring-brand-blue', sameScope);
                } catch (error) {
                    row.removeAttribute('aria-current');
                }
            });
        }

        function syncToggleState() {
            if (!body || !toggleButton || !list) return;

            var isCollapsed = body.classList.contains('hidden');
            list.classList.toggle('hidden', isCollapsed);
            toggleButton.innerHTML = isCollapsed
                ? '<i class="fas fa-chevron-down text-[11px]" aria-hidden="true"></i><span>Ver listado</span>'
                : '<i class="fas fa-chevron-up text-[11px]" aria-hidden="true"></i><span>Ocultar listado</span>';
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
                if (body) {
                    body.classList.remove('hidden');
                }
                if (list) {
                    list.classList.remove('hidden');
                }
                applyFilters();
                syncToggleState();
            });
        }

        if (body && toggleButton) {
            toggleButton.addEventListener('click', function () {
                body.classList.toggle('hidden');
                syncToggleState();
            });
            if (!currentSelection) {
                body.classList.remove('hidden');
                list.classList.remove('hidden');
            }
            syncToggleState();
        }

        list.addEventListener('click', function (event) {
            var row = event.target.closest('[data-voting-row]');
            if (!row || !list.contains(row)) return;

            event.preventDefault();

            var payloadText = row.getAttribute('data-voting-payload') || '';
            var payload = null;

            try {
                payload = JSON.parse(window.atob(payloadText));
            } catch (error) {
                payload = null;
            }

            if (payload && payload.territory && payload.round) {
                currentSelection = payload;
                renderSelection(payload);

                if (body && !body.classList.contains('hidden')) {
                    body.classList.add('hidden');
                }

                syncToggleState();

                var target = document.getElementById('votacion-seleccionada');
                if (target && target.scrollIntoView) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }

                return;
            }

            var targetUrl = row.getAttribute('data-target-url') || '';
            if (!targetUrl) return;

            window.location.href = targetUrl + '#votacion-seleccionada';
        });

        if (currentSelection) {
            renderSelection(currentSelection);
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
