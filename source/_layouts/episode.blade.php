@extends('_layouts.master')

{{-- The hook, then the series' own filing ("Q2 '05 Earnings Call, Part 3"), then the banner. --}}
@section('title', $page->title.' — '.$page->season_name.' Earnings Call, Part '.$page->part.' — The Axis of Stevil Show')
@section('description', $page->notes_text)
@section('og_type', 'article')
{{-- Its preview's picture: its first exhibit, else the series' cover (the layout's default). --}}
@section('card', $page->exhibits[0]['image'] ?? '')
@section('body_class', 'is-episode')

@section('body')
    @php $length = fn ($s) => sprintf('%d:%02d', intdiv((int) $s, 60), (int) $s % 60); @endphp
    @include('_partials.console', ['episode' => $page->toArray()])

    <div class="spread" data-snap>
        {{-- The call's record is what search indexes (Pagefind): its title, what it is, its quarter and picture said here,
             since the call's heading sits in the console above. --}}
        <article class="plate record" data-pagefind-body data-pagefind-sort="date:{{ $page->released_at }}">
            <span hidden data-pagefind-meta="title:{{ $page->title }}"></span>
            <span hidden data-pagefind-meta="kind:Call"></span>
            <span hidden data-pagefind-meta="quarter:{{ $page->season_name }}, Part {{ $page->part }}"></span>
            {{-- Its first exhibit, else a blank tile of the sheet, which should not draw the eye. --}}
            <span hidden data-pagefind-meta="image:{{ $page->exhibits[0]['image'] ?? '/assets/search/no-picture.svg' }}"></span>
            <div class="record-pair">
            <section class="record-part record-agenda" aria-labelledby="agenda-title">
                <h2 class="section-title" id="agenda-title">Agenda</h2>
                <ol class="agenda">
                    @foreach ($page->chapters as $chapter)
                        <li class="agenda-item">
                            <button type="button" class="agenda-button" data-seek="{{ $chapter['start'] }}">
                                <span class="agenda-time" data-pagefind-ignore>{{ $chapter['clock'] }}</span>
                                <span class="agenda-title">{{ $chapter['title'] }}</span>
                                @if ($chapter['exhibit'])<span class="agenda-exhibit">Ex. {{ $chapter['exhibit'] }}</span>@endif
                            </button>
                        </li>
                    @endforeach
                </ol>
            </section>
            <section class="record-part record-summary" aria-labelledby="notes-title">
                <h2 class="section-title" id="notes-title">Call summary</h2>
                <div class="work-body">{!! $page->notes !!}</div>
            </section>
            </div>
            <section class="record-part" aria-labelledby="transcript-title">
                <h2 class="section-title" id="transcript-title">Prepared remarks</h2>
                @foreach ($page->transcript as $part)
                    <div class="remarks" @if ($part['start'] !== null) data-remarks-start="{{ $part['start'] }}" @endif>
                        <h3 class="remarks-head">
                            @if ($part['start'] !== null)
                                <button type="button" class="remarks-seek" data-seek="{{ $part['start'] }}"><span class="remarks-time" data-pagefind-ignore><span class="remarks-now" aria-hidden="true"></span>{{ $length($part['start']) }}</span><span>{{ $part['title'] }}</span></button>
                            @else
                                <span>{{ $part['title'] }}</span>
                            @endif
                        </h3>
                        @if ($part['start'] !== null)<span class="remarks-track" aria-hidden="true"><i></i></span>@endif
                        <div class="work-body remarks-body">
                            @foreach ($part['paragraphs'] as $paragraph)
                                <p data-cue="{{ $paragraph['start'] }}" data-cue-end="{{ $paragraph['end'] }}">{{ $paragraph['text'] }}</p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>
            <p class="caption" data-pagefind-ignore>Plate 3 · The record</p>
        </article>

        <aside class="plate notes" aria-labelledby="facts-title">
            <div class="notes-inner">
                <h2 class="notes-title" id="facts-title">Notes</h2>
                <dl class="facts">
                    @php $anchor = 'q'.((($page->season - 1) % 4) + 1).'-'.(2005 + intdiv($page->season - 1, 4)); @endphp
                    <div><dt>Quarter</dt><dd><a href="/earnings-calls/#{{ $anchor }}">{{ $page->season_name }}</a></dd></div>
                    <div><dt>Part</dt><dd>{{ $page->part }}</dd></div>
                    <div><dt>Call</dt><dd>{{ $page->number }} of the run</dd></div>
                    <div><dt>Released</dt><dd><time datetime="{{ $page->released_at }}">{{ $page->released_on }}</time></dd></div>
                    <div><dt>Length</dt><dd>{{ $length($page->duration) }}</dd></div>
                    <div><dt>Exhibits</dt><dd>{{ count($page->exhibits) }}</dd></div>
                    <div><dt>Audio</dt><dd><a href="{{ $page->audio['url'] }}">MP3, {{ number_format($page->audio['bytes'] / 1000000, 1) }} MB</a></dd></div>
                </dl>
                <p class="notes-label">The run</p>
                <ul class="notes-run">
                    @if ($page->previous)<li><a href="/earnings-calls/episodes/{{ $page->previous['slug'] }}/" rel="prev"><span>Previous · {{ $page->previous['label'] }}</span>{{ $page->previous['title'] }}</a></li>@endif
                    @if ($page->next)<li><a href="/earnings-calls/episodes/{{ $page->next['slug'] }}/" rel="next"><span>Next · {{ $page->next['label'] }}</span>{{ $page->next['title'] }}</a></li>
                    @elseif (count($page->upcoming) > 0)<li class="is-coming"><span>Next · {{ $page->upcoming[0]['when'] }}</span>{{ $page->upcoming[0]['title'] }}</li>@endif
                </ul>
            </div>
                @if (count($page->exhibits) > 0)
                    {{-- The exhibit in hand, small, while the packet above is out of view; it follows the call. --}}
                    <div class="mini-slot mini-sticky" data-mini-slot>
                    <a class="mini-packet" href="#main" data-mini-packet tabindex="-1" aria-hidden="true">
                        <span class="mini-picture"><img data-mini-image src="{{ $page->exhibits[0]['image'] }}" alt="{{ $page->exhibits[0]['alt'] }}"><span class="zoom-open mini-zoom" role="button" tabindex="-1" data-mini-zoom aria-label="Zoom: view this exhibit full size"><svg viewBox="0 0 12 12" aria-hidden="true"><circle cx="5" cy="5" r="3.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M7.6 7.6 11 11" stroke="currentColor" stroke-width="1.5"/></svg>Zoom</span></span>
                        <span class="mini-caption"><span class="packet-number" data-mini-number>Exhibit 1 of {{ count($page->exhibits) }}</span><span data-mini-title>{{ ucfirst($page->exhibits[0]['title']) }}</span></span>
                    </a>
                    </div>
                @endif
            <p class="caption">Plate 4 · Notes</p>
        </aside>
        </div>

    @php
        // Two lists: the works read on the call (recited, or shown as exhibits and read from), in the
        // call's order; and the works it only mentions (its housekeeping).
        $groups = collect($page->works)->groupBy(fn ($work) => $work['role'] === 'housekeeping' ? 'mentioned' : 'read');
        $headings = ['read' => 'Read on the call', 'mentioned' => 'Mentioned on the call'];
    @endphp
    {{-- The foot of the page, as the home page's: the works beside the house ad, so a listener done with the call goes on to
         the works, or subscribes. Without works, the ad alone. --}}
    <div class="sheet-row">
    @if (count($page->works) > 0)
        {{-- Every archive work the call covers, so a listener goes on to them once the call is done. --}}
        <section class="plate works-plate" aria-labelledby="works-title" data-snap>
            <h2 class="section-title" id="works-title">The works on this call</h2>
            <p class="works-lede">Each one as it first ran, on axisofstevil.com.</p>
            <div class="works-groups">
                @foreach ($headings as $role => $heading)
                    @if (isset($groups[$role]))
                        <section class="works-group" aria-label="{{ $heading }}">
                            <h3 class="notes-label">{{ $heading }}</h3>
                            <ul>
                                @foreach ($groups[$role] as $work)
                                    <li><a href="{{ $work['url'] }}">{{ $work['title'] }} @include('_partials.arrow')</a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                @endforeach
            </div>
            <p class="caption">Plate 5 · The works</p>
        </section>
    @endif
        @include('_partials.subscribe')
    </div>
@endsection
