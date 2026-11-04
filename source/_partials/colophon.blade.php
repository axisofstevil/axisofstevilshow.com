<footer class="plate colophon" data-snap>
    <a class="colophon-lockup" href="/" aria-label="The Axis of Stevil Show, home">@include('_partials.lockup')</a>
    {{-- The colophon's line, as the archive's: the tagline, then the copyright (owner-approved, 2026-11-03). The AI
         disclosure lives where it is read: the feed, every call's notes and its introduction, and About. --}}
    {{-- The copyright runs from 2026 to the year of the build (the site rebuilds daily): 2026, then 2026–2027 and on. --}}
    @php $year = (int) (new DateTimeImmutable('now', new DateTimeZone($page->timezone)))->format('Y'); $years = $year > 2026 ? '2026–'.$year : '2026'; @endphp
    {{-- The featured series' own lines around the site's own facts ("Est.", the copyright): its tagline and its sign-off,
         from the channel, the site's own words only without them. --}}
    @php
        $tagline = $page->tagline ?: 'Quarterly earnings calls from The Axis of Stevil';
        $signOff = $page->sign_off ?: 'All statements are backward-looking. Past results are guaranteed; they already happened.';
    @endphp
    <p class="colophon-line"><span>{{ $tagline }} · Est. 2026</span> <span>&copy; {{ $years }} The Axis of Stevil. {{ $signOff }}</span></p>
    <nav aria-label="More">
        <a href="/earnings-calls/about/">About</a>
        <a href="/earnings-calls/subscribe/">Subscribe</a>
        <a href="{{ $page->archiveUrl }}/">axisofstevil.com</a>
    </nav>
</footer>
