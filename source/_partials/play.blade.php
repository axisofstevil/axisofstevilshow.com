{{-- A call's play button: anywhere but its own page, it starts the call in the dock, then goes to the call's page. --}}
<button type="button" class="play {{ $class ?? '' }}" data-play-call="{{ $slug }}" data-call-slug="{{ $slug }}" data-call-title="{{ $title }}" data-href="/earnings-calls/episodes/{{ $slug }}/" aria-label="Play {{ $title }}">
    <svg viewBox="0 0 16 16" aria-hidden="true"><path class="icon-play" d="M5 3l8 5-8 5z"/><path class="icon-resume" d="M2.5 3h2v10h-2zM6.5 3l7.5 5-7.5 5z"/><path class="icon-pause" d="M4 3h3v10H4zM9 3h3v10H9z"/></svg>
</button>
