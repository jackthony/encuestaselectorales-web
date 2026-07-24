/**
 * GPS vote-confirmation flow for BL-14.
 *
 * Pair with partials/widget-gps.php (the modal markup) and a page-specific
 * vote form whose candidate radios are named `candidato`.
 */

(function () {
    'use strict';

    var startTime;
    var latestGps = null;

    var overlay, vistaSoftAsk, vistaCargando, vistaSmartMatch, vistaExito, vistaError, vistaErrorTexto, vistaErrorTitulo;

    function ocultarTodasLasVistas() {
        [vistaSoftAsk, vistaCargando, vistaSmartMatch, vistaExito, vistaError].forEach(function (v) {
            if (v) v.classList.add('hidden');
        });
    }

    function mostrarError(mensaje, titulo) {
        ocultarTodasLasVistas();
        if (vistaErrorTitulo) {
            vistaErrorTitulo.textContent = titulo || 'No pudimos registrar el voto';
        }
        if (vistaErrorTexto) {
            vistaErrorTexto.textContent = mensaje || 'No pudimos registrar tu voto.';
        }
        if (overlay) overlay.classList.remove('hidden');
        if (vistaError) vistaError.classList.remove('hidden');
    }

    function messageForGpsError(error) {
        switch (error && error.code) {
            case 1:
                return {
                    title: 'Activa tu ubicación',
                    message: 'Debes permitir el acceso a ubicación para continuar con tu voto.',
                };
            case 2:
                return {
                    title: 'No detectamos tu ubicación',
                    message: 'Tu navegador no pudo obtener una posición válida. Revisa el GPS o inténtalo otra vez.',
                };
            case 3:
                return {
                    title: 'La validación tardó demasiado',
                    message: 'La confirmación de ubicación se quedó sin tiempo. Vuelve a intentarlo.',
                };
            default:
                return {
                    title: 'No pudimos validar tu ubicación',
                    message: 'Revisa la ubicación de tu equipo e inténtalo nuevamente.',
                };
        }
    }

    function messageForVoteResponse(response, data) {
        var code = data && typeof data.code === 'string' ? data.code : '';

        if (code === 'duplicate_vote' || response.status === 409) {
            return {
                title: 'Voto ya registrado',
                message: data && data.message ? data.message : 'Ya registramos un voto para esta encuesta desde este dispositivo o conexión.',
            };
        }

        if (code === 'geographic_validation_failed' || response.status === 422) {
            if (data && data.errors && typeof data.errors === 'object') {
                var firstField = Object.keys(data.errors)[0];
                var firstError = firstField && Array.isArray(data.errors[firstField]) ? data.errors[firstField][0] : null;

                if (firstError) {
                    return {
                        title: 'Datos de voto inválidos',
                        message: firstError,
                    };
                }
            }

            return {
                title: 'Ubicación fuera de ámbito',
                message: data && data.message ? data.message : 'No pudimos validar tu ubicación dentro del ámbito de esta encuesta.',
            };
        }

        if (code === 'network_validation_failed' || response.status === 503) {
            return {
                title: 'No pudimos validar la conexión',
                message: data && data.message ? data.message : 'Inténtalo nuevamente.',
            };
        }

        if (response.status === 422 && data && data.errors) {
            return {
                title: 'Revisa tu voto',
                message: 'La información enviada ya no es válida. Selecciona nuevamente y vuelve a intentarlo.',
            };
        }

        return {
            title: 'No pudimos registrar el voto',
            message: data && data.message ? data.message : 'No pudimos registrar tu voto.',
        };
    }

    function iniciarValidacion() {
        var context = getVoteContext();
        var seleccionado = document.querySelector('input[name="candidato"]:checked');
        if (context && context.surveyRoundId && hasStoredVoteForRound(context.surveyRoundId)) {
            mostrarError('Ya registraste tu voto en esta encuesta. Revisa el conteo actual.', 'Voto ya registrado');
            return;
        }

        if (!seleccionado) {
            mostrarError('Por favor, selecciona un candidato primero.');
            return;
        }

        ocultarTodasLasVistas();
        if (overlay) overlay.classList.remove('hidden');
        if (vistaSoftAsk) vistaSoftAsk.classList.remove('hidden');
    }

    function solicitarGPS() {
        startTime = Date.now();

        ocultarTodasLasVistas();
        if (vistaCargando) vistaCargando.classList.remove('hidden');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                gpsExito,
                gpsError,
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            mostrarError('Tu navegador no soporta geolocalización.');
        }
    }

    function gpsExito(position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var accuracy = position.coords.accuracy;
        var interactionTime = Date.now() - startTime;

        latestGps = {
            lat: lat,
            lng: lng,
            accuracy: accuracy,
            interactionTime: interactionTime
        };

        setTimeout(function () {
            ocultarTodasLasVistas();
            if (vistaSmartMatch) vistaSmartMatch.classList.remove('hidden');
        }, 1500);
    }

    function gpsError(error) {
        var payload = messageForGpsError(error || {});
        mostrarError(payload.message, payload.title);
    }

    function getVoteContext() {
        var host = document.getElementById('voto-panel') || document.querySelector('[data-survey-round-id]');
        if (!host) {
            return null;
        }

        return {
            territoryId: host.getAttribute('data-territory-id') || '',
            surveyRoundId: host.getAttribute('data-survey-round-id') || host.getAttribute('data-encuesta-id') || '',
            territoryName: host.getAttribute('data-territory-name') || host.getAttribute('data-distrito-nombre') || ''
        };
    }

    function getSelectedCandidateId() {
        var seleccionado = document.querySelector('input[name="candidato"]:checked');
        return seleccionado ? seleccionado.value : '';
    }

    function buildBrowserFingerprint() {
        var timezone = '';
        try {
            timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {}

        return [
            navigator.platform || '',
            navigator.maxTouchPoints || '',
            window.screen ? window.screen.width + 'x' + window.screen.height : '',
            window.screen ? window.screen.colorDepth : '',
            navigator.hardwareConcurrency || '',
            navigator.deviceMemory || '',
            timezone
        ].join('|');
    }

    function getStoredDeviceToken() {
        var storageKey = 'encuestaselectorales.device_token';
        var token = '';

        try {
            token = window.localStorage.getItem(storageKey) || '';
        } catch (e) {}

        return token.toLowerCase();
    }

    function getStoredVotedRounds() {
        var storageKey = 'encuestaselectorales.voted_rounds';
        var stored = {};

        try {
            stored = JSON.parse(window.localStorage.getItem(storageKey) || '{}') || {};
        } catch (e) {
            stored = {};
        }

        return stored && typeof stored === 'object' ? stored : {};
    }

    function storeVotedRound(roundId, voteId) {
        if (!roundId) {
            return;
        }

        var storageKey = 'encuestaselectorales.voted_rounds';
        var stored = getStoredVotedRounds();

        stored[roundId] = {
            vote_id: voteId || null,
            recorded_at: new Date().toISOString()
        };

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(stored));
        } catch (e) {}
    }

    function hasStoredVoteForRound(roundId) {
        if (!roundId) {
            return false;
        }

        return Object.prototype.hasOwnProperty.call(getStoredVotedRounds(), roundId);
    }

    function syncVoteAlreadyState() {
        var context = getVoteContext();
        if (!context || !context.surveyRoundId) {
            return;
        }

        var banner = document.querySelector('[data-vote-already-registered]');
        var button = document.getElementById('registrar-voto-btn');
        var hasVote = hasStoredVoteForRound(context.surveyRoundId);

        if (banner) {
            banner.classList.toggle('hidden', !hasVote);
        }

        if (button) {
            button.disabled = hasVote;
            button.classList.toggle('opacity-60', hasVote);
            button.classList.toggle('cursor-not-allowed', hasVote);

            if (hasVote) {
                button.innerHTML = '<i class="fas fa-check"></i> Ya votaste';
            }
        }
    }

    function storeDeviceToken(token) {
        var storageKey = 'encuestaselectorales.device_token';
        if (!/^[a-f0-9]{64}$/i.test(token)) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, token.toLowerCase());
        } catch (e) {}
    }

    function emitVoteRegistered(detail) {
        if (!detail || typeof document === 'undefined' || typeof document.dispatchEvent !== 'function') {
            return;
        }

        document.dispatchEvent(new CustomEvent('vote:registered', {
            detail: detail
        }));
    }

    function volverAResultados() {
        cerrarModal();

        var target = document.getElementById('conteo-actual');
        if (target && typeof target.scrollIntoView === 'function') {
            setTimeout(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        }
    }

    function finalizarVoto() {
        var context = getVoteContext();
        var surveyOptionId = getSelectedCandidateId();

        if (!context || !context.surveyRoundId) {
            mostrarError('No encontramos la encuesta activa de este ámbito electoral.', 'Encuesta no disponible');
            return;
        }

        if (!surveyOptionId) {
            mostrarError('Por favor, selecciona un candidato primero.', 'Selecciona un candidato');
            return;
        }

        if (!latestGps) {
            mostrarError('Primero valida tu ubicación.', 'Falta validar ubicación');
            return;
        }

        ocultarTodasLasVistas();
        if (vistaCargando) vistaCargando.classList.remove('hidden');

        fetch('/api/votes', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                survey_round_id: context.surveyRoundId,
                survey_option_id: surveyOptionId,
                gps_latitude: latestGps.lat,
                gps_longitude: latestGps.lng,
                gps_accuracy_meters: latestGps.accuracy,
                interaction_time_ms: latestGps.interactionTime,
                browser_fingerprint: buildBrowserFingerprint(),
                device_token: getStoredDeviceToken()
            })
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    var errorPayload = messageForVoteResponse(response, data || {});
                    var error = new Error(errorPayload.message);
                    error.title = errorPayload.title;
                    throw error;
                }
                return data;
            });
        }).then(function (data) {
            if (data && data.device_token) {
                storeDeviceToken(data.device_token);
            }
            storeVotedRound(context.surveyRoundId, data && data.data ? data.data.vote_id : null);
            syncVoteAlreadyState();
            emitVoteRegistered({
                territoryId: context.territoryId || '',
                surveyRoundId: context.surveyRoundId || '',
                voteId: data && data.data ? data.data.vote_id : null,
                deviceToken: data && data.device_token ? data.device_token : null,
                result: data && data.data ? data.data.result : null
            });
            ocultarTodasLasVistas();
            if (vistaExito) vistaExito.classList.remove('hidden');
        }).catch(function (error) {
            mostrarError(error.message || 'No pudimos registrar tu voto.', error.title || null);
        });
    }

    function cerrarModal() {
        if (overlay) overlay.classList.add('hidden');
        ocultarTodasLasVistas();
    }

    document.addEventListener('DOMContentLoaded', function () {
        overlay = document.getElementById('modal-overlay');
        vistaSoftAsk = document.getElementById('paso-softask');
        vistaCargando = document.getElementById('paso-cargando');
        vistaSmartMatch = document.getElementById('paso-smartmatch');
        vistaExito = document.getElementById('paso-exito');
        vistaError = document.getElementById('paso-error');
        vistaErrorTitulo = document.getElementById('paso-error-title');
        vistaErrorTexto = document.getElementById('paso-error-texto');
        syncVoteAlreadyState();
    });

    // Exposed globally: partials/widget-gps.php and page-specific vote
    // buttons call these via inline onclick=, matching the prototype.
    window.iniciarValidacion = iniciarValidacion;
    window.solicitarGPS = solicitarGPS;
    window.finalizarVoto = finalizarVoto;
    window.cerrarModal = cerrarModal;
    window.volverAResultados = volverAResultados;
})();
