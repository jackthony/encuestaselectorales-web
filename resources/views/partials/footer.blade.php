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
                <li><a href="{{ url('/') }}" class="text-white">Inicio</a></li>
                <li><a href="{{ url('/politica-editorial.html') }}" class="text-gray-400 hover:text-white transition-colors">Política Editorial</a></li>
                <li><a href="{{ url('/politica-privacidad.html') }}" class="text-gray-400 hover:text-white transition-colors">Privacidad</a></li>
                <li><a href="{{ url('/fuentes-correcciones.html') }}" class="text-gray-400 hover:text-white transition-colors">Fuentes y correcciones</a></li>
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
