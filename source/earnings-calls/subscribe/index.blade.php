@extends('_layouts.master')

@section('title', 'Subscribe — The Axis of Stevil Show')
@section('body_class', 'is-subscribe')

@section('body')
    @php
        // Each app's link, with the feed handed to it; a listing in the directory, once there is one, instead.
        $feed = $page->baseUrl.'/earnings-calls/feed.xml';
        $listings = $page->show->listings ? $page->show->listings->toArray() : [];
        // The channel's list for the series when it serves one (already only the apps shown, in the owner's order); else the
        // site's own (show.apps, with show.listings over it).
        $fill = fn (?string $link) => $link === null ? null : strtr($link, ['{feed}' => rawurlencode($feed), '{host}' => preg_replace('#^https?://#', '', $feed), '{title}' => rawurlencode($page->series->title)]);
        $listed = $page->subscribe_links ? json_decode(json_encode($page->subscribe_links), true) : null;
        $apps = $listed !== null
            ? collect($listed)->map(fn ($app) => ['name' => $app['name'], 'on' => $app['devices'], 'platforms' => implode(' ', $app['platforms'] ?? []), 'href' => $fill($app['link'] ?? null)])
            : collect($page->show->apps->toArray())->map(fn ($app) => [
                'name' => $app['name'],
                'on' => $app['on'],
                'platforms' => $app['platforms'],
                'href' => $listings[$app['name']] ?? $fill($app['open']),
            ]);
        $apps = $apps->filter(fn ($app) => $app['href'])->values();
    @endphp
    <div class="spread" data-snap>
        <article class="plate record">
            <h1 class="work-title">Subscribe</h1>
            <p class="subscribe-lede">Every call, in your own podcast app, the Monday it goes out.</p>
            <h2 class="subscribe-head">Open the show in your app</h2>
            {{-- One table of columns for every row, the last option's too: names, devices and actions line up down it. --}}
            <div class="apps-table">
            <p class="apps-note apps-lead">Each link opens the app, if it is on this device, with the show ready to follow.</p>
            <ul class="apps" data-apps>
                @foreach ($apps as $app)
                    <li data-platforms="{{ $app['platforms'] }}">
                        <a href="{{ $app['href'] }}">
                            <span class="app-name">{{ $app['name'] }}</span>
                            <span class="app-on">{{ $app['on'] }}<span class="app-here">For this device</span></span>
                            <span class="app-go"><span class="app-go-label">Open in {{ $app['name'] }}</span> @include('_partials.arrow')</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="apps-note apps-other-note">Most other apps (Podcast Guru, Podurama, Downcast, Moon FM, RSSRadio among them) can follow a show by its address: look for Add by URL, and paste the address below.</p>
            {{-- The last option, for every app not named, after its own note: set as one
                 more row, its address in place of the devices, a copy in place of a link. --}}
            <div class="app-row app-other">
                <span class="app-name">Any other app</span>
                <span class="app-on app-address">{!! str_replace('/', '/<wbr>', e(preg_replace('#^https?://#', '', $feed))) !!}</span>
                <button type="button" class="app-go" data-copy="{{ $feed }}"><span class="app-go-label">Copy the address</span> <svg class="arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M5.5 5.5h7v7h-7zM3.5 10.5v-7h7"/></svg></button>
            </div>
            </div>
            <p class="caption">Plate 1 · Subscribe</p>
        </article>
        <aside class="plate notes">
            <div class="notes-inner">
                @include('_partials.relations')
                <dl class="facts">
                    <div><dt>Schedule</dt><dd>Mondays, 6:00 AM ET</dd></div>
                    <div><dt>Each call</dt><dd>Chapters and a transcript</dd></div>
                    <div><dt>The run</dt><dd>Q1 '05 to Q2 '07</dd></div>
                </dl>
            </div>
            <p class="caption">Plate 2 · Shareholder relations</p>
        </aside>
    </div>
@endsection
