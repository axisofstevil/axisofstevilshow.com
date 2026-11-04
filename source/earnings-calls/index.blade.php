@extends('_layouts.master')

@section('title', 'Earnings Calls — The Axis of Stevil Show')
@section('body_class', 'is-run')

@section('body')
    @php
        // The whole run's quarters, Q1 '05 to Q2 '07, for the thumb index: each one there once it has a call, released or scheduled.
        $there = collect($page->quarters)->pluck('season')->merge(collect($page->upcoming)->pluck('season'))->unique()->all();
        $next = $page->upcoming[0]['season'] ?? null;
        $years = [];
        for ($season = 1; $season <= 10; $season++) { $years[2005 + intdiv($season - 1, 4)][] = $season; }
    @endphp
    <div class="index-sheet" data-snap>
        <section class="plate calls" aria-labelledby="calls-title">
            {{-- The series, restated as facts (the home page sells it): its name, its line, the run, the schedule and what
                 is out so far, then straight into the quarters. --}}
            <div class="series-head">
                <h1 class="work-title" id="calls-title">{{ $page->series->name }}</h1>
                <a class="series-subscribe" href="/earnings-calls/subscribe/">Subscribe @include('_partials.arrow')</a>
            </div>
            {{-- The series' introduction, from its channel (Markdown); the draft line only without it. --}}
            @if ($page->introduction)
                <div class="calls-lede series-intro">{!! $page->introduction !!}</div>
            @else
                <p class="calls-lede">The archive's quarterly results, 2005 to 2007, reported twenty years late.</p>
            @endif
            <dl class="series-facts">
                <div><dt>The run</dt><dd>Q1 '05 to Q2 '07</dd></div>
                <div><dt>Schedule</dt><dd>Mondays, 6:00 AM ET</dd></div>
                <div><dt>Calls released</dt><dd>{{ $page->figures['calls'] ?? 0 }}</dd></div>
            </dl>
            @if (count($page->quarters) > 0 || count($page->upcoming) > 0)
                @include('_partials.run', ['level' => 2])
            @else
                <p class="calls-lede">The first call has not begun. Management thanks you for holding.</p>
            @endif
            <p class="caption">Plate 1 · The run</p>
        </section>
        {{-- The thumb index, as the archive's: the run's quarters by year, each a jump to its group, which lifts when reached. --}}
        <nav class="plate thumb-index" aria-label="The quarters">
            <div class="thumb-list">
                @foreach ($years as $year => $seasons)
                    <p class="thumb-year">{{ $year }}</p>
                    @foreach ($seasons as $season)
                        @php $label = 'Q'.((($season - 1) % 4) + 1); @endphp
                        @if (in_array($season, $there))
                            <a href="#{{ strtolower($label) }}-{{ $year }}" data-turbo="false" @if ($season === $next) class="is-next" title="The next call" @endif>{{ $label }}</a>
                        @else
                            <span class="is-waiting" title="No calls yet">{{ $label }}</span>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </nav>
    </div>
    @include('_partials.subscribe')
@endsection
