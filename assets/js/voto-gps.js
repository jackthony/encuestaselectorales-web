/**
 * GPS vote-confirmation state machine (tasks.md 4.2). Source:
 * canvas-gemini/flujo_de_votaci_n_gps.html — behavior unchanged from the
 * prototype, including the `alert()` calls on missing-candidate-selection
 * and on GPS permission denial. That's hostile UX and BL-11 owns fixing it
 * ("Notes carried forward" in tasks.md) — BL-10 is a structural refactor
 * only, it preserves current behavior including its current defects
 * (proposal.md).
 *
 * Pair with partials/widget-gps.php (the modal markup) and a page-specific
 * vote form whose candidate radios are named `candidato`.
 */

(function () {
    'use strict';

    var startTime;

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
        /*
         * AQUÍ SUCEDE LA MAGIA EN EL BACKEND (Simulado en JS para el frontend).
         * En un entorno real, enviarías lat/lng a PHP, PHP haría un Reverse Geocoding
         * rápido usando la BD espacial y determinaría que el usuario está en "San Isidro".
         * Como el voto era para "Miraflores", PHP devuelve un flag de "Mismatch".
         */
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var accuracy = position.coords.accuracy;
        var interactionTime = Date.now() - startTime;

        console.log('Datos para la Bóveda:', { lat: lat, lng: lng, accuracy: accuracy, interactionTime: interactionTime });

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

    function finalizarVoto(distritoFinal) {
        /*
         * Aquí envías el JSON final a `/api/vote.php`.
         * Si eligió 'Miraflores', guardas { is_out_of_district: true }.
         * Si eligió 'San Isidro', cambias el `distrito_id` del voto.
         */
        ocultarTodasLasVistas();
        if (vistaCargando) vistaCargando.classList.remove('hidden');

        setTimeout(function () {
            ocultarTodasLasVistas();
            if (vistaExito) vistaExito.classList.remove('hidden');
        }, 1000);
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
