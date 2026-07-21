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

    var overlay, vistaSoftAsk, vistaCargando, vistaSmartMatch, vistaExito;

    function ocultarTodasLasVistas() {
        [vistaSoftAsk, vistaCargando, vistaSmartMatch, vistaExito].forEach(function (v) {
            if (v) v.classList.add('hidden');
        });
    }

    function iniciarValidacion() {
        var seleccionado = document.querySelector('input[name="candidato"]:checked');
        if (!seleccionado) {
            alert('Por favor, selecciona un candidato primero.');
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
            alert('Tu navegador no soporta geolocalización.');
            cerrarModal();
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
        ocultarTodasLasVistas();
        alert('No podemos registrar tu voto sin validación geográfica. Permiso denegado.');
        cerrarModal();
    }

    function getVoteContext() {
        var host = document.getElementById('voto-panel') || document.querySelector('[data-encuesta-id][data-ubigeo-votacion]');
        if (!host) {
            return null;
        }

        return {
            encuestaId: host.getAttribute('data-encuesta-id') || '',
            ubigeoVotacion: host.getAttribute('data-ubigeo-votacion') || '',
            distritoNombre: host.getAttribute('data-distrito-nombre') || ''
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
            navigator.userAgent || '',
            navigator.language || '',
            window.screen ? window.screen.width + 'x' + window.screen.height : '',
            window.screen ? window.screen.colorDepth : '',
            navigator.hardwareConcurrency || '',
            navigator.deviceMemory || '',
            timezone
        ].join('|');
    }

    function finalizarVoto() {
        var context = getVoteContext();
        var candidatoId = getSelectedCandidateId();

        if (!context || !context.encuestaId || !context.ubigeoVotacion) {
            alert('No encontramos la encuesta activa de este distrito.');
            return;
        }

        if (!candidatoId) {
            alert('Por favor, selecciona un candidato primero.');
            return;
        }

        if (!latestGps) {
            alert('Primero valida tu ubicación.');
            return;
        }

        ocultarTodasLasVistas();
        if (vistaCargando) vistaCargando.classList.remove('hidden');

        fetch('api/votar.php?encuesta_id=' + encodeURIComponent(context.encuestaId), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ubigeo_votacion: context.ubigeoVotacion,
                tipo_voto: 'candidato',
                candidato_id: parseInt(candidatoId, 10),
                gps_lat: latestGps.lat,
                gps_lng: latestGps.lng,
                gps_accuracy_meters: latestGps.accuracy,
                interaction_time_ms: latestGps.interactionTime,
                browser_fingerprint: buildBrowserFingerprint()
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
        }).then(function () {
            ocultarTodasLasVistas();
            if (vistaExito) vistaExito.classList.remove('hidden');
        }).catch(function (error) {
            alert(error.message || 'No pudimos registrar tu voto.');
            cerrarModal();
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
    });

    // Exposed globally: partials/widget-gps.php and page-specific vote
    // buttons call these via inline onclick=, matching the prototype.
    window.iniciarValidacion = iniciarValidacion;
    window.solicitarGPS = solicitarGPS;
    window.finalizarVoto = finalizarVoto;
    window.cerrarModal = cerrarModal;
})();
