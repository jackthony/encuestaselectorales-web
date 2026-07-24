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

    var overlay, vistaSoftAsk, vistaCargando, vistaSmartMatch, vistaExito, vistaError, vistaErrorTexto;

    function ocultarTodasLasVistas() {
        [vistaSoftAsk, vistaCargando, vistaSmartMatch, vistaExito, vistaError].forEach(function (v) {
            if (v) v.classList.add('hidden');
        });
    }

    function mostrarError(mensaje) {
        ocultarTodasLasVistas();
        if (vistaErrorTexto) {
            vistaErrorTexto.textContent = mensaje || 'No pudimos registrar tu voto.';
        }
        if (overlay) overlay.classList.remove('hidden');
        if (vistaError) vistaError.classList.remove('hidden');
    }

    function iniciarValidacion() {
        var seleccionado = document.querySelector('input[name="candidato"]:checked');
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
        mostrarError('No podemos registrar tu voto sin validación geográfica. Permiso denegado.');
    }

    function getVoteContext() {
        var host = document.getElementById('voto-panel') || document.querySelector('[data-survey-round-id]');
        if (!host) {
            return null;
        }

        return {
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
            mostrarError('No encontramos la encuesta activa de este ámbito electoral.');
            return;
        }

        if (!surveyOptionId) {
            mostrarError('Por favor, selecciona un candidato primero.');
            return;
        }

        if (!latestGps) {
            mostrarError('Primero valida tu ubicación.');
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
                    var message = data && data.message ? data.message : 'No pudimos registrar tu voto.';
                    throw new Error(message);
                }
                return data;
            });
        }).then(function (data) {
            if (data && data.device_token) {
                storeDeviceToken(data.device_token);
            }
            emitVoteRegistered({
                voteId: data && data.data ? data.data.vote_id : null,
                deviceToken: data && data.device_token ? data.device_token : null,
                result: data && data.data ? data.data.result : null
            });
            ocultarTodasLasVistas();
            if (vistaExito) vistaExito.classList.remove('hidden');
        }).catch(function (error) {
            mostrarError(error.message || 'No pudimos registrar tu voto.');
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
        vistaErrorTexto = document.getElementById('paso-error-texto');
    });

    // Exposed globally: partials/widget-gps.php and page-specific vote
    // buttons call these via inline onclick=, matching the prototype.
    window.iniciarValidacion = iniciarValidacion;
    window.solicitarGPS = solicitarGPS;
    window.finalizarVoto = finalizarVoto;
    window.cerrarModal = cerrarModal;
    window.volverAResultados = volverAResultados;
})();
