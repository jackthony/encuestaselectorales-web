@php
    $shareTitle = $shareTitle ?? $pageTitle ?? 'EncuestasElectorales.pe';
    $shareDescription = $shareDescription ?? $pageDescription ?? '';
    $shareUrl = $shareUrl ?? url()->current();
    $shareImage = $shareImage ?? null;
    $hasShareImage = is_string($shareImage) && $shareImage !== '';
    $shareImageUrl = $hasShareImage
        ? (preg_match('/^https?:\/\//i', (string) $shareImage) ? $shareImage : asset($shareImage))
        : null;
    $shareText = trim($shareTitle . ' ' . $shareUrl);
    $buttonGridClass = $hasShareImage ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-2 md:grid-cols-3';
@endphp

<section class="bg-brand-card border border-brand-border rounded-2xl p-5 md:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="min-w-0">
            <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Compartir</div>
            <h3 class="text-xl font-serif font-bold text-brand-blue leading-tight mb-2">{{ $shareTitle }}</h3>
            @if ($shareDescription !== '')
                <p class="text-sm text-brand-muted leading-relaxed max-w-2xl">{{ $shareDescription }}</p>
            @endif
        </div>

        @if ($hasShareImage && $shareImageUrl)
            <div class="shrink-0 w-full max-w-[180px]">
                <a href="{{ $shareImageUrl }}" target="_blank" rel="noopener" class="block">
                    <img src="{{ $shareImageUrl }}" alt="{{ $shareTitle }}" class="w-full aspect-[4/5] object-cover rounded-2xl border border-brand-border shadow-sm">
                </a>
            </div>
        @endif
    </div>

    <div class="grid {{ $buttonGridClass }} gap-3 mt-5">
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 hover:text-brand-green transition-colors">
            <i class="fab fa-facebook-f"></i> Facebook
        </a>
        <a href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 hover:text-brand-green transition-colors">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <button
            type="button"
            data-copy-share
            data-copy-text="{{ $shareText }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 hover:text-brand-green transition-colors"
        >
            <i class="fas fa-link"></i> Copiar enlace
        </button>
        @if ($hasShareImage && $shareImageUrl)
            <a href="{{ $shareImageUrl }}" download class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 hover:text-brand-green transition-colors">
                <i class="fas fa-image"></i> Historia
            </a>
        @endif
    </div>
</section>
