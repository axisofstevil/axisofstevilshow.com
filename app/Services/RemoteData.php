<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Michelf\MarkdownExtra;
use RuntimeException;
use TightenCo\Jigsaw\Jigsaw;

/**
 * The show's channel: its released episodes, each with its place in the run,
 * audio, chapters, exhibits, transcript and the works it reads, and the
 * channel's redirects (the old site's addresses), read once before the build.
 *
 * The channel must answer: a missing URL, an error or something other than
 * JSON fails the build, so a broken feed never deploys an empty site. Before
 * launch it has no released episodes, and the site says the first call is
 * coming.
 */
class RemoteData
{
    private Client $api;

    /** @var array<string, bool> Each series folder: whether its plates' web copies are on the archive. */
    private array $webPlates = [];

    public function __construct()
    {
        $this->api = new Client;
    }

    public function handle(Jigsaw $jigsaw): void
    {
        $mainUrl = $jigsaw->getConfig('channel_url');

        if (! is_string($mainUrl) || trim($mainUrl) === '') {
            throw new RuntimeException('CHANNEL_URL is not set: not building the show without its channel.');
        }

        // One address: the site's own channel. Its linked channels say the rest: the featured one is the series the
        // site builds (its episodes, pages and settings, from the link's api_url). An address that answers without
        // links (or links nothing) is a series' own channel, as before the site had one, and the site builds from it alone.
        $mainUrl = rtrim($mainUrl, '/');
        $main = $this->json($mainUrl)['data'] ?? [];
        $siteUrl = null;
        $channelUrl = $mainUrl;

        if (is_array($main['linked'] ?? null) && $main['linked'] !== []) {
            $links = $main['linked'];
            $featured = array_values(array_filter($links, fn (array $link) => ! empty($link['featured'])))[0] ?? null;
            if ($featured === null || empty($featured['api_url'])) {
                throw new RuntimeException("{$mainUrl} features no series: link one and feature it on the channel.");
            }
            $siteUrl = $mainUrl;
            $channelUrl = rtrim((string) $featured['api_url'], '/');
        }
        // The site's series as its channel lists them (llms.txt names each), and the site's own description (the
        // website's, in its structured data); none when the site builds from a series alone.
        $jigsaw->setConfig('linked', array_map(fn (array $link) => [
            'name' => (string) $link['name'],
            'site_url' => (string) ($link['site_url'] ?? ''),
            'featured' => ! empty($link['featured']),
        ], $siteUrl !== null ? $main['linked'] : []));
        $siteDescription = $main['description'] ?? null;
        $jigsaw->setConfig('site_description', $siteUrl !== null && is_string($siteDescription) && trim($siteDescription) !== '' ? trim($siteDescription) : null);
        $zone = (string) $jigsaw->getConfig('timezone');
        $upcoming = $this->json($channelUrl.'/upcoming')['data'] ?? [];

        $episodes = array_values(array_filter(array_map(
            fn (array $entry) => $this->episode($entry, $zone),
            $this->all($channelUrl.'/entries?per_page=100'),
        )));

        // The run's order: by number, which is quarter then part.
        usort($episodes, fn (array $a, array $b) => $a['number'] <=> $b['number']);

        foreach ($episodes as $i => &$episode) {
            $episode['previous'] = $i > 0 ? self::neighbour($episodes[$i - 1]) : null;
            $episode['next'] = $episodes[$i + 1] ?? null ? self::neighbour($episodes[$i + 1]) : null;
        }
        unset($episode);

        $quarters = [];

        foreach ($episodes as $episode) {
            $quarters[$episode['season']] ??= ['season' => $episode['season'], 'name' => $episode['season_name'], 'year' => $episode['year'], 'episodes' => []];
            $quarters[$episode['season']]['episodes'][] = $episode;
        }

        // The cover's chart: calls per quarter of the whole run (Q1 '05 to Q2 '07), released and scheduled.
        $chart = [];

        for ($season = 1; $season <= 10; $season++) {
            $chart[] = [
                'season' => $season,
                'released' => count(array_filter($episodes, fn (array $e) => $e['season'] === $season)),
                'scheduled' => count(array_filter($upcoming, fn (array $u) => (int) $u['season'] === $season)),
            ];
        }

        $jigsaw->setConfig('chart', $chart);
        $jigsaw->setConfig('figures', [
            'calls' => count($episodes),
            'quarters' => count(array_unique(array_column($episodes, 'season'))),
            'minutes' => (int) round(array_sum(array_column($episodes, 'duration')) / 60),
        ]);

        $latest = $episodes === [] ? null : array_reduce($episodes, fn (?array $carry, array $e) => $carry === null || $e['released_at'] > $carry['released_at'] ? $e : $carry);

        $collections = $jigsaw->getConfig('collections');
        $collections->set('episodes.items', $episodes);
        $jigsaw->setConfig('collections', $collections);
        $jigsaw->setConfig('episodes', $episodes);
        $jigsaw->setConfig('quarters', array_values($quarters));
        $jigsaw->setConfig('latest', $latest);
        // The playlist every page carries, for the player: each episode's audio and chapters, in the run's order.
        $jigsaw->setConfig('playlist', array_map(fn (array $e) => [
            'slug' => $e['slug'],
            'title' => $e['title'],
            'label' => $e['label'],
            'url' => '/earnings-calls/episodes/'.$e['slug'].'/',
            'audio' => $e['audio']['url'],
            'duration' => $e['duration'],
            'chapters' => array_map(fn (array $c) => ['start' => $c['start'], 'title' => $c['title'], 'exhibit' => $c['exhibit'], 'image' => $c['image']], $e['chapters']),
            // The packet, for the dock's exhibits drawer: each exhibit's number, title, picture and its work on the archive.
            'exhibits' => array_map(fn (array $x) => ['number' => (int) $x['number'], 'title' => ucfirst((string) $x['title']), 'image' => $x['image'], 'alt' => $x['alt'] ?? '', 'work' => $x['work']['url'] ?? null], $e['exhibits']),
        ], $episodes));

        // Where to subscribe, as the series' channel keeps it: each app's name, devices and link (a template with {feed},
        // {host} and {title}, or the app's listing of the series). A channel that doesn't serve it yet answers 404, and the
        // site's own list (show.apps) stands in.
        $jigsaw->setConfig('subscribe_links', $this->optional($channelUrl.'/subscribe-links'));

        // The series' own: its description (the feed's, and its About page's opening; it replaces the site's copy in
        // show.description, which everything reads), its letter and its About page's sections, each by path.
        $series = $this->optional($channelUrl, single: true);
        if (is_string($series['cover'] ?? null) && trim($series['cover']) !== '') {
            $jigsaw->setConfig('cover', trim($series['cover']));
        }
        if (is_string($series['description'] ?? null) && trim($series['description']) !== '') {
            $jigsaw->setConfig('show', [...$this->rows($jigsaw->getConfig('show')), 'description' => trim($series['description'])]);
        }
        $intro = $series['introduction'] ?? null;
        $jigsaw->setConfig('introduction', is_string($intro) && trim($intro) !== '' ? MarkdownExtra::defaultTransform(trim($intro)) : null);
        $own = $this->pages($channelUrl);
        $letter = $own['letter'] ?? null;
        $jigsaw->setConfig('letter', $letter === null ? null : ['title' => (string) $letter['title'], ...$this->letter((string) ($letter['body'] ?? ''))]);
        $about = $own['about'] ?? null;
        $jigsaw->setConfig('about', $about === null ? null : ['content' => MarkdownExtra::defaultTransform((string) ($about['body'] ?? ''))]);

        // The site's own pages, kept by path on the site's channel (the series' channel stands in until there
        // is one): llms.txt opens /llms.txt in the owner's words (App\Services\LlmsFile); error is /error.html and 404
        // is /404.html (source/error.blade.php, source/404.blade.php).
        $pages = $siteUrl !== null ? $this->pages($siteUrl) : $own;

        // The forwards (the old site's /show/ pages, any address that moves) are the site's: its channel's redirects,
        // the series' channel's until the site has one. A channel without redirects answers 404: none.
        $jigsaw->setConfig('redirects', $this->optional(($siteUrl ?? $channelUrl).'/redirects') ?? []);

        // The footer's lines are the featured series' own: its tagline before the site's "Est.", its sign-off after the
        // site's copyright.
        foreach (['tagline', 'sign_off'] as $line) {
            $value = $series[$line] ?? null;
            $jigsaw->setConfig($line, is_string($value) && trim($value) !== '' ? trim($value) : null);
        }
        $jigsaw->setConfig('llms', $pages['llms.txt']['body'] ?? null);
        foreach (['error' => 'error', 'not_found' => '404'] as $key => $path) {
            $page = $pages[$path] ?? null;
            $jigsaw->setConfig($key, $page === null ? null : [
                'title' => (string) $page['title'],
                'summary' => $page['summary'] ?? null,
                'content' => MarkdownExtra::defaultTransform((string) ($page['body'] ?? '')),
            ]);
        }
        // The next call, teased by title and time (none between a release and the next scheduling); its time in the forms
        // the pages use: in full, as the notice's date and hour, short (a row of the run), and the day alone (a figure).
        $jigsaw->setConfig('upcoming', array_map(function (array $u) use ($zone) {
            $at = CarbonImmutable::parse($u['releases_at'])->setTimezone($zone);

            return [
                ...$u,
                'label' => "{$u['season_name']}, Part {$u['part']}",
                'when' => $at->format('l, F j, g:i A T'),
                'date' => $at->format('l, F j, Y'),
                'time' => $at->format('g:i A').' Eastern',
                'short' => $at->format('D, M j · g:i A'),
                'day' => $at->format('M j'),
            ];
        }, $upcoming));
    }

    /**
     * An entry as the site's episode page reads it (null for one without an episode).
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>|null
     */
    private function episode(array $entry, string $zone): ?array
    {
        $episode = $entry['episode'] ?? null;

        if (! is_array($episode) || empty($episode['audio']['url'])) {
            return null;
        }

        $released = CarbonImmutable::parse((string) $entry['released_at'])->setTimezone($zone);
        // "Q2 '05": the quarter's year in full, for the run's headings.
        $year = preg_match("/'(\\d\\d)$/", (string) $episode['season_name'], $m) ? 2000 + (int) $m[1] : null;
        $exhibits = $episode['exhibits'] ?? [];
        $chapters = $this->chapters($episode['chapters'] ?? [], $exhibits);

        return [
            'id' => $entry['id'],
            'slug' => $entry['slug'],
            'title' => $entry['title'],
            'notes' => MarkdownExtra::defaultTransform((string) $entry['body']),
            'notes_text' => trim(preg_replace('/\s+/', ' ', strip_tags(MarkdownExtra::defaultTransform((string) $entry['body'])))),
            'number' => (int) $episode['number'],
            'season' => (int) $episode['season'],
            'season_name' => (string) $episode['season_name'],
            'year' => $year,
            'part' => (int) $episode['part'],
            'label' => "{$episode['season_name']}, Part {$episode['part']}",
            'duration' => (float) $episode['duration'],
            'audio' => $episode['audio'],
            'chapters_url' => $episode['chapters_url'] ?? null,
            'transcript_url' => $episode['transcript_url'] ?? null,
            'transcript' => $this->transcript((array) ($episode['transcript'] ?? []), $chapters),
            'chapters' => $chapters,
            'exhibits' => $exhibits,
            'works' => array_values(array_filter($episode['works'] ?? [])),
            'released_at' => $released->toIso8601String(),
            'released_on' => $released->format('F j, Y'),
            'released_rfc' => $released->toRfc2822String(),
        ];
    }

    /**
     * The chapters, each with the exhibit its picture is (null when it is a chapter plate, or a picture that is no
     * exhibit): the packet shows that exhibit while the chapter plays, else the chapter's own picture.
     *
     * @param  list<array<string, mixed>>  $chapters
     * @param  list<array<string, mixed>>  $exhibits
     * @return list<array<string, mixed>>
     */
    private function chapters(array $chapters, array $exhibits): array
    {
        $byImage = [];

        foreach ($exhibits as $exhibit) {
            $byImage[$exhibit['image']] ??= (int) $exhibit['number'];
        }

        return array_values(array_map(function (array $chapter, int $i) use ($byImage): array {

            return [
                'position' => $i + 1,
                'start' => (float) $chapter['start'],
                'clock' => sprintf('%d:%02d', intdiv((int) $chapter['start'], 60), (int) $chapter['start'] % 60),
                'title' => (string) $chapter['title'],
                'type' => (string) $chapter['type'],
                'image' => $this->web((string) $chapter['image']),
                'work' => $chapter['work'] ?? null,
                // The exhibit this chapter's picture is, if any; without one, the packet shows the chapter's own picture.
                'exhibit' => $byImage[$chapter['image']] ?? null,
            ];
        }, $chapters, array_keys($chapters)));
    }

    /**
     * The transcript by chapter: its timed paragraphs (the WebVTT's cues, from the channel) grouped under the chapter
     * each begins in. A chapter with no words (the hold music) has no part.
     *
     * @param  list<array{start: float, end: float, text: string}>  $cues
     * @param  list<array<string, mixed>>  $chapters
     * @return list<array{title: string, start: float, paragraphs: list<array{start: float, end: float, text: string}>}>
     */
    private function transcript(array $cues, array $chapters): array
    {
        $sections = [];

        foreach ($chapters as $i => $chapter) {
            $from = (float) $chapter['start'];
            $to = (float) ($chapters[$i + 1]['start'] ?? PHP_FLOAT_MAX);
            $paragraphs = array_values(array_filter($cues, fn (array $cue) => $cue['start'] >= $from - 0.05 && $cue['start'] < $to - 0.05));

            if ($paragraphs !== []) {
                $sections[] = ['title' => (string) $chapter['title'], 'start' => $from, 'paragraphs' => $paragraphs];
            }
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $episode
     * @return array{slug: string, title: string, label: string}
     */
    private static function neighbour(array $episode): array
    {
        return ['slug' => $episode['slug'], 'title' => $episode['title'], 'label' => $episode['label']];
    }

    /**
     * Every row of a paged list.
     *
     * @return list<array<string, mixed>>
     */
    private function all(string $url): array
    {
        $rows = [];

        while ($url) {
            $page = $this->json($url);
            array_push($rows, ...($page['data'] ?? []));
            $url = $page['links']['next'] ?? null;
        }

        return $rows;
    }

    /**
     * The channel's released pages by path. A channel without pages answers 404: none, and the site's own words stand.
     *
     * @return array<string, array<string, mixed>>
     */
    private function pages(string $channelUrl): array
    {
        $pages = [];
        $rows = $this->optional($channelUrl.'/pages') ?? [];


        foreach ($rows as $row) {
            $pages[trim((string) ($row['path'] ?? ''), '/')] = $row;
        }

        return $pages;
    }

    /**
     * A series' plate as the site shows it: its web copy beside it (plate-{type}-800.webp, 800 px WebP), once those are
     * on the archive (one is checked, once per build); the 1400 px JPEG otherwise. The podcast apps keep the JPEGs
     * (chapters.json). Any other picture is as it is.
     */
    private function web(string $image): string
    {
        if (! preg_match('#^(https://[^/]+/shows/[a-z0-9-]+/)plate-([a-z-]+)\.jpg$#', $image, $m)) {
            return $image;
        }

        $copy = "{$m[1]}plate-{$m[2]}-800.webp";
        $this->webPlates[$m[1]] ??= $this->exists($copy);

        return $this->webPlates[$m[1]] ? $copy : $image;
    }

    /**
     * Whether an address answers (a HEAD request, a few seconds at most).
     */
    private function exists(string $url): bool
    {
        try {
            return $this->api->head($url, ['timeout' => 5, 'http_errors' => false])->getStatusCode() === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * The letter as the home page sets it: its paragraphs, then its signature, the last paragraph opening "— ", kept
     * apart (the page signs it in its own type).
     *
     * @return array{content: string, signed: string|null}
     */
    private function letter(string $body): array
    {
        $body = trim(str_replace(["\r\n", "\r"], "\n", $body));
        $signed = null;
        if (preg_match('/\n\n[—–-]\s*(.+)$/u', $body, $m)) {
            $signed = trim($m[1]);
            $body = trim(substr($body, 0, -strlen($m[0])));
        }

        return ['content' => MarkdownExtra::defaultTransform($body), 'signed' => $signed];
    }

    /**
     * A config value as plain arrays, however it is held.
     *
     * @return array<string, mixed>
     */
    private function rows(mixed $value): array
    {
        return is_array($value) ? $value : (array) json_decode((string) json_encode($value), true);
    }

    /**
     * A list the channel may not serve yet: its rows, or null where it answers 404.
     *
     * @return list<array<string, mixed>>|null
     */
    private function optional(string $url, bool $single = false): ?array
    {
        try {
            $data = $this->json($url)['data'] ?? [];

            return $single || array_is_list($data) ? $data : [$data];
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Guzzle already throws on an error status.
     *
     * @return array<string, mixed>
     */
    private function json(string $url): array
    {
        $body = json_decode((string) $this->api->get($url, ['headers' => ['Accept' => 'application/json']])->getBody(), true);

        if (! is_array($body)) {
            throw new RuntimeException("{$url} did not return JSON.");
        }

        return $body;
    }
}
