<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            // Sections arrive escaped; decoded here so the head escapes them once.
            $section = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5);
            $url = $page->baseUrl.$page->link();
            // The head follows the featured series, and always names the show: the home page's title
            // is the show, then the series; every other page sets its own title, ending in the show's name. A page without
            // a description of its own (an episode has its notes) takes the series', with the show's name before it
            // when the series' text doesn't already carry it. The image is the page's own, else the series' cover.
            $home = trim((string) $page->getPath(), '/') === '';
            $title = $section('title') ?: ($home ? $page->siteName.': '.$page->series->name : $page->title);
            $description = $section('description');
            if ($description === '') {
                $description = (string) $page->show->description;
                if (stripos($description, (string) $page->siteName) === false) {
                    $description = $page->siteName.': '.$description;
                }
            }
            $card = $section('card') ?: $page->cover;
            $structured = \App\Services\StructuredData::for($page, $title, $description);
        @endphp
        <title>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        @if ($page->noindex || $page->preview)
        <meta name="robots" content="noindex">
        @else
        <link rel="canonical" href="{{ $url }}">
        @endif
        <meta property="og:site_name" content="{{ $page->siteName }}">
        <meta name="application-name" content="{{ $page->siteName }}">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:url" content="{{ $url }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:image" content="{{ $card }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $card }}">
        <link rel="alternate" type="application/rss+xml" title="{{ $page->series->title }}" href="{{ $page->baseUrl }}/earnings-calls/feed.xml">
        <link rel="preload" href="/assets/fonts/jost.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/assets/fonts/source-serif-4.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="stylesheet" href="{{ $page->assetCss }}" data-turbo-track="reload">
        <script>document.documentElement.classList.add('js')</script>
        <meta name="theme-color" content="#3f4a54">
        {{-- Turbo: a link fetches the next page and swaps the body, so the call (kept across the swap) plays on. The stylesheet
             and script are tracked: named for their contents, they change only with the site's code, and then the next
             visit loads in full, so nobody runs yesterday's script (the daily content builds keep their names). --}}
        <script src="/assets/turbo.8.0.13.js" defer></script>
        <script src="{{ $page->assetJs }}" defer data-turbo-track="reload"></script>
        <link rel="icon" href="/favicon.ico" sizes="48x48 32x32 16x16">
        <link rel="icon" href="https://archive.axisofstevil.com/brand/axis-symbol.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/assets/logo/apple-touch-icon.png">
    </head>
    <body class="@yield('body_class')">
        <a class="skip" href="#main">Skip to the call</a>
        <main id="main" class="report">
            {{-- The home page has none, as the archive's: its cover carries the lockup. --}}
            @sectionMissing('no_running_head')
                @include('_partials.running-head')
            @endif
            @yield('body')
            @include('_partials.colophon')
        </main>
        {{-- The grid set and snapped before the first paint, so nothing moves when the script arrives. --}}
        <script>{!! $page->snapInline !!}</script>
        {{-- In the body, which Turbo swaps whole, so a visit never keeps the last page's data. --}}
        @if ($structured !== [])
        <script type="application/ld+json">{!! json_encode($structured, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endif
        @include('_partials.dock')
    </body>
</html>
