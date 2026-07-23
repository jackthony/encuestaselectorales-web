<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>OG Results Preview — Dev</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>{!! file_get_contents(resource_path('css/og-results-preview.css')) !!}</style>
</head>
<body>
    <div class="og-canvas" id="og-canvas">
        <img class="og-bg" src="{{ asset('assets/miniatura-compartir/og-results-background-1200x630.png') }}" alt="">

        <img class="og-logo" src="{{ asset('assets/miniatura-compartir/brand-logo-horizontal-cleaned.png') }}" alt="">
        <div class="og-divider"></div>

        <div class="og-eyebrow">{{ $data['eyebrow'] }}</div>
        <div class="og-title" id="og-title">{{ $data['title'] }}</div>
        <div class="og-subtitle">{{ $data['subtitle'] }}</div>

        @php
            // Banda disponible para filas dentro del panel (coords relativas al panel):
            // empieza a 44px (bajo el header RESULTADOS/PORCENTAJE/VOTOS) y termina a
            // 369px (arriba del footer). Se reparte por igual entre N candidatos, así
            // que con menos de 5 el diseño no deja un hueco vacío abajo.
            $rowsAreaTop = 44;
            $rowsAreaHeight = 325;
            $rowCount = max(count($data['results']), 1);
            $rowPitch = $rowsAreaHeight / $rowCount;
        @endphp

        <div class="og-panel">
            <div class="og-col-header og-col-header--results">RESULTADOS</div>
            <div class="og-col-header og-col-header--pct">PORCENTAJE</div>
            <div class="og-col-header og-col-header--votes">VOTOS</div>

            @foreach ($data['results'] as $index => $row)
                @php
                    $isFirst = $row['position'] === 1;
                    $barWidth = round((float) $row['percentage'] * 258 / 100);
                @endphp
                <div class="og-row" style="top: {{ $rowsAreaTop + $index * $rowPitch }}px; height: {{ $rowPitch }}px;">
                    <div class="og-rank {{ $isFirst ? 'is-first' : '' }}">{{ $row['position'] }}</div>
                    <div class="og-candidate-block">
                        <div class="og-candidate-name">{{ $row['candidate_name'] }}</div>
                        <div class="og-candidate-party {{ $isFirst ? 'is-first' : '' }}">{{ $row['party_name'] }}</div>
                    </div>
                    <div class="og-bar-track">
                        <div class="og-bar-fill {{ $isFirst ? 'is-first' : '' }}" style="width: {{ $barWidth }}px;"></div>
                    </div>
                    <div class="og-pct {{ $isFirst ? 'is-first' : '' }}">{{ $row['percentage'] }}</div>
                    <div class="og-votes-box {{ $isFirst ? 'is-first' : '' }}">
                        <div class="og-votes-text">{{ $row['votes'] }}</div>
                    </div>
                </div>
                @if (!$loop->last)
                    <div class="og-row-sep" style="top: {{ $rowsAreaTop + ($index + 1) * $rowPitch }}px;"></div>
                @endif
            @endforeach

            <div class="og-footer">
                <svg class="og-footer-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.5 11a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5Zm7 0a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5Zm-7 1.5c-2.33 0-7 1.17-7 3.5v1.5h9v-1.5c0-1.06.55-1.93 1.32-2.62A11.6 11.6 0 0 0 8.5 12.5Zm7 0c-.6 0-1.3.07-2.02.2.86.72 1.52 1.68 1.52 2.8v1.5h7.5v-1.5c0-2.33-4.67-3.5-7-3.5Z" fill="#102F86"/>
                </svg>
                <div class="og-footer-text">{{ $data['footer_text'] }}</div>
            </div>
        </div>

        <div class="og-domain">
            <img src="{{ asset('assets/miniatura-compartir/brand-domain-lockup-cleaned.png') }}" alt="">
        </div>
    </div>
    <script>
        // Distritos con nombre largo: encoge el título hasta que quepa en una
        // línea dentro del contenedor (805px), sin truncar el texto.
        document.fonts.ready.then(function () {
            var el = document.getElementById('og-title');
            if (!el) return;
            var maxWidth = 805;
            var minFontSize = 32;
            var fontSize = parseFloat(getComputedStyle(el).fontSize);
            while (el.scrollWidth > maxWidth && fontSize > minFontSize) {
                fontSize -= 1;
                el.style.fontSize = fontSize + 'px';
                el.style.lineHeight = (fontSize + 2) + 'px';
            }
        });
    </script>
</body>
</html>
