{{-- The call, docked: one audio element kept across page visits (Turbo keeps it), carrying the run's playlist. --}}
<aside class="dock" id="dock" data-dock data-turbo-permanent hidden aria-label="The call in progress">
    <script type="application/json" data-playlist>{!! json_encode($page->playlist ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    <audio data-audio preload="none"></audio>
    <button type="button" class="dock-toggle" data-toggle aria-label="Play">
        <svg viewBox="0 0 16 16" aria-hidden="true"><path class="icon-play" d="M5 3l8 5-8 5z"/><path class="icon-pause" d="M4 3h3v10H4zM9 3h3v10H9z"/></svg>
    </button>
    <a class="dock-label" data-dock-link href="/">
        <span class="dock-live"><span class="live-dot" aria-hidden="true"></span><span data-dock-season></span></span>
        <span class="dock-title" data-dock-title></span>
        <span class="dock-now" data-dock-now></span>
    </a>
    <div class="dock-time">
        <span class="num" data-at>0:00</span>
        {{-- One control, the call's position: a slider (arrows 15 seconds, Page Up and Down a minute, Home and End), its
             chapters drawn in it, each named on hover; a click lands where it falls. --}}
        <div class="dock-track" data-track role="slider" tabindex="0" aria-label="Position in the call" aria-valuemin="0" aria-valuemax="0" aria-valuenow="0"></div>
        <span class="num" data-length>0:00</span>
    </div>
    <div class="dock-options">
        {{-- Phones and tablets: it opens the exhibits drawer. --}}
        <button type="button" class="dock-exhibit" data-drawer-open aria-controls="exhibit-drawer" aria-expanded="false">
            <svg viewBox="0 0 16 16" aria-hidden="true"><rect x="2.5" y="3" width="11" height="10"/><path d="M4.5 11l2.75-3.25 2 2.25 1.5-1.5 1.75 2.5"/><circle cx="10.5" cy="5.75" r="0.9"/></svg>
            <span class="dock-exhibit-label" data-drawer-badge>Exhibits</span>
        </button>
        <button type="button" class="dock-spot" data-to-spot aria-label="To the spot in the transcript">
            <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 3.5h10M3 6.5h10M3 9.5h6"/><circle cx="12" cy="11.5" r="2"/></svg><span>To the spot</span>
        </button>
        <label class="dock-auto" title="When a call ends, the next one in the run begins"><input type="checkbox" data-autoplay> <span class="auto-long">Autoplay</span><span class="auto-short" aria-hidden="true">Auto</span></label>
    </div>
    {{-- The exhibits drawer: the packet, risen from the dock, following the call; a picked exhibit stays until the
         call moves on, or until "Follow the call". --}}
    <div class="drawer-well">
    <section class="drawer" id="exhibit-drawer" data-drawer role="dialog" aria-modal="false" aria-labelledby="drawer-title" hidden>
        <div class="drawer-head" data-drawer-grab>
            <span class="drawer-grabber" aria-hidden="true"></span>
            <p class="drawer-caption"><span class="packet-number" data-drawer-number></span> <span class="drawer-title" id="drawer-title" data-drawer-title></span></p>
            <button type="button" class="drawer-close" data-drawer-close aria-label="Close the exhibits">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8"/></svg>
            </button>
        </div>
        <div class="drawer-picture">
            <img data-drawer-image alt="">
            <button type="button" class="zoom-open" data-drawer-zoom hidden aria-label="Zoom: view this exhibit full size"><svg viewBox="0 0 12 12" aria-hidden="true"><circle cx="5" cy="5" r="3.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M7.6 7.6 11 11" stroke="currentColor" stroke-width="1.5"/></svg>Zoom</button>
        </div>
        <div class="drawer-foot">
            <div class="drawer-tabs" role="group" aria-label="Exhibits" data-drawer-tabs></div>
            <button type="button" class="drawer-follow" data-drawer-follow hidden>Follow the call</button>
            <a class="drawer-work" data-drawer-work href="/" hidden>On axisofstevil.com @include('_partials.arrow')</a>
        </div>
    </section>
    </div>
    <button type="button" class="dock-close" data-close aria-label="Leave the call">
        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8"/></svg>
    </button>
</aside>
