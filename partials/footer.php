<?php
/**
 * Single shared footer (php-architecture spec, "Repeated UI lives in partials").
 *
 * Finding (tasks.md section 3.2): like the header, the footer has no single
 * canonical version across the 8 prototypes — even within "cluster B"
 * (portal_de_sondeos_ciudadanos, metodolog_a, qui_nes_somos_autoridad) the
 * three footers differ (different nav links, one drops the copyright line,
 * one swaps "Contacto" for "Soporte"). Per "newest prototype wins", this
 * partial uses portal_de_sondeos_ciudadanos.html's footer verbatim (same
 * source as header.php, for internal consistency) — cluster A pages
 * (index.php, encuesta.php, candidato.php, encuestadoras.php) pick up this
 * footer instead of their own bg-white/bg-brand-blue/bg-gray-900 variants,
 * same recorded, intentional consequence as the header.
 */
require_once __DIR__ . '/../includes/helpers.php';
?>
    <footer class="bg-[#0b1221] text-gray-300 py-12 mt-auto border-t-[5px] border-brand-green">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-12 gap-10">
            <div class="md:col-span-5">
                <div class="text-xl font-extrabold tracking-tight leading-none mb-4 text-white">
                    <span class="text-white">Encuestas</span>electorales<span class="text-brand-green">.pe</span>
                </div>
                <p class="text-sm text-gray-400 leading-relaxed mb-4 max-w-sm">
                    Plataforma independiente de inteligencia electoral y medición ciudadana para Lima Metropolitana.
                </p>
            </div>

            <div class="md:col-span-3 md:col-start-7">
                <h4 class="text-white font-bold uppercase tracking-wider text-xs mb-4">Navegación</h4>
                <ul class="flex flex-col gap-2 text-sm font-medium">
                    <li><a href="index.php" class="text-white">Inicio</a></li>
                    <li><a href="metodologia.php" class="text-gray-400 hover:text-white transition-colors">Metodología</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Política Editorial</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacidad</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h4 class="text-white font-bold uppercase tracking-wider text-xs mb-4">Contacto</h4>
                <ul class="flex flex-col gap-2 text-sm text-gray-400">
                    <li><i class="far fa-envelope mr-2 text-brand-green"></i> contacto@encuestaselectorales.pe</li>
                    <li><i class="fab fa-whatsapp mr-2 text-brand-green"></i> +51 971 388 435</li>
                </ul>
                <div class="mt-6 text-xs text-gray-500 font-medium">
                    © 2026 Todos los derechos reservados.
                </div>
            </div>
        </div>
    </footer>
