{{-- Shareholder relations: the show's own pages (as the archive's Organization), an index at the head of each one's notes. --}}
@php $path = $page->getPath(); @endphp
<h2 class="notes-title">Shareholder relations</h2>
<ul class="relations-index">
    <li><a href="/earnings-calls/about/" @if ($path === '/earnings-calls/about') aria-current="page" @endif>About</a></li>
    <li><a href="/earnings-calls/subscribe/" @if ($path === '/earnings-calls/subscribe') aria-current="page" @endif>Subscribe</a></li>
</ul>
