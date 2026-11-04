{{-- The house advertisement: wherever it runs, it sends the reader to the subscribe page. --}}
<section class="plate ad" aria-labelledby="ad-title-{{ $where ?? 'page' }}">
    <div class="ad-inner">
        <h2 class="ad-title" id="ad-title-{{ $where ?? 'page' }}">Never miss a call.</h2>
        <div class="ad-body">
            @if (! $page->latest && count($page->upcoming) > 0)
                <p class="ad-line">Subscribe now, and the first call arrives in your podcast app the moment it goes out.</p>
            @else
                <p class="ad-line">A new call every Monday at 6:00 AM Eastern, in the podcast app you already use.</p>
            @endif
            <a class="ad-cta" href="/earnings-calls/subscribe/">Subscribe @include('_partials.arrow')</a>
        </div>
    </div>
    <p class="caption">Shareholder relations</p>
</section>
