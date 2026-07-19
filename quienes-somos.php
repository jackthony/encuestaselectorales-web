<?php
/**
 * quienes-somos.php — about / authority page. Source:
 * canvas-gemini/qui_nes_somos_autoridad.html, relocated verbatim. No
 * legal scrub applies — no third-party pollster or out-of-scope
 * territory is mentioned in this prototype.
 */

require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Quiénes Somos | EncuestasElectorales.pe';
$pageDescription = 'Conoce al equipo multidisciplinario de científicos de datos y analistas detrás de la principal plataforma de inteligencia electoral de Lima.';
$activeNav = 'quienes-somos';
?><!doctype html>
<html lang="es" class="scroll-smooth">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <div class="bg-brand-green text-[#062010] text-[11px] md:text-xs font-bold py-2 px-4 w-full relative z-50 shadow-sm border-b border-white/20">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2 tracking-wide uppercase">
                <span class="relative flex h-2 w-2 mr-1">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#062010] opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-[#062010]"></span>
                </span>
                Inteligencia Electoral y Ciencia de Datos
            </div>
            <div id="reloj" class="font-mono tracking-wide hidden md:block" aria-live="polite">
                --/--/---- --:--:--
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="flex-grow w-full">
        <!-- Hero Minimalista -->
        <section class="bg-brand-surface border-b border-brand-border py-20 md:py-28 px-4 text-center">
            <div class="max-w-3xl mx-auto scroll-animate">
                <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-blue mb-6 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-full shadow-sm">
                    <i class="fas fa-landmark"></i> Plataforma Cívica
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-brand-blue leading-[1.1] mb-6">
                    Tecnología para la transparencia electoral
                </h1>
                <p class="text-lg md:text-xl text-brand-muted leading-relaxed">
                    Somos una iniciativa independiente enfocada en democratizar el acceso a la inteligencia electoral. Datos rigurosos, en tiempo real y sin agendas ocultas.
                </p>
            </div>
        </section>

        <!-- Pilares Ultra Simplificados -->
        <section class="max-w-7xl mx-auto px-4 py-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-brand-card border border-brand-border p-8 rounded-2xl shadow-sm text-center scroll-animate">
                    <div class="w-14 h-14 bg-blue-50 text-brand-blue rounded-full flex items-center justify-center text-2xl mx-auto mb-5 border border-blue-100">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3 class="text-xl font-bold text-brand-text mb-3">Independencia</h3>
                    <p class="text-brand-muted text-sm leading-relaxed">Financiamiento 100% privado. Cero vínculos estatales o partidarios. Nuestra lealtad es exclusiva con los datos.</p>
                </div>

                <div class="bg-brand-card border border-brand-border p-8 rounded-2xl shadow-sm text-center scroll-animate delay-100">
                    <div class="w-14 h-14 bg-green-50 text-brand-green rounded-full flex items-center justify-center text-2xl mx-auto mb-5 border border-green-100">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-brand-text mb-3">Rigor y Seguridad</h3>
                    <p class="text-brand-muted text-sm leading-relaxed">Infraestructura blindada. Cruzamos huellas de red y geolocalización (GPS) para garantizar muestras libres de fraude.</p>
                </div>

                <div class="bg-brand-card border border-brand-border p-8 rounded-2xl shadow-sm text-center scroll-animate delay-200">
                    <div class="w-14 h-14 bg-gray-50 text-brand-text rounded-full flex items-center justify-center text-2xl mx-auto mb-5 border border-gray-200">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="text-xl font-bold text-brand-text mb-3">Transparencia</h3>
                    <p class="text-brand-muted text-sm leading-relaxed">Sin cajas negras. Metodología abierta, muestra en tiempo real y trazabilidad total de nuestros resultados.</p>
                </div>
            </div>
        </section>

        <!-- Contacto Directo -->
        <section class="bg-brand-blue text-white py-20 px-4">
            <div class="max-w-4xl mx-auto text-center scroll-animate">
                <h2 class="text-3xl font-serif font-bold mb-10">Canales Oficiales</h2>
                <div class="flex flex-col sm:flex-row justify-center gap-6">
                    <a href="mailto:prensa@encuestaselectorales.pe" class="flex items-center justify-center gap-4 bg-white/10 hover:bg-white/20 border border-white/20 px-8 py-5 rounded-xl transition-colors shadow-sm">
                        <i class="far fa-newspaper text-2xl text-brand-green"></i>
                        <div class="text-left">
                            <div class="text-[10px] font-bold text-blue-200 uppercase tracking-widest mb-0.5">Prensa y Medios</div>
                            <div class="font-semibold tracking-wide">prensa@encuestaselectorales.pe</div>
                        </div>
                    </a>
                    <a href="#" class="flex items-center justify-center gap-4 bg-white/10 hover:bg-white/20 border border-white/20 px-8 py-5 rounded-xl transition-colors shadow-sm">
                        <i class="fab fa-whatsapp text-2xl text-brand-green"></i>
                        <div class="text-left">
                            <div class="text-[10px] font-bold text-blue-200 uppercase tracking-widest mb-0.5">Contacto Directo</div>
                            <div class="font-semibold tracking-wide">+51 971 388 435</div>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
