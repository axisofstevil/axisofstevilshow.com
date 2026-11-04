{{-- The show's running head, as the archive's: one half-module tall, the lockup home, then the breadcrumb (the series
     on every page; on a call, its quarter, whose chevron opens every quarter with calls out), then the archive and
     Subscribe, the one action. The page's own name is its heading, never a crumb. --}}
@php
    $path = $page->getPath();
    $anchor = fn (int $season) => 'q'.((($season - 1) % 4) + 1).'-'.(2005 + intdiv($season - 1, 4));
    $quarters = collect($page->quarters ? $page->quarters->toArray() : [])->sortBy('season');
    $season = str_starts_with($path, '/earnings-calls/episodes/') && $page->season ? (int) $page->season : null;
@endphp
<header class="plate running-head">
    <a class="running-lockup" href="/" aria-label="The Axis of Stevil Show, home">@include('_partials.lockup')</a>
    <nav class="running-crumbs" aria-label="Breadcrumb">
        <div class="crumb">
            <p class="running-title"><a href="/earnings-calls/" @if ($path === '/earnings-calls') aria-current="page" @endif>{{ $page->series->name }}</a></p>
        </div>
        @if ($season)
            <div class="crumb">
                <p class="running-title"><a href="/earnings-calls/#{{ $anchor($season) }}">{{ $page->season_name }}</a></p>
                @if ($quarters->count() > 0)
                    <details class="switcher">
                        <summary aria-label="Choose another quarter"><svg class="switcher-chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.75"/></svg></summary>
                        <ul class="switcher-menu">
                            @foreach ($quarters as $quarter)
                                <li><a href="/earnings-calls/#{{ $anchor($quarter['season']) }}" @if ($quarter['season'] === $season) aria-current="true" @endif>{{ $quarter['name'] }}<span>{{ count($quarter['episodes']) }}</span></a></li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        @endif
    </nav>
    <nav class="running-nav" aria-label="The show">
        <a class="running-archive" href="{{ $page->archiveUrl }}/">axisofstevil.com @include('_partials.arrow')</a>
        <a class="running-subscribe" href="/earnings-calls/subscribe/" @if ($path === '/earnings-calls/subscribe') aria-current="page" @endif>Subscribe</a>
    </nav>
</header>
