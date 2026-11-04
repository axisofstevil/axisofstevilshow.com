@extends('_layouts.master')

@section('title', 'About the calls — The Axis of Stevil Show')
@section('body_class', 'is-about')

@section('body')
    <div class="spread" data-snap>
        <article class="plate record">
            <h1 class="work-title">About the calls</h1>
            <div class="work-body">
                {{-- The show's description: this paragraph, and only this one, is what the feed sends to the directories
                     (config.php, show.description). Everything below it is the site's own. --}}
                <p class="about-lede">{{ $page->show->description }}</p>
            </div>
            {{-- The site's own, from here down: the series' About page on its channel (its page at about), set by its Markdown (## a
                 section, ### a sub-head, a definition list an agenda); the site's own sections only without it. --}}
            @php $about = $page->about ? json_decode(json_encode($page->about), true) : null; @endphp
            @if ($about)
                <div class="about-body">{!! $about['content'] !!}</div>
            @else
            <section class="about-part" aria-labelledby="the-format">
                <h2 class="about-head" id="the-format">About the format</h2>
                <p class="about-text">The Axis of Stevil was always a company. It had divisions, committees and a mission, and it published on a schedule. It never once reported its results. Twenty years on, management concluded that the responsible course was a quarterly earnings call, with the archive's works as the results. The CFO reports them with all the gravity they deserve: about a sixth of Earth's, roughly that of the moon.</p>
            </section>
            <section class="about-part" aria-labelledby="on-a-call">
                <h2 class="about-head" id="on-a-call">On a call</h2>
                <p class="about-text">Every call follows the agenda of a quarterly earnings call. The order varies with the quarter's business.</p>
                <dl class="about-agenda">
                    <div><dt>Results</dt><dd>Works performance in the period, read in full. Comics are read panel by panel; the CFO describes the pictures, a duty not in his original job description.</dd></div>
                    <div><dt>Words</dt><dd>New vocabulary, as filed by the vocabulary division. The division does not report to the CFO. Few divisions do.</dd></div>
                    <div><dt>Questions from shareholders</dt><dd>Read into the record and answered, twenty years after they were asked. Management stands by the answers as given at the time.</dd></div>
                    <div><dt>Featured Steve</dt><dd>Personnel. A Steve the company admires, recognized for their contribution. They are not an employee. They have not been told.</dd></div>
                    <div><dt>Art Acquisitions</dt><dd>The committee's report. The committee meets every period, at length, and acquires less often than it meets.</dd></div>
                    <div><dt>Housekeeping</dt><dd>Items that do not suit the call's format, noted for the record. They are best enjoyed on the site, where they work. If they work.</dd></div>
                    <div><dt>Forward-looking statements</dt><dd>What management expects next quarter, followed by the statement Legal requires, read quickly so that nobody hears it.</dd></div>
                </dl>
                <h3 class="about-subhead">Supporting materials</h3>
                <p class="about-text">Shareholders following along online will find the packet on each call's page: the exhibits, numbered, turning as the CFO refers to them, and the transcript, keeping pace. Every work read on the call is listed at the foot of its page, with a link to where it first ran.</p>
            </section>
            <section class="about-part" aria-labelledby="the-works">
                <h2 class="about-head" id="the-works">The works</h2>
                <p class="about-text">The company's holdings are the archive at <a href="{{ $page->archiveUrl }}/">axisofstevil.com</a>: random insanity at its finest, published from 2005 to 2007 in some hundred weekly issues and kept, unchanged, ever since. The calls read each included work, as it was written, by division.</p>
                <dl class="about-agenda">
                    <div><dt>Publications</dt><dd>The most amazing stories the universe has to offer, once every Monday, Wednesday and Friday. Reported as results.</dd></div>
                    <div><dt>Comics</dt><dd>Reported as results, panel by panel. The pictures are in your packet.</dd></div>
                    <div><dt>Words</dt><dd>The vocabulary division's filings, each with a definition and an example sentence.</dd></div>
                    <div><dt>Ask Stevil</dt><dd>Life's unsolved mysteries, put to the company by its shareholders. The Axis of Stevil answered them in writing.</dd></div>
                    <div><dt>Featured Steves</dt><dd>Steves who show excellence in their field: famous, local, fictional, feline or honorary.</dd></div>
                    <div><dt>Everything else</dt><dd>Letters, animations, extras, the gallery and the tunes of the week. Where they suit the call, it reads them; where they do not, see Housekeeping.</dd></div>
                </dl>
            </section>
            <section class="about-part" aria-labelledby="pisco-sour">
                <h2 class="about-head" id="pisco-sour">Pisco Sour</h2>
                <p class="about-text">A bright, foamy Peruvian cocktail made with unaged grape brandy.</p>
                <h3 class="about-subhead">Ingredients</h3>
                <ul class="about-list">
                    <li>2 to 3 ounces pisco</li>
                    <li>1 ounce fresh lime juice</li>
                    <li>&frac34; to 1 ounce simple syrup (equal parts sugar and water)</li>
                    <li>1 egg white</li>
                    <li>3 to 5 drops aromatic bitters</li>
                </ul>
                <h3 class="about-subhead">Instructions</h3>
                <dl class="about-agenda about-steps">
                    <div><dt>1. Dry shake</dt><dd>Shake the pisco, lime juice, simple syrup and egg white without ice for 10 to 15 seconds, until foamy.</dd></div>
                    <div><dt>2. Wet shake</dt><dd>Add ice and shake hard for 15 to 30 seconds, until very cold.</dd></div>
                    <div><dt>3. Strain</dt><dd>Double-strain into a chilled coupe or rocks glass.</dd></div>
                    <div><dt>4. Garnish</dt><dd>Drip the bitters onto the foam, then drag a toothpick through the drops to make a design.</dd></div>
                </dl>
            </section>
            @endif
            <p class="caption">Plate 1 · About the calls</p>
        </article>
        <aside class="plate notes">
            <div class="notes-inner">
                @include('_partials.relations')
                <dl class="facts">
                    <div><dt>Presented by</dt><dd>Juan Conroy, CFO</dd></div>
                    <div><dt>On the call</dt><dd>Stevil, CEO</dd></div>
                    <div><dt>The run</dt><dd>Q1 '05 to Q2 '07</dd></div>
                    <div><dt>Schedule</dt><dd>Mondays, 6:00 AM ET</dd></div>
                </dl>
            </div>
            <p class="caption">Plate 2 · Shareholder relations</p>
        </aside>
    </div>
    @include('_partials.subscribe')
@endsection
