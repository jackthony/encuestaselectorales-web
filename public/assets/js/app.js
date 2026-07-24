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

    function setupVoteLiveUpdates() {
        var root = document.querySelector('[data-vote-live-root]');
        if (!root) return;

        var territoryId = root.getAttribute('data-survey-territory-id') || '';
        var totalVotesEl = root.querySelector('[data-vote-live-total]');
        var bannerEl = root.querySelector('[data-vote-live-banner]');
        var emptyEl = root.querySelector('[data-vote-live-empty]');
        var listEl = root.querySelector('[data-vote-live-list]');
        var updatedAtEl = root.querySelector('[data-vote-live-updated-at]');

        if (!territoryId || !totalVotesEl || !emptyEl || !listEl) {
            return;
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('es-PE').format(Number(value || 0));
        }

        function formatDateTime(value) {
            if (!value) return '';

            var date = new Date(value);
            if (isNaN(date.getTime())) return '';

            return new Intl.DateTimeFormat('es-PE', {
                dateStyle: 'medium',
                timeStyle: 'short',
                timeZone: 'America/Lima',
            }).format(date);
        }

        function rankOptions(options) {
            return options.slice().sort(function (left, right) {
                var leftVotes = Number(left.vote_count || 0);
                var rightVotes = Number(right.vote_count || 0);
                if (leftVotes !== rightVotes) {
                    return rightVotes - leftVotes;
                }

                var leftOrder = Number(left.display_order || 0);
                var rightOrder = Number(right.display_order || 0);
                if (leftOrder !== rightOrder) {
                    return leftOrder - rightOrder;
                }

                return String(left.option_id || '').localeCompare(String(right.option_id || ''));
            });
        }

        function getPayload(response) {
            if (!response) return null;
            if (response.round || response.territory) {
                return response;
            }
            if (response.data && (response.data.round || response.data.territory)) {
                return response.data;
            }

            return null;
        }

        function getOptions(payload) {
            if (!payload || !payload.round) return [];
            if (Array.isArray(payload.ranked_options) && payload.ranked_options.length > 0) {
                return rankOptions(payload.ranked_options);
            }

            return rankOptions(Array.isArray(payload.round.options) ? payload.round.options : []);
        }

        function updateCard(card, option, totalVotes) {
            var voteCount = Number(option.vote_count || 0);
            var voteShare = totalVotes > 0 ? (voteCount / totalVotes) * 100 : 0;

            var nameEl = card.querySelector('[data-vote-live-name]');
            var partyEl = card.querySelector('[data-vote-live-party]');
            var votesEl = card.querySelector('[data-vote-live-votes]');
            var labelEl = card.querySelector('[data-vote-live-label]');
            var shareEl = card.querySelector('[data-vote-live-share]');
            var barEl = card.querySelector('[data-vote-live-bar]');

            if (nameEl) {
                nameEl.textContent = option.candidate && option.candidate.name ? option.candidate.name : '';
            }
            if (partyEl) {
                partyEl.textContent = option.party && option.party.name ? option.party.name : '';
            }
            if (votesEl) {
                votesEl.textContent = formatNumber(voteCount);
            }
            if (labelEl) {
                labelEl.textContent = option.candidate && option.candidate.name ? option.candidate.name : '';
            }
            if (shareEl) {
                shareEl.textContent = voteShare.toFixed(1) + '%';
            }
            if (barEl) {
                barEl.style.width = voteShare + '%';
            }

            card.setAttribute('data-display-order', String(option.display_order || 0));
        }

        function updateShareImage(lastVoteAt) {
            var wrapper = document.querySelector('[data-share-image-wrapper]');
            if (!wrapper || !lastVoteAt) return;

            var base = wrapper.getAttribute('data-share-image-base') || '';
            if (!base) return;

            var version = Math.floor(new Date(lastVoteAt).getTime() / 1000);
            if (!version || isNaN(version)) return;

            var versionedUrl = base + (base.indexOf('?') === -1 ? '?' : '&') + 'v=' + version;

            var img = wrapper.querySelector('[data-share-image]');
            if (img && img.getAttribute('src') !== versionedUrl) {
                img.setAttribute('src', versionedUrl);
            }

            var link = wrapper.querySelector('[data-share-image-link]');
            if (link) link.setAttribute('href', versionedUrl);

            var download = document.querySelector('[data-share-image-download]');
            if (download) download.setAttribute('href', versionedUrl);
        }

        function render(payload, showBanner) {
            var result = getPayload(payload);
            if (!result || !result.round) return;

            var options = getOptions(result);
            var lastVoteAt = result.round.last_vote_at || result.last_vote_at || '';
            updateShareImage(lastVoteAt);
            var totalVotes = Number(result.total_votes || (result.round && Array.isArray(result.round.options)
                ? result.round.options.reduce(function (sum, option) {
                    return sum + Number(option.vote_count || 0);
                }, 0)
                : 0));
            var cards = Array.prototype.slice.call(listEl.querySelectorAll('[data-vote-live-card]'));
            var cardByOptionId = {};
            var fragment = document.createDocumentFragment();

            cards.forEach(function (card) {
                cardByOptionId[String(card.getAttribute('data-option-id') || '')] = card;
            });

            totalVotesEl.textContent = formatNumber(totalVotes);
            emptyEl.classList.toggle('hidden', totalVotes !== 0);
            listEl.classList.toggle('hidden', totalVotes === 0);

            if (updatedAtEl) {
                var updatedLabel = formatDateTime(lastVoteAt);
                updatedAtEl.textContent = updatedLabel ? 'Última actualización ' + updatedLabel : '';
                updatedAtEl.classList.toggle('hidden', !updatedLabel);
            }

            options.forEach(function (option) {
                var optionId = String(option.option_id || '');
                var card = cardByOptionId[optionId];
                if (!card) return;

                updateCard(card, option, totalVotes);
                fragment.appendChild(card);
            });

            if (totalVotes > 0 && fragment.childNodes.length > 0) {
                listEl.innerHTML = '';
                listEl.appendChild(fragment);
            }

            if (showBanner && bannerEl) {
                bannerEl.classList.remove('hidden');
            }
        }

        function refresh(showBanner) {
            if (document.hidden) return;

            fetch('/api/territories/' + encodeURIComponent(territoryId) + '/survey-round', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                render(payload, showBanner);
            }).catch(function () {});
        }

        document.addEventListener('vote:registered', function (event) {
            var detail = event && event.detail ? event.detail : null;
            if (detail && detail.territoryId && detail.territoryId !== territoryId) {
                return;
            }

            refresh(true);
        });

        refresh(false);
        setInterval(function () {
            refresh(false);
        }, 15000);
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
            body.classList.remove('hidden');
            list.classList.remove('hidden');
            syncToggleState();
        }

        list.addEventListener('click', function (event) {
            var row = event.target.closest('[data-voting-row]');
            if (!row || !list.contains(row)) return;

            event.preventDefault();

            var targetUrl = row.getAttribute('data-target-url') || '';
            if (!targetUrl) return;

            window.location.href = targetUrl;
        });

        applyFilters();
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateClock();
        setInterval(updateClock, 1000);
        setupMobileMenu();
        setupScrollAnimations();
        setupVoteLiveUpdates();
        setupHomeVotingFilters();
    });
})();
