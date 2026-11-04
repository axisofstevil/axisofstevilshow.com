{{-- Each quarter's anchor is its quarter and year in full (#q2-2005). The run, as an index: each quarter a group, its parts in order with their lengths; the next call in its place. --}}
@php
    $length = fn ($s) => sprintf('%d:%02d', intdiv((int) $s, 60), (int) $s % 60);
    $groups = collect($page->quarters)->map(fn ($q) => ['season' => $q['season'], 'name' => $q['name'], 'year' => $q['year'], 'calls' => collect($q['episodes'])->map(fn ($e) => ['part' => $e['part'], 'slug' => $e['slug'], 'title' => $e['title'], 'href' => '/earnings-calls/episodes/'.$e['slug'].'/', 'meta' => $length($e['duration'])])->all()])->keyBy('season');
    foreach ($page->upcoming as $u) {
        $group = $groups[$u['season']] ?? ['season' => $u['season'], 'name' => $u['season_name'], 'year' => null, 'calls' => []];
        $group['calls'][] = ['part' => $u['part'], 'slug' => null, 'title' => $u['title'], 'href' => null, 'meta' => $u['when']];
        $groups[$u['season']] = $group;
    }
@endphp
<div class="run">
    @foreach ($groups->sortKeys() as $group)
        @php $anchor = 'q'.((($group['season'] - 1) % 4) + 1).'-'.(2005 + intdiv($group['season'] - 1, 4)); @endphp
        <section class="run-group" id="{{ $anchor }}" aria-labelledby="{{ $anchor }}-name">
            <h{{ $level ?? 3 }} class="run-quarter" id="{{ $anchor }}-name">{{ $group['name'] }} <span>Season {{ $group['season'] }}</span></h{{ $level ?? 3 }}>
            <ol>
                @foreach ($group['calls'] as $call)
                    <li @if (! $call['href']) class="is-coming" @endif>
                        @if ($call['slug'])@include('_partials.play', ['slug' => $call['slug'], 'title' => $call['title'], 'class' => 'play-small'])@else<span class="play-spacer" aria-hidden="true"></span>@endif
                        <span class="run-part">Part {{ $call['part'] }}</span>
                        @if ($call['href'])<a href="{{ $call['href'] }}">{{ $call['title'] }}</a>@else<span class="run-title">{{ $call['title'] }}</span>@endif
                        <span class="run-meta">{{ $call['meta'] }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endforeach
</div>
