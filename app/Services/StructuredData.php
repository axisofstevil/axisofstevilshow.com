<?php

namespace App\Services;

use TightenCo\Jigsaw\PageVariable;

/**
 * Each page's structured data (schema.org, as JSON-LD): what the show is, for search engines and answer engines. Every
 * page names the website and the podcast, "The Axis of Stevil Show", as their own entities, distinct from the archive
 * (The Axis of Stevil, at axisofstevil.com), which publishes them; the archive's organisation is referred to by the
 * archive's own @id, so the two sites describe one company. Both say what sets them apart from the unrelated radio show of
 * the same name (disambiguatingDescription). The podcast names its pages in the directories (sameAs). A call's page is a PodcastEpisode in its season and series;
 * it names the archive's works it covers by the archive's own ids, and its chapters as clips, each at its moment (the
 * player cues a call at ?t= seconds); the calls are an ItemList; About is an AboutPage. Pages marked noindex carry none.
 */
class StructuredData
{
    /** What sets the show apart from the unrelated radio show of the same name (owner, 2026-11-12). */
    private const string DISAMBIGUATION = 'An audio series begun in 2026 that reads the archive of axisofstevil.com (2005 to 2007). Not the weekly radio show of the same name at theaxisofstevilshow.com.';

    /**
     * @return array<string, mixed>
     */
    public static function for(PageVariable $page, string $title, string $description): array
    {
        if ($page->noindex) {
            return [];
        }

        $base = rtrim((string) $page->baseUrl, '/');
        $archive = rtrim((string) $page->archiveUrl, '/');
        $path = rtrim((string) $page->getPath(), '/');
        $url = $base.$page->link();
        // The show's own name (a call's page has its own title).
        $name = (string) $page->siteName;
        $organisation = ['@type' => 'Organization', '@id' => $archive.'/#organization', 'name' => 'The Axis of Stevil', 'url' => $archive.'/'];
        // The podcast is the series, at its own address under the banner's site.
        $series = ['@id' => $base.'/earnings-calls/#podcast'];
        $show = $page->show;

        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $base.'/#website',
                'url' => $base.'/',
                'name' => $name,
                'alternateName' => 'Axis of Stevil Show',
                // The banner's own description (its channel's), the featured series' only until it has one.
                'description' => (string) ($page->site_description ?: $show->description),
                'disambiguatingDescription' => self::DISAMBIGUATION,
                'inLanguage' => 'en-US',
                'publisher' => ['@id' => $organisation['@id']],
            ],
            $organisation,
            [
                '@type' => 'PodcastSeries',
                '@id' => $series['@id'],
                'name' => (string) $page->series->title,
                'alternateName' => (string) $page->series->name,
                'url' => $base.'/earnings-calls/',
                'description' => (string) $show->description,
                'disambiguatingDescription' => self::DISAMBIGUATION,
                'image' => (string) $page->cover,
                'webFeed' => $base.'/earnings-calls/feed.xml',
                'inLanguage' => 'en-US',
                'genre' => (string) $show->category,
                'author' => ['@id' => $organisation['@id']],
                'publisher' => ['@id' => $organisation['@id']],
                'isBasedOn' => ['@type' => 'WebSite', 'name' => 'The Axis of Stevil', 'url' => $archive.'/'],
                ...self::listings($show, $page->subscribe_links),
            ],
        ];

        $webPage = ['@id' => $url.'#webpage', 'url' => $url, 'name' => $title, 'description' => $description, 'isPartOf' => ['@id' => $base.'/#website'], 'inLanguage' => 'en-US'];

        if ($path === '') {
            $graph[] = ['@type' => 'WebPage', ...$webPage, 'about' => $series, 'mainEntity' => $series];
        } elseif ($path === '/earnings-calls' && ! $page->slug) {
            $graph[] = ['@type' => 'CollectionPage', ...$webPage, 'about' => $series, 'mainEntity' => ['@id' => $url.'#calls']];
            $graph[] = [
                '@type' => 'ItemList',
                '@id' => $url.'#calls',
                'name' => 'The calls',
                'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
                'numberOfItems' => count($page->episodes ?? []),
                'itemListElement' => array_map(fn (array $e, int $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => $base.'/earnings-calls/episodes/'.$e['slug'].'/',
                    'name' => $e['title'],
                ], self::rows($page->episodes), array_keys(self::rows($page->episodes))),
            ];
        } elseif ($page->slug && $page->audio) {
            $episode = $url.'#episode';
            $graph[] = ['@type' => 'WebPage', ...$webPage, 'mainEntity' => ['@id' => $episode], 'breadcrumb' => ['@id' => $url.'#breadcrumb']];
            $graph[] = [
                '@type' => 'PodcastEpisode',
                '@id' => $episode,
                'url' => $url,
                'name' => (string) $page->title,
                'description' => (string) $page->notes_text,
                'datePublished' => (string) $page->released_at,
                'episodeNumber' => (int) $page->part,
                'timeRequired' => self::duration((float) $page->duration),
                'image' => (string) ($page->exhibits[0]['image'] ?? $page->cover),
                'inLanguage' => 'en-US',
                'partOfSeries' => $series,
                // The works it covers, by the ids their pages on the archive give them: read, shown or housekept.
                'about' => array_map(fn (array $w) => ['@type' => 'CreativeWork', '@id' => $w['url'].'#work', 'name' => $w['title'], 'url' => $w['url']], self::rows($page->works)) ?: null,
                // Its chapters, each a clip from its start to the next one's, at an address that cues the call there.
                'hasPart' => array_map(function (array $c, int $i) use ($page, $url) {
                    $chapters = self::rows($page->chapters);
                    $start = (int) ceil((float) $c['start']);

                    return [
                        '@type' => 'Clip',
                        'name' => (string) $c['title'],
                        'startOffset' => $start,
                        'endOffset' => (int) ceil((float) ($chapters[$i + 1]['start'] ?? $page->duration)),
                        'url' => $url.'?t='.$start,
                    ];
                }, self::rows($page->chapters), array_keys(self::rows($page->chapters))) ?: null,
                'partOfSeason' => ['@type' => 'PodcastSeason', 'name' => (string) $page->season_name, 'seasonNumber' => (int) $page->season, 'partOfSeries' => $series],
                'associatedMedia' => [
                    '@type' => 'AudioObject',
                    'contentUrl' => (string) $page->audio['url'],
                    'encodingFormat' => (string) ($page->audio['type'] ?? 'audio/mpeg'),
                    'duration' => self::duration((float) $page->duration),
                ],
                'publisher' => ['@id' => $organisation['@id']],
            ];
            $graph[] = [
                '@type' => 'BreadcrumbList',
                '@id' => $url.'#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => (string) $page->series->name, 'item' => $base.'/earnings-calls/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => (string) $page->season_name, 'item' => $base.'/earnings-calls/#'.self::anchor((int) $page->season)],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $page->title, 'item' => $url],
                ],
            ];
        } elseif ($path === '/earnings-calls/about') {
            $graph[] = ['@type' => 'AboutPage', ...$webPage, 'about' => $series];
        } else {
            $graph[] = ['@type' => 'WebPage', ...$webPage];
        }

        return ['@context' => 'https://schema.org', '@graph' => array_map(fn (array $node) => array_filter($node, fn ($v) => $v !== null), $graph)];
    }

    /**
     * The show's pages in the directories: the same podcast, elsewhere. The site's own listings, and each subscribe
     * link the channel offers that is a web page of its own (a YouTube playlist, an Apple Podcasts page), not one that
     * hands an app the feed (a {feed}, {host} or {title} template, or an app's own scheme).
     *
     * @return array<string, list<string>>
     */
    private static function listings(mixed $show, mixed $subscribeLinks): array
    {
        $offered = array_column(self::rows($subscribeLinks), 'link');
        $pages = array_filter($offered, fn ($link) => is_string($link) && preg_match('#^https://#', $link) && ! str_contains($link, '{'));
        $listings = array_values(array_unique(array_filter([...self::rows($show->listings ?? []), ...$pages])));

        return $listings === [] ? [] : ['sameAs' => $listings];
    }

    /**
     * Seconds as an ISO 8601 duration (PT11M33S).
     */
    private static function duration(float $seconds): string
    {
        $seconds = (int) round($seconds);

        return 'PT'.(intdiv($seconds, 60) > 0 ? intdiv($seconds, 60).'M' : '').($seconds % 60).'S';
    }

    /**
     * A quarter's anchor on the calls page (#q2-2005).
     */
    private static function anchor(int $season): string
    {
        return 'q'.((($season - 1) % 4) + 1).'-'.(2005 + intdiv($season - 1, 4));
    }

    /**
     * @return array<mixed>
     */
    private static function rows(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return is_array($value) ? $value : (array) json_decode((string) json_encode($value), true);
    }
}
