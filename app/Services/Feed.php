<?php

namespace App\Services;

use TightenCo\Jigsaw\Jigsaw;

/**
 * The series' podcast feed (/earnings-calls/feed.xml) the directories read: the series, titled as they list it, then every
 * released episode, newest first (the show is episodic). iTunes tags for
 * Apple and Spotify; Podcasting 2.0 tags for the chapters, the transcript and
 * the season's name. Every episode's description ends with the disclosure:
 * the CFO's voice is synthetic and his remarks were written with assistance
 * from AI (Apple Podcasts' guidelines, 1.11).
 */
class Feed
{
    public const string DISCLOSURE = 'The CFO\'s voice is synthetic, and his remarks were written with assistance from AI. The archive\'s works were written by people.';

    /**
     * The audio's address tagged with its version (?v=, the start of its SHA-256; its size until Alfred sends that), so
     * a replaced file is a new address: Apple Podcasts fetches it afresh, where it would keep its copy of the old one
     * (owner, 2026-11-09). The episode's guid is unchanged, so it stays the same episode.
     */
    public static function versioned(array|\ArrayAccess $audio): string
    {
        $version = isset($audio['sha256']) && $audio['sha256'] !== '' ? substr((string) $audio['sha256'], 0, 10) : (string) $audio['bytes'];

        return $audio['url'].(str_contains((string) $audio['url'], '?') ? '&' : '?').'v='.$version;
    }

    public function handle(Jigsaw $jigsaw): void
    {
        $base = rtrim((string) $jigsaw->getConfig('baseUrl'), '/');
        $show = $jigsaw->getConfig('show');
        // Calls released together (the launch's three, at the same 6:00) would share a date, and apps would order them
        // as they please: each takes one second more than the one before it in the run, so every app sorts them in order.
        $episodes = collect($jigsaw->getConfig('episodes') ?? [])->sortBy('number')->groupBy('released_at')
            ->flatMap(fn ($same) => $same->values()->map(fn ($e, $i) => [...$e, 'released_rfc' => (new \DateTimeImmutable($e['released_at']))->modify("+{$i} seconds")->format(DATE_RFC2822)]))
            ->sortByDesc(fn ($e) => [$e['released_at'], $e['number']])->values();
        $x = fn (?string $text): string => htmlspecialchars((string) $text, ENT_XML1 | ENT_QUOTES);
        $title = (string) $jigsaw->getConfig('series')['title'];
        $home = $base.(string) $jigsaw->getConfig('series')['path'];
        $cover = (string) $jigsaw->getConfig('cover');

        $items = $episodes->map(function ($e) use ($base, $x): string {
            $url = $base.'/earnings-calls/episodes/'.$e['slug'].'/';
            $notes = str_contains($e['notes'], 'synthetic') ? $e['notes'] : $e['notes'].'<p>'.self::DISCLOSURE.'</p>';
            $lines = [
                '<title>'.$x($e['title']).'</title>',
                '<link>'.$x($url).'</link>',
                '<guid isPermaLink="false">'.$x($e['id']).'</guid>',
                '<pubDate>'.$x($e['released_rfc']).'</pubDate>',
                '<description><![CDATA['.$notes.']]></description>',
                '<enclosure url="'.$x(self::versioned($e['audio'])).'" length="'.(int) $e['audio']['bytes'].'" type="'.$x($e['audio']['type'] ?? 'audio/mpeg').'"/>',
                '<itunes:title>'.$x($e['title']).'</itunes:title>',
                '<itunes:duration>'.(int) round($e['duration']).'</itunes:duration>',
                '<itunes:season>'.(int) $e['season'].'</itunes:season>',
                '<itunes:episode>'.(int) $e['part'].'</itunes:episode>',
                '<itunes:episodeType>full</itunes:episodeType>',
                '<itunes:explicit>false</itunes:explicit>',
                '<podcast:season name="'.$x($e['season_name']).'">'.(int) $e['season'].'</podcast:season>',
                '<podcast:episode>'.(int) $e['part'].'</podcast:episode>',
            ];

            if ($e['chapters_url']) {
                $lines[] = '<podcast:chapters url="'.$x($e['chapters_url']).'" type="application/json+chapters"/>';
            }

            if ($e['transcript_url']) {
                $lines[] = '<podcast:transcript url="'.$x($e['transcript_url']).'" type="text/vtt" language="en"/>';
            }

            return "    <item>\n      ".implode("\n      ", $lines)."\n    </item>";
        })->implode("\n");

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:podcast="https://podcastindex.org/namespace/1.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{$x($title)}</title>
    <link>{$x($home)}</link>
    <atom:link href="{$x($base.'/earnings-calls/feed.xml')}" rel="self" type="application/rss+xml"/>
    <language>{$x($show['language'])}</language>
    <description>{$x($show['description'])}</description>
    <itunes:summary>{$x($show['description'])}</itunes:summary>
    <itunes:author>{$x($show['author'])}</itunes:author>
    <itunes:owner>
      <itunes:name>{$x($show['author'])}</itunes:name>
      <itunes:email>{$x($show['owner_email'])}</itunes:email>
    </itunes:owner>
    <itunes:image href="{$x($cover)}"/>
    <image><url>{$x($cover)}</url><title>{$x($title)}</title><link>{$x($home)}</link></image>
    <itunes:category text="{$x($show['category'])}"/>
    <itunes:explicit>false</itunes:explicit>
    <itunes:type>episodic</itunes:type>
    <podcast:txt purpose="ai-disclosure">{$x(self::DISCLOSURE)}</podcast:txt>
{$items}
  </channel>
</rss>

XML;

        @mkdir($jigsaw->getDestinationPath().'/earnings-calls', 0777, true);
        file_put_contents($jigsaw->getDestinationPath().'/earnings-calls/feed.xml', $xml);
    }
}
