@extends('_layouts.master')

@section('body_class', 'is-home')
@section('no_running_head', true)

@section('body')
    @php
        $length = fn ($s) => sprintf('%d:%02d', intdiv((int) $s, 60), (int) $s % 60);
        $most = max(1, ...array_map(fn ($q) => $q['released'] + $q['scheduled'], $page->chart->toArray() ?: [['released' => 1, 'scheduled' => 0]]));
        $latest = $page->latest;
        // Before the first release the page is the first call's notice: its date and agenda, in place of the run's results.
        $first = $page->upcoming[0] ?? null;
        $opening = ! $latest && $first;
        $together = $first ? count(array_filter($page->upcoming->toArray(), fn ($u) => $u['releases_at'] === $first['releases_at'])) : 0;
        $words = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten'];
        $plate = 1;
    @endphp
    <section class="fold-top home-top" data-snap data-fill>
        <div class="plate cover call home-cover">
            {{-- The run's chart: calls per quarter, Q1 '05 to Q2 '07; released solid, scheduled in outline. Once there are results. --}}
            @if ($latest)
                <div class="cover-chart" aria-hidden="true">
                    @foreach ($page->chart as $quarter)
                        <span class="cover-bar"><i style="--h: {{ round($quarter['released'] / $most * 100) }}%"></i><b style="--h: {{ round(($quarter['released'] + $quarter['scheduled']) / $most * 100) }}%"></b></span>
                    @endforeach
                </div>
            @endif
            <p class="cover-title home-title">Earnings<br>Calls</p>
            <div class="cover-band">
                {{-- The page's heading is the show's name, in its lockup (the image's text): what the home page is. --}}
                <h1 class="cover-lockup">@include('_partials.lockup')</h1>
                <p class="cover-imprint"><span>Q1 '05 – Q2 '07</span><span class="caption">Plate {{ $plate++ }} · The cover</span></p>
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
            {{-- The results so far; before the first call, what is coming: its day, the calls on the calendar, the quarters to report. --}}
            @if ($opening)
                <dl class="figures">
                    <div><dt>The first call</dt><dd>{{ $first['day'] }}</dd></div>
                    <div><dt>On the calendar</dt><dd>{{ count($page->upcoming) }}</dd></div>
                    <div><dt>Quarters to report</dt><dd>10</dd></div>
                </dl>
            @else
                <dl class="figures">
                    <div><dt>Calls released</dt><dd><a href="/earnings-calls/" aria-label="{{ $page->figures['calls'] ?? 0 }} calls released: the calls">{{ $page->figures['calls'] ?? 0 }}</a></dd></div>
                    <div><dt>Quarters reported</dt><dd>{{ $page->figures['quarters'] ?? 0 }}</dd></div>
                    <div><dt>Minutes on record</dt><dd>{{ $page->figures['minutes'] ?? 0 }}</dd></div>
                </dl>
            @endif
            <p class="caption">Plate {{ $plate++ }} · Letter to listeners</p>
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
                <p class="caption">Plate {{ $plate++ }} · The current call</p>
            </section>
        @endif
        @if ($opening)
            {{-- The notice of the first call, as a company gives one: when, what is on the agenda, and how to dial in. --}}
            <section class="plate notice" aria-labelledby="notice-title">
                <div class="notice-main">
                    <h2 class="section-title notice-title" id="notice-title">Notice of the first call</h2>
                    <p class="notice-when"><span>{{ $first['date'] }}</span><span>{{ $first['time'] }}</span></p>
                    <p class="notice-lede">The Axis of Stevil will host its first earnings call.@if ($together > 1) The first {{ $words[$together] ?? $together }} calls go out together; after that, a new call every Monday.@endif Participants may dial in from any podcast app.</p>
                    <a class="ad-cta" href="/earnings-calls/subscribe/">Subscribe before the call @include('_partials.arrow')</a>
                </div>
                <div class="notice-agenda">
                    <h3 class="notice-sub">On the calendar</h3>
                    <ol class="next-calls">
                        @foreach ($page->upcoming as $call)
                            <li><span>{{ $call['label'] }}@if ($call['releases_at'] !== $first['releases_at']) · {{ $call['short'] }}@endif</span>{{ $call['title'] }}</li>
                        @endforeach
                    </ol>
                </div>
                <p class="caption">Plate {{ $plate++ }} · Notice of the call</p>
            </section>
        @else
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
            <p class="caption">Plate {{ $plate++ }} · On the calendar</p>
        </section>
        @endif
    </div>

    <div class="sheet-row">
        {{-- The run, once a call is out (before then the notice's agenda is the whole of it). --}}
        @unless ($opening)
            <section class="plate calls" aria-labelledby="calls-title" data-snap>
                <h2 class="section-title" id="calls-title"><a href="/earnings-calls/">The calls</a></h2>
                @include('_partials.run')
                <p class="caption">Plate {{ $plate++ }} · The run</p>
            </section>
        @endunless
        @include('_partials.subscribe')
    </div>
@endsection
