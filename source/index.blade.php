@extends('_layouts.master')

@section('body_class', 'is-home')
@section('no_running_head', true)

@section('body')
    @php
        $length = fn ($s) => sprintf('%d:%02d', intdiv((int) $s, 60), (int) $s % 60);
        $most = max(1, ...array_map(fn ($q) => $q['released'] + $q['scheduled'], $page->chart->toArray() ?: [['released' => 1, 'scheduled' => 0]]));
        $latest = $page->latest;
    @endphp
    <section class="fold-top home-top" data-snap data-fill>
        <div class="plate cover call home-cover">
            {{-- The run's chart: calls per quarter, Q1 '05 to Q2 '07; released solid, scheduled in outline. --}}
            <div class="cover-chart" aria-hidden="true">
                @foreach ($page->chart as $quarter)
                    <span class="cover-bar"><i style="--h: {{ round($quarter['released'] / $most * 100) }}%"></i><b style="--h: {{ round(($quarter['released'] + $quarter['scheduled']) / $most * 100) }}%"></b></span>
                @endforeach
            </div>
            <p class="cover-title home-title">Earnings<br>Calls</p>
            <div class="cover-band">
                {{-- The page's heading is the show's name, in its lockup (the image's text): what the home page is. --}}
                <h1 class="cover-lockup">@include('_partials.lockup')</h1>
                <p class="cover-imprint"><span>Q1 '05 – Q2 '07</span><span class="caption">Plate 1 · The cover</span></p>
            </div>
        </div>
        <div class="plate letter">
            {{-- The featured series' letter, from its channel (its page at letter): its title the heading, its last paragraph
                 the signature. The site's own copy only without it. --}}
            @php $letter = $page->letter ? json_decode(json_encode($page->letter), true) : null; @endphp
            @if ($letter)
                <h2 class="letter-title">{{ $letter['title'] }}</h2>
                <div class="letter-body">
                    {!! $letter['content'] !!}
                    @if ($letter['signed'])<p class="signed">{{ $letter['signed'] }}</p>@endif
                </div>
            @else
                <h2 class="letter-title">To our listeners</h2>
                <div class="letter-body">
                    <p>The Axis of Stevil is reporting its results twenty years late. Each call covers one quarter of the archive, from 2005 to 2007, as read by its Chief Financial Officer: publications, the words, the questions, and the Steves. The exhibits are in your packet.</p>
                    <p>Stevil is on <a href="/earnings-calls/">every call</a>. He has not spoken.</p>
                    <p>A new call goes out every Monday at 6:00 AM Eastern. Management thanks you for holding.</p>
                    <p class="signed">Juan Conroy, CFO</p>
                </div>
            @endif
            <dl class="figures">
                <div><dt>Calls released</dt><dd><a href="/earnings-calls/" aria-label="{{ $page->figures['calls'] ?? 0 }} calls released: the calls">{{ $page->figures['calls'] ?? 0 }}</a></dd></div>
                <div><dt>Quarters reported</dt><dd>{{ $page->figures['quarters'] ?? 0 }}</dd></div>
                <div><dt>Minutes on record</dt><dd>{{ $page->figures['minutes'] ?? 0 }}</dd></div>
            </dl>
            <p class="caption">Plate 2 · Letter to listeners</p>
        </div>
    </section>

    <div class="home-calls" data-snap>
        @if ($latest)
            <section class="plate current-call" aria-labelledby="current-title">
                <div class="current-head">
                    @include('_partials.play', ['slug' => $latest['slug'], 'title' => $latest['title']])
                    <h2 class="work-title current-title" id="current-title"><a href="/earnings-calls/episodes/{{ $latest['slug'] }}/">{{ $latest['title'] }}</a></h2>
                </div>
                <dl class="current-facts">
                    <div><dt>The current call</dt><dd>{{ $latest['label'] }}</dd></div>
                    <div><dt>Released</dt><dd>{{ $latest['released_on'] }}</dd></div>
                    <div><dt>Length</dt><dd>{{ $length($latest['duration']) }}</dd></div>
                    <div><dt>Exhibits</dt><dd>{{ count($latest['exhibits']) }}</dd></div>
                </dl>
                @php
                    // The notes' opening, whole sentences up to about two lines, without the quarter and part (the facts carry them).
                    $sentences = preg_split('/(?<=[.!?])\s+/', preg_replace('/^Q\d \'\d\d, Part \d+\. /', '', $latest['notes_text']));
                    $opening = '';
                    foreach ($sentences as $sentence) { if ($opening !== '' && mb_strlen($opening.' '.$sentence) > 260) break; $opening = trim($opening.' '.$sentence); }
                @endphp
                <p class="current-notes">{{ $opening }}</p>
                <a class="current-link" href="/earnings-calls/episodes/{{ $latest['slug'] }}/">To the call @include('_partials.arrow')</a>
                <p class="caption">Plate 3 · The current call</p>
            </section>
        @endif
        <section class="plate next-plate" aria-labelledby="next-title">
            <h2 class="section-title" id="next-title">{{ $latest ? 'The next call' : 'The first call' }}</h2>
            @if (count($page->upcoming) > 0)
                <p class="next-when">{{ $page->upcoming[0]['when'] }}</p>
                <ol class="next-calls">
                    @foreach ($page->upcoming as $call)
                        <li><span>{{ $call['label'] }}</span>{{ $call['title'] }}</li>
                    @endforeach
                </ol>
            @else
                <p class="next-when">Mondays, 6:00 AM Eastern</p>
                <p class="next-note">The next call is being scheduled. Management thanks you for holding.</p>
            @endif
            <p class="caption">Plate 4 · On the calendar</p>
        </section>
    </div>

    <div class="sheet-row">
        <section class="plate calls" aria-labelledby="calls-title" data-snap>
            <h2 class="section-title" id="calls-title"><a href="/earnings-calls/">The calls</a></h2>
            @include('_partials.run')
            <p class="caption">Plate 5 · The run</p>
        </section>
        @include('_partials.subscribe')
    </div>
@endsection
