<?php
/**
 * Result card (php-architecture spec, "Repeated UI lives in partials").
 * Source: canvas-gemini/portal_de_sondeos_ciudadanos.html's `generateCardHTML()`
 * JS template — relocated here as a PHP-renderable partial, matching the
 * two states the JS template produced 1:1 (has data / no data yet).
 *
 * sondeos.php currently keeps rendering its own cards client-side via
 * assets/js/app.js (relocated verbatim from the prototype, unchanged) so
 * this refactor makes no visual change — wiring a page to call this
 * partial server-side instead is BL-16's job ("Wiring views to data/*.json",
 * explicitly out of scope here per proposal.md). This partial exists now so
 * that hookup is a one-line `renderCardSondeo(...)` call instead of a new
 * extraction later.
 *
 * @param array{id:string,nombre:string,actualizado?:string,votos?:string,candidatos?:array} $distrito
 * @param string $delayClass optional 'delay-100' stagger class, matches the prototype's i%2 alternation
 */

require_once __DIR__ . '/../includes/helpers.php';

function renderCardSondeo(array $distrito, string $delayClass = ''): string
{
    $candidatos = $distrito['candidatos'] ?? [];
    $hasData = count($candidatos) > 0;

    if ($hasData) {
        $maxPct = (float) $candidatos[0]['pct'];
        $top3 = array_slice($candidatos, 0, 3);
        $rowsHtml = '';
        foreach ($top3 as $c) {
            $width = $maxPct > 0 ? ((float) $c['pct'] / $maxPct) * 100 : 0;
            $rowsHtml .= '
                    <div class="mb-4 last:mb-0 group cursor-default">
                        <div class="flex justify-between items-end mb-1">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-3 h-3 rounded-[3px] shrink-0 shadow-sm" style="background-color: ' . esc($c['color']) . '"></div>
                                <div class="truncate">
                                    <span class="text-[14px] font-bold text-brand-text group-hover:text-brand-blue transition-colors">' . esc($c['nombre']) . '</span>
                                    <span class="text-[10px] text-gray-400 font-bold ml-1.5 uppercase tracking-wider hidden sm:inline-block">' . esc($c['siglas']) . '</span>
                                </div>
                            </div>
                            <div class="font-extrabold text-brand-blue tabular-nums text-lg leading-none tracking-tight ml-2">' . pct($c['pct']) . '</div>
                        </div>
                        <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden relative">
                            <div class="h-full rounded-full relative group-hover:opacity-90 transition-all duration-700 ease-out" style="width: ' . esc((string) $width) . '%; background-color: ' . esc($c['color']) . '">
                                <div class="absolute inset-0 w-full h-full bg-white/30 transform -skew-x-12 -translate-x-full group-hover:animate-[shine_1.5s_ease-in-out]"></div>
                            </div>
                        </div>
                    </div>';
        }

        return '
                <article class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-7 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col scroll-animate ' . esc($delayClass) . '">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-serif font-bold text-2xl text-brand-blue leading-tight">' . esc($distrito['nombre']) . '</h3>
                        <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shadow-sm shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-green animate-pulse"></span> Estudio Activo
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-[11px] text-brand-muted mb-6 font-medium border-b border-gray-50 pb-4">
                        <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt opacity-60 text-brand-blue"></i> ' . esc($distrito['actualizado'] ?? '') . '</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-users opacity-60 text-brand-blue"></i> Muestra: ' . esc($distrito['votos'] ?? '') . '</span>
                    </div>

                    <div class="flex-grow mb-6">' . $rowsHtml . '
                    </div>

                    <div class="mt-auto pt-5 border-t border-brand-border flex justify-between items-center">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><i class="fas fa-chart-bar mr-1 opacity-50"></i> Top 3 Resultados</span>
                        <a href="encuesta.php?id=' . esc($distrito['id']) . '" class="text-[13px] font-bold text-brand-blue hover:text-brand-green flex items-center gap-1.5 group transition-colors">
                            Ver informe completo
                            <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </article>';
    }

    return '
                <article class="bg-gray-50/50 border border-dashed border-gray-300 rounded-2xl p-6 flex flex-col justify-center items-center text-center scroll-animate ' . esc($delayClass) . ' min-h-[340px]">
                    <div class="w-12 h-12 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 mb-4">
                        <i class="fas fa-satellite-dish text-xl animate-pulse"></i>
                    </div>
                    <h3 class="font-serif font-bold text-xl text-brand-blue mb-1">' . esc($distrito['nombre']) . '</h3>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Trabajo de campo en progreso</div>
                    <p class="text-xs text-gray-500 max-w-[220px] mb-6 leading-relaxed">Nuestros sistemas están recolectando y auditando la data estadística para este distrito.</p>
                    <button disabled class="px-5 py-2.5 bg-white border border-gray-200 rounded-lg text-[11px] font-bold text-gray-400 uppercase tracking-wider cursor-not-allowed shadow-sm">
                        Resultados pendientes
                    </button>
                </article>';
}
