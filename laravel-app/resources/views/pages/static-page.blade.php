@extends('layouts.public')

@section('content')
    @if (!empty($tickerText))
        <div class="bg-brand-green text-[#062010] text-[11px] md:text-xs font-bold py-2 px-4 w-full relative z-50 shadow-sm border-b border-white/20">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2 tracking-wide uppercase">
                    <span class="relative flex h-2 w-2 mr-1">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#062010] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#062010]"></span>
                    </span>
                    {{ $tickerText }}
                </div>
                @if (!empty($tickerSecondary))
                    <div class="font-mono tracking-wide hidden md:block" aria-live="polite">{{ $tickerSecondary }}</div>
                @endif
            </div>
        </div>
    @endif

    <main class="flex-grow w-full">
        <section class="bg-brand-surface border-b border-brand-border py-20 md:py-28 px-4 text-center">
            <div class="max-w-3xl mx-auto scroll-animate">
                @if (!empty($heroBadge))
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-blue mb-6 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-full shadow-sm">
                        <i class="fas fa-bolt"></i> {{ $heroBadge }}
                    </div>
                @endif
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-brand-blue leading-[1.1] mb-6">
                    {{ $heroTitle }}
                </h1>
                <p class="text-lg md:text-xl text-brand-muted leading-relaxed">
                    {{ $heroLead }}
                </p>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            @if (!empty($sidebarLinks))
                <aside class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-28 bg-brand-card border border-brand-border rounded-xl p-6 shadow-sm scroll-animate">
                        <h3 class="text-xs font-bold text-brand-muted uppercase tracking-widest mb-5">{{ $sidebarTitle ?? 'En esta página' }}</h3>
                        <nav class="flex flex-col gap-4 text-sm font-semibold">
                            @foreach ($sidebarLinks as $link)
                                <a href="{{ $link['href'] }}" class="text-brand-text hover:text-brand-green transition-colors flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span> {{ $link['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>
            @endif

            <div class="{{ !empty($sidebarLinks) ? 'lg:col-span-8 lg:col-start-4' : 'lg:col-span-10 lg:col-start-2' }} prose-editorial scroll-animate delay-100">
                @if (!empty($intro))
                    <p class="text-xl text-brand-blue font-serif font-bold leading-relaxed mb-10">
                        {{ $intro }}
                    </p>
                @endif

                @foreach ($sections as $section)
                    <h2 id="{{ $section['id'] }}">{{ $section['title'] }}</h2>

                    @foreach (($section['paragraphs'] ?? []) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach

                    @if (!empty($section['bullets']))
                        <ul>
                            @foreach ($section['bullets'] as $bullet)
                                <li>{{ $bullet }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if (!empty($section['cards']))
                        <div class="grid md:grid-cols-2 gap-6 my-8">
                            @foreach ($section['cards'] as $card)
                                <div class="bg-brand-surface p-6 border border-brand-border rounded-xl">
                                    @if (!empty($card['icon']))
                                        <div class="text-brand-green text-2xl mb-3"><i class="{{ $card['icon'] }}"></i></div>
                                    @endif
                                    <h4 class="font-bold text-brand-text text-lg mb-2">{{ $card['title'] }}</h4>
                                    <p class="text-sm text-brand-muted m-0 leading-relaxed">{{ $card['body'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($section['quote']))
                        <blockquote>{{ $section['quote'] }}</blockquote>
                    @endif
                @endforeach

                @if (!empty($cta))
                    <hr class="border-brand-border my-12">

                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                        <h3 class="font-serif text-2xl font-bold text-brand-blue mb-4 !mt-0">{{ $cta['title'] }}</h3>
                        <p class="text-brand-muted mb-6 max-w-lg mx-auto">{{ $cta['body'] }}</p>
                        <a href="{{ $cta['href'] }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-lg text-white bg-brand-blue hover:bg-[#0c2466] shadow-sm transition-transform hover:-translate-y-0.5">
                            <i class="far fa-envelope mr-2"></i> {{ $cta['label'] }}
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection
