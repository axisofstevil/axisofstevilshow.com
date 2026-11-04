<?php

/*
 * The episodes are read at build time from the show's channel at CHANNEL_URL (app/Services/RemoteData.php).
 */
return [
    'production' => false,
    // Absolute links (canonical, the feed, social cards, the apps' subscribe links) name the site's address: the real one,
    // or SITE_URL for a build served somewhere else first (a CloudFront test address). Links within the site are relative.
    'baseUrl' => rtrim(env('SITE_URL') ?: 'https://www.axisofstevilshow.com', '/'),
    // A build at any other address than the real one is a preview: every page noindex, robots.txt shutting crawlers out,
    // so the show is never indexed twice.
    'preview' => rtrim(env('SITE_URL') ?: 'https://www.axisofstevilshow.com', '/') !== 'https://www.axisofstevilshow.com',
    'title' => 'The Axis of Stevil Show',
    // The show's name wherever a page's own title would stand in for it (a call's page has its own): the search
    // engines' site name, the podcast's name in the structured data.
    'siteName' => 'The Axis of Stevil Show',
    // The series this site carries so far (the show is a banner; each series is its own channel, feed
    // and subsite): its name, its title in the directories, and where it lives on the site.
    'series' => [
        'name' => 'Earnings Calls',
        'title' => 'Earnings Calls: The Axis of Stevil Show',
        'path' => '/earnings-calls/',
    ],
    // The site's own forwards, beside the channel's: an address someone might trim a call's back to.
    'forwards' => [
        ['from' => '/earnings-calls/episodes/', 'to' => '/earnings-calls/'],
    ],
    // Where to subscribe, as the series' channel keeps it (set by RemoteData; null until the channel serves it, when the
    // show's own list, show.apps, stands in).
    'subscribe_links' => null,
    'archiveUrl' => 'https://www.axisofstevil.com',
    'cover' => 'https://archive.axisofstevil.com/shows/earnings-calls/cover.jpg',

    // The show as the podcast directories list it: its description says it is parody and how the CFO is made.
    'show' => [
        // The show's description, sent in the feed and printed as the first paragraph of About, word for word: the one
        // place both read from. It carries the show-level AI disclosure Apple requires (Content Requirements, 1.11).
        'description' => 'The Axis of Stevil is reporting its results twenty years late. In a run of quarterly earnings calls, its Chief Financial Officer, Juan Conroy, reads the archive of axisofstevil.com from 2005 to 2007, one quarter to a season and a few calls to a quarter. Stevil, the CEO, is on every call. He has not spoken. The CFO\'s voice is synthetic, and his remarks were written with assistance from AI; the works he reads were written by people. A parody, read from an archive of satire.',
        'author' => 'The Axis of Stevil',
        'category' => 'Comedy',
        'language' => 'en-us',
        // The show's pages in the directories, by app name, once it is listed: a listing replaces the app's own
        // link below, and an app with no link of its own (Spotify) is offered only once it has one.
        'listings' => [],
        // The apps the subscribe page offers, in order: each one's link hands it the feed ({feed}, {host}: the
        // feed's address, whole and without its scheme; {title}: the show's), and its platforms put it first
        // on a device it runs on.
        'apps' => [
            ['name' => 'Apple Podcasts', 'on' => 'iPhone, iPad and Mac', 'platforms' => 'ios mac', 'open' => 'podcast://{host}'],
            ['name' => 'Spotify', 'on' => 'Phones, computers and the web', 'platforms' => 'ios android mac windows', 'open' => null],
            ['name' => 'Overcast', 'on' => 'iPhone and iPad', 'platforms' => 'ios', 'open' => 'overcast://x-callback-url/add?url={feed}'],
            ['name' => 'Pocket Casts', 'on' => 'iPhone, iPad and Android', 'platforms' => 'ios android', 'open' => 'pktc://subscribe/{host}'],
            ['name' => 'AntennaPod', 'on' => 'Android', 'platforms' => 'android', 'open' => 'https://antennapod.org/deeplink/subscribe?url={feed}&title={title}'],
            // No documented link takes it a feed; its own page for the show, once it has indexed it, is the link.
            ['name' => 'Podcast Addict', 'on' => 'Android', 'platforms' => 'android', 'open' => null],
        ],
    ],

    // Set by App\Services\Assets: the content-named stylesheet and script.
    'assetCss' => null,
    'assetJs' => null,
    'snapInline' => null,

    // The site's own channel, the build's one address: its pages and forwards are the site's, and its
    // linked channels say which series it builds (the featured one). A series' own channel works too, alone.
    'channel_url' => env('CHANNEL_URL'),

    // Whether each forward gets a stub page at its old address (for a host that only serves files); S3 answers
    // with a real 301 set by the deploy, so production leaves an empty file in the stub's place.
    'redirect_stubs' => true,

    'timezone' => 'America/New_York',

    // Filled by App\Services\RemoteData.
    'episodes' => [],
    'quarters' => [],
    'latest' => null,
    'upcoming' => [],
    'chart' => [],
    'figures' => [],
    'playlist' => [],
    'redirects' => [],
    // The owner's words that open /llms.txt (the channel's page at llms.txt), and the error page (its page at error):
    // set by RemoteData, null without them.
    'llms' => null,
    'error' => null,
    'not_found' => null,
    // The featured series' own footer lines, either null for the site's own words (set by RemoteData).
    'tagline' => null,
    // The series' introduction on its listing page, rendered from the channel's Markdown (set by RemoteData; null for the draft).
    'introduction' => null,
    'sign_off' => null,
    // The series' own pages on its channel: its letter (the home page's, while it is featured) and its About page's sections.
    'letter' => null,
    'about' => null,

    'collections' => [
        // Each episode at /earnings-calls/episodes/{slug}/ (items set by RemoteData): the series' episodes in a segment of
        // their own, so no slug can meet the series' own pages.
        'episodes' => [
            'extends' => '_layouts.episode',
            'path' => fn ($page) => 'earnings-calls/episodes/'.$page->slug,
            'sort' => 'number',
        ],
    ],

    // A page's address, with its trailing slash.
    'link' => fn ($page) => rtrim($page->getPath(), '/').'/',
];
