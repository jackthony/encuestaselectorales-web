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

        <img class="og-logo" src="{{ asset('assets/miniatura-compartir/brand-logo-horizontal.png') }}" alt="">
        <div class="og-divider"></div>

        <div class="og-eyebrow">{{ $data['eyebrow'] }}</div>
        <div class="og-title">{{ $data['title'] }}</div>
        <div class="og-subtitle">{{ $data['subtitle'] }}</div>

        <div class="og-panel">
            <div class="og-col-header og-col-header--results">RESULTADOS</div>
            <div class="og-col-header og-col-header--pct">PORCENTAJE</div>
            <div class="og-col-header og-col-header--votes">VOTOS</div>

            @foreach ($data['results'] as $index => $row)
                @php $isFirst = $row['position'] === 1; @endphp
                <div class="og-row" style="top: {{ 44 + $index * 65 }}px;">
                    <div class="og-rank {{ $isFirst ? 'is-first' : '' }}">{{ $row['position'] }}</div>
                    <div class="og-candidate-name">{{ $row['candidate_name'] }}</div>
                    <div class="og-candidate-party {{ $isFirst ? 'is-first' : '' }}">{{ $row['party_name'] }}</div>
                    <div class="og-bar-track">
                        <div class="og-bar-fill {{ $isFirst ? 'is-first' : '' }}" style="width: {{ $row['bar_width'] }}px;"></div>
                    </div>
                    <div class="og-pct {{ $isFirst ? 'is-first' : '' }}">{{ $row['percentage'] }}</div>
                    <div class="og-votes-box {{ $isFirst ? 'is-first' : '' }}">
                        <div class="og-votes-text">{{ $row['votes'] }}</div>
                    </div>
                </div>
                @if (!$loop->last)
                    <div class="og-row-sep" style="top: {{ 44 + ($index + 1) * 65 }}px;"></div>
                @endif
            @endforeach

            <div class="og-footer">
                <svg class="og-footer-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="#102F86"/>
                </svg>
                <div class="og-footer-text">{{ $data['footer_text'] }}</div>
            </div>
        </div>

        <img class="og-domain" src="{{ asset('assets/miniatura-compartir/brand-domain-lockup.png') }}" alt="">
    </div>
</body>
</html>
