{{-- The first viewport: the call (Plate 1, slate, the sheet's grid across it) and the packet (Plate 2). site.js
     binds both to the docked audio; without script, the audio plays here. --}}
@php
    $length = fn ($s) => sprintf('%d:%02d', intdiv((int) $s, 60), (int) $s % 60);
    $first = $episode['exhibits'][0] ?? null;
    $count = count($episode['exhibits']);
    $duration = max(1, (float) $episode['duration']);
    $chapters = $episode['chapters'];
@endphp
<section class="fold-top" data-call="{{ $episode['slug'] }}" data-snap data-fill aria-label="{{ $episode['title'] }}">
    <div class="plate cover call">
        <p class="call-status"><span class="live-dot" aria-hidden="true"></span><span data-live-state>On the line</span><span class="call-now"><span class="visually-hidden">Current topic: </span><span data-now-title>{{ $chapters[0]['title'] ?? '' }}</span></span></p>
        {{-- The call's chart: its chapters in proportion, filling as it plays; a click on one seeks there. A pointer's
             shortcut only (aria-hidden): the agenda's rows are the same seeks, each a full-size control for every input. --}}
        <ol class="timeline" aria-hidden="true">
            @foreach ($chapters as $i => $chapter)
                @php $end = $chapters[$i + 1]['start'] ?? $duration; @endphp
                <li style="flex-grow: {{ max(1, round($end - $chapter['start'])) }}"><span class="timeline-seg" data-seek="{{ $chapter['start'] }}" data-tip="{{ $chapter['title'] }}"><i></i></span></li>
            @endforeach
        </ol>
        <h1 class="cover-title call-title">{{ $episode['title'] }}</h1>
        <div class="call-controls">
            <button type="button" class="play" data-play data-call-slug="{{ $episode['slug'] }}" data-call-title="{{ $episode['title'] }}" aria-label="Play the call">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path class="icon-play" d="M5 3l8 5-8 5z"/><path class="icon-resume" d="M2.5 3h2v10h-2zM6.5 3l7.5 5-7.5 5z"/><path class="icon-pause" d="M4 3h3v10H4zM9 3h3v10H9z"/></svg>
            </button>
            <dl class="call-figures">
                <div><dt>Elapsed</dt><dd data-clock>0:00</dd></div>
                <div><dt>Length</dt><dd>{{ $length($duration) }}</dd></div>
            </dl>
            <p class="call-imprint"><span>{{ $episode['season_name'] }} · Part {{ $episode['part'] }}</span><span class="caption">Plate 1 · The call</span></p>
        </div>
        <noscript><audio class="call-audio" src="{{ $episode['audio']['url'] }}" controls preload="none"></audio></noscript>
    </div>
    <div class="plate packet" data-packet>
        @if ($first)
            <h2 class="visually-hidden">Exhibits</h2>
            <div class="packet-frames">
                <figure class="packet-frame is-chapter" data-chapter-frame hidden>
                    <div class="packet-picture"><img alt="" data-chapter-image></div>
                    <figcaption><span class="packet-number">No exhibit</span> <span data-chapter-caption></span></figcaption>
                </figure>
                @foreach ($episode['exhibits'] as $exhibit)
                    <figure class="packet-frame" data-exhibit="{{ $exhibit['number'] }}" data-title="{{ ucfirst($exhibit['title']) }}" @if (! $loop->first) hidden @endif>
                        <div class="packet-picture"><img src="{{ $exhibit['image'] }}" alt="{{ $exhibit['alt'] }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif></div>
                        <figcaption><span class="packet-number">Exhibit {{ $exhibit['number'] }} of {{ $count }}</span> {{ ucfirst($exhibit['title']) }}@if ($exhibit['work'] ?? null) · <a href="{{ $exhibit['work']['url'] }}">on axisofstevil.com @include('_partials.arrow')</a>@endif</figcaption>
                    </figure>
                @endforeach
            </div>
            <div class="packet-index" role="group" aria-label="Exhibits">
                @foreach ($episode['exhibits'] as $exhibit)
                    <button type="button" class="packet-tab" data-exhibit-tab="{{ $exhibit['number'] }}" aria-label="Exhibit {{ $exhibit['number'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $exhibit['number'] }}</button>
                @endforeach
            </div>
        @else
            <h2 class="visually-hidden">Exhibits</h2>
            <p class="packet-empty">No exhibits on this call.</p>
        @endif
        <p class="caption">Plate 2 · The shareholder packet</p>
    </div>
</section>
