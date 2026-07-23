<?php
/**
 * GPS vote-confirmation modal (php-architecture spec, "Repeated UI lives in
 * partials"). Source: canvas-gemini/flujo_de_votaci_n_gps.html, the
 * `#modal-overlay` element and its 4 steps (soft-ask, loading, smart-match,
 * success) — markup only, verbatim. JS state machine lives in
 * assets/js/voto-gps.js (tasks.md 3.3/4.2).
 *
 * The modal now routes validation and permission errors through the inline
 * error step instead of browser alerts so the flow stays usable on mobile.
 *
 * Include this partial on any page that offers a vote action (currently
 * current survey flow).
 */
?>
    <div id="modal-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden flex items-center justify-center p-4">

        <!-- PASO 1: EL SOFT-ASK (Justificando el permiso) -->
        <div id="paso-softask" class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl hidden fade-in">
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-brand-blue text-xl mb-4 mx-auto">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h4 class="text-center font-serif text-xl font-bold text-brand-blue mb-2">Verificación de Seguridad</h4>
            <p class="text-center text-sm text-gray-600 mb-6 leading-relaxed">
                Para garantizar la pureza de esta encuesta y evitar que "granjas de bots" manipulen los resultados, necesitamos validar tu ubicación física.
                <br><br>
                <strong class="text-gray-800">Tu privacidad está segura:</strong> Registramos coordenadas y precisión para validar el ámbito; no publicamos ni compartimos tu ubicación.
            </p>
            <button onclick="solicitarGPS()" class="w-full bg-brand-green text-white font-bold py-3 rounded-xl hover:bg-[#12a668] transition-colors shadow-md flex items-center justify-center gap-2 mb-3">
                <i class="fas fa-location-arrow"></i> Validar Ubicación
            </button>
            <button onclick="cerrarModal()" class="w-full text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-wider py-2">
                Cancelar voto
            </button>
        </div>

        <!-- PASO 2: CARGANDO GPS (Simulación radar) -->
        <div id="paso-cargando" class="bg-white rounded-2xl p-8 w-full max-w-sm shadow-2xl hidden flex flex-col items-center justify-center fade-in">
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 bg-brand-green/20 rounded-full radar-ping"></div>
                <div class="absolute inset-0 flex items-center justify-center text-brand-green text-2xl">
                    <i class="fas fa-satellite-dish"></i>
                </div>
            </div>
            <h4 class="font-bold text-brand-blue text-lg">Validando conexión...</h4>
            <p class="text-xs text-gray-500 mt-2 text-center">Por favor, dale a "Permitir" en el aviso de tu navegador.</p>
        </div>

        <!-- PASO 3: Confirmación final -->
        <div id="paso-smartmatch" class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl hidden fade-in">
            <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 text-xl mb-4 mx-auto border border-amber-100">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h4 class="text-center font-serif text-xl font-bold text-brand-blue mb-4">Confirma tu voto</h4>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-5 text-sm text-gray-700 leading-relaxed text-center">
                Ya validamos tu ubicación. Pulsa el botón para registrar el voto seleccionado.
            </div>

            <div class="space-y-3">
                <button onclick="finalizarVoto()" class="w-full bg-brand-blue text-white font-bold py-3 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm text-sm">
                    Registrar mi voto
                </button>
                <button onclick="cerrarModal()" class="w-full bg-white border border-gray-300 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-sm">
                    Cancelar
                </button>
            </div>
        </div>

        <!-- PASO 4: ÉXITO -->
        <div id="paso-exito" class="bg-white rounded-2xl p-8 w-full max-w-sm shadow-2xl hidden flex flex-col items-center justify-center fade-in text-center">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center text-brand-green text-3xl mb-4">
                <i class="fas fa-check"></i>
            </div>
            <h4 class="font-serif text-2xl font-bold text-brand-blue mb-2">¡Voto Registrado!</h4>
            <p class="text-sm text-gray-600">
                Tu participación ha sido validada criptográficamente e ingresada a la matriz estadística.
            </p>
            <button onclick="cerrarModal()" class="mt-6 text-xs font-bold text-brand-blue uppercase tracking-wider hover:underline">
                Volver a los resultados
            </button>
        </div>

        <!-- PASO 5: ERROR -->
        <div id="paso-error" class="bg-white rounded-2xl p-8 w-full max-w-sm shadow-2xl hidden flex flex-col items-center justify-center fade-in text-center">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center text-red-600 text-3xl mb-4">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="font-serif text-2xl font-bold text-brand-blue mb-2">No pudimos registrar el voto</h4>
            <p id="paso-error-texto" class="text-sm text-gray-600 leading-relaxed">
                Ocurrió un error al intentar guardar tu voto.
            </p>
            <button onclick="cerrarModal()" class="mt-6 inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm text-sm">
                Entendido
            </button>
        </div>

    </div>
