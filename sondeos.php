<?php
/**
 * sondeos.php — citizen-sondeo feed (opt-in web poll), by district.
 * Source: canvas-gemini/portal_de_sondeos_ciudadanos.html, relocated
 * verbatim. This prototype already follows PROMPT-portal.md's "no
 * third-party pollster" rule, so no legal scrub applies here.
 */

require_once __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/data.php'; // VOTACION_EN_VIVO — see bl-11-responsive-wcag design.md

$pageTitle = 'EncuestasElectorales.pe - Sondeo en vivo';
$activeNav = 'inicio';

$whatsappNumero = '51971388435';
?><!doctype html>
<html lang="es" class="scroll-smooth">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <div class="bg-brand-green text-[#062010] text-[11px] md:text-xs font-bold py-2 px-4 w-full relative z-50 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2 tracking-wide uppercase">
                <span class="relative flex h-2 w-2 mr-1">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#062010] opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-[#062010]"></span>
                </span>
                Sondeo ciudadano en vivo · Elecciones 2026
            </div>
            <div id="reloj" class="font-mono tracking-wide hidden md:block" aria-live="polite">
                --/--/---- --:--:--
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="flex-grow w-full bg-brand-bg">

        <!-- Hero Section Animado -->
        <section class="relative bg-brand-blue text-white overflow-hidden border-b border-brand-blue/80">
            <!-- Capa de fondo cuadriculada animada -->
            <div class="absolute inset-0 bg-grid-pattern opacity-30"></div>
            <!-- Gradiente para suavizar bordes -->
            <div class="absolute inset-0 bg-gradient-to-t from-brand-blue to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-4 py-20 md:py-28 flex flex-col items-center text-center">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold leading-tight mb-6 tracking-tight max-w-4xl scroll-animate">
                    ¿Quién va ganando en <br class="hidden md:block"/>
                    <span class="word-slider text-brand-green">
                        <span>Miraflores?</span>
                        <span>San Isidro?</span>
                        <span>Surco?</span>
                        <span>La Victoria?</span>
                        <span>tu distrito?</span>
                    </span>
                </h1>
                <p class="text-lg md:text-xl text-blue-100 max-w-2xl font-medium mb-10 leading-relaxed scroll-animate delay-100">
                    El portal cívico de inteligencia electoral. Monitorea los resultados en vivo y compara distrito por distrito.
                </p>

                <div class="flex gap-4 scroll-animate delay-100">
                    <button class="bg-brand-green hover:bg-[#12a668] text-white font-bold py-3 px-8 rounded-full shadow-lg transition-transform hover:-translate-y-0.5">
                        Ver resultados
                    </button>
                    <button class="bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 text-white font-bold py-3 px-8 rounded-full transition-colors">
                        Metodología
                    </button>
                </div>
            </div>
        </section>

        <!-- Contenido Principal -->
        <section class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

            <!-- Feed Izquierdo: Tarjetas de Sondeos -->
            <div class="lg:col-span-8">
                <div class="flex justify-between items-baseline mb-8 border-b border-brand-border pb-4">
                    <h2 class="text-3xl font-serif font-bold text-brand-blue">Sondeos Activos</h2>
                    <span class="text-sm font-bold text-brand-muted uppercase tracking-wider">43 Distritos</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="feed-container">
                    <!-- Las tarjetas se inyectarán vía JavaScript -->
                </div>
            </div>

            <!-- Sidebar Derecho: Votación Interactiva.
                 Gated on VOTACION_EN_VIVO (bl-11-responsive-wcag design.md) — no
                 online round has ever opened, so this no longer hardcodes a
                 specific district's real candidates into a form that submits
                 nowhere (bl-11c-purge-datos-ficticios). -->
            <aside class="lg:col-span-4">
<?php if (VOTACION_EN_VIVO): ?>
                <div class="sticky top-28 bg-brand-card border border-brand-border rounded-2xl p-6 shadow-soft scroll-animate">
                    <!-- Vote form renders here once a real online_propia round is open. -->
                </div>
<?php else: ?>
                <div class="sticky top-28 bg-brand-card border border-brand-border rounded-2xl p-6 shadow-soft scroll-animate text-center">
                    <div class="w-12 h-12 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-xl mx-auto mb-4">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="font-serif font-bold text-lg text-brand-blue mb-2">Aún no hay un sondeo en línea abierto</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-5">Elegí tu distrito en el buscador para ver sus candidatos, o proponé uno si aún no está en la lista.</p>
                    <a href="https://wa.me/<?= esc($whatsappNumero) ?>?text=<?= rawurlencode('Hola, quiero proponer un candidato') ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-3 px-5 rounded-xl hover:bg-[#20bd5a] transition-colors w-full shadow-sm text-sm">
                        <i class="fab fa-whatsapp text-lg"></i> Proponer candidato por WhatsApp
                    </a>
                </div>
<?php endif; ?>
            </aside>
        </section>
    </main>

    <!-- Botón Flotante WhatsApp -->
    <a href="#" class="fixed bottom-6 right-6 w-14 h-14 bg-[#25D366] text-white rounded-full flex items-center justify-center text-3xl shadow-lg hover:scale-110 transition-transform z-50" aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script>
        // Utilidad de escape XSS
        const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]);

        // District sample list — no `candidatos`/`pct` fields on any entry:
        // no district has a real, live online poll result yet (BL-13b/BL-14
        // not shipped), so every card falls through to generateCardHTML()'s
        // real "trabajo de campo en progreso" state below, not a fabricated
        // percentage (bl-11c-purge-datos-ficticios).
        const mockData = [
            { id: "miraflores", nombre: "Miraflores" },
            { id: "surco", nombre: "Santiago de Surco" },
            { id: "san-isidro", nombre: "San Isidro" },
            { id: "barranco", nombre: "Barranco" }
        ];

        // Función generadora de tarjetas (Mejorada para Claude Code)
        function generateCardHTML(data, delayClass = '') {
            const hasData = data.candidatos && data.candidatos.length > 0;

            if(hasData) {
                /* =====================================================================
                   🤖 INSTRUCCIONES PARA CLAUDE CODE (BACKEND INTEGRATION)
                   =====================================================================
                   Claude: La barra de progreso no se escala sobre 100% absoluto.
                   Se debe calcular `maxPct` (el candidato líder) y escalar el width
                   de todos en relación a él (c.pct / maxPct) * 100.
                   Esto garantiza contraste visual en el dashboard.
                   El span numérico ya tiene 'tabular-nums' para evitar saltos.
                   ===================================================================== */
                const maxPct = data.candidatos[0].pct;
                const top3 = data.candidatos.slice(0, 3).map(c => {
                    const width = (c.pct / maxPct) * 100;
                    return `
                    <div class="mb-4 last:mb-0 group cursor-default">
                        <div class="flex justify-between items-end mb-1">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-3 h-3 rounded-[3px] shrink-0 shadow-sm" style="background-color: ${c.color}"></div>
                                <div class="truncate">
                                    <span class="text-[14px] font-bold text-brand-text group-hover:text-brand-blue transition-colors">${esc(c.nombre)}</span>
                                    <span class="text-[10px] text-gray-400 font-bold ml-1.5 uppercase tracking-wider hidden sm:inline-block">${esc(c.siglas)}</span>
                                </div>
                            </div>
                            <div class="font-extrabold text-brand-blue tabular-nums text-lg leading-none tracking-tight ml-2">${c.pct.toFixed(1)}%</div>
                        </div>
                        <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden relative">
                            <div class="h-full rounded-full relative group-hover:opacity-90 transition-all duration-700 ease-out" style="width: ${width}%; background-color: ${c.color}">
                                <!-- Efecto Brillo animado en Hover -->
                                <div class="absolute inset-0 w-full h-full bg-white/30 transform -skew-x-12 -translate-x-full group-hover:animate-[shine_1.5s_ease-in-out]"></div>
                            </div>
                        </div>
                    </div>`;
                }).join('');

                return `
                <article class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-7 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col scroll-animate ${delayClass}">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-serif font-bold text-2xl text-brand-blue leading-tight">${esc(data.nombre)}</h3>
                        <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shadow-sm shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-green animate-pulse"></span> Estudio Activo
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-[11px] text-brand-muted mb-6 font-medium border-b border-gray-50 pb-4">
                        <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt opacity-60 text-brand-blue"></i> ${esc(data.actualizado)}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-users opacity-60 text-brand-blue"></i> Muestra: ${esc(data.votos)}</span>
                    </div>

                    <div class="flex-grow mb-6">
                        ${top3}
                    </div>

                    <div class="mt-auto pt-5 border-t border-brand-border flex justify-between items-center">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><i class="fas fa-chart-bar mr-1 opacity-50"></i> Top 3 Resultados</span>
                        <a href="encuesta.php" class="text-[13px] font-bold text-brand-blue hover:text-brand-green flex items-center gap-1.5 group transition-colors">
                            Ver informe completo
                            <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </article>`;
            } else {
                return `
                <article class="bg-gray-50/50 border border-dashed border-gray-300 rounded-2xl p-6 flex flex-col justify-center items-center text-center scroll-animate ${delayClass} min-h-[340px]">
                    <div class="w-12 h-12 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 mb-4">
                        <i class="fas fa-satellite-dish text-xl animate-pulse"></i>
                    </div>
                    <h3 class="font-serif font-bold text-xl text-brand-blue mb-1">${esc(data.nombre)}</h3>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Trabajo de campo en progreso</div>
                    <p class="text-xs text-gray-500 max-w-[220px] mb-6 leading-relaxed">Nuestros sistemas están recolectando y auditando la data estadística para este distrito.</p>
                    <button disabled class="px-5 py-2.5 bg-white border border-gray-200 rounded-lg text-[11px] font-bold text-gray-400 uppercase tracking-wider cursor-not-allowed shadow-sm">
                        Resultados pendientes
                    </button>
                </article>`;
            }
        }

        // Renderizar tarjetas
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('feed-container');
            if(container) {
                container.innerHTML = mockData.map((d, i) => generateCardHTML(d, i % 2 === 0 ? '' : 'delay-100')).join('');
            }
        });
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
