<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use TightenCo\Jigsaw\Jigsaw;

/**
 * The guide for language models (/llms.txt, per llmstxt.org), as the archive's: short enough to read whole. It opens
 * in the owner's own words (the site channel's page at llms.txt; the show's name and description when there is none),
 * then names the show's series (the site channel's linked channels, the featured one described), lists the featured
 * series' released calls with its place in the run and its own pages, points to the archive and the archive's own guide,
 * and ends with the sitemap, the feed and when it was written.
 */
class LlmsFile
{
    public function handle(Jigsaw $jigsaw): void
    {
        $base = rtrim((string) $jigsaw->getConfig('baseUrl'), '/');
        $title = (string) $jigsaw->getConfig('title');
        $archive = rtrim((string) $jigsaw->getConfig('archiveUrl'), '/');
        $episodes = $this->rows($jigsaw->getConfig('episodes'));
        $show = $this->rows($jigsaw->getConfig('show'));
        $series = (string) $jigsaw->getConfig('series')['name'];
        $feed = $base.'/earnings-calls/feed.xml';
        $link = fn (string $name, string $address, string $note = '') => "- [{$name}]({$address})".($note === '' ? '' : ': '.$this->line($note));
        $length = fn (float $s) => sprintf('%d:%02d', intdiv((int) $s, 60), (int) $s % 60);

        // The owner's words as written, tidied: no trailing spaces, list items at the margin, one blank line between blocks.
        $own = trim((string) preg_replace(['/[ \t]+$/m', '/^[ \t]+(?=[-*] )/m', '/\n{3,}/'], ['', '', "\n\n"], str_replace(["\r\n", "\r"], "\n", (string) $jigsaw->getConfig()->get('llms'))));

        $lines = $own !== '' ? [$own, ''] : ['# '.$title, '', '> '.$this->line((string) ($show['description'] ?? '')), ''];

        // The show's series, as its site channel links them: the featured one (the series this site builds) with its
        // description, schedule and feed; any other by its name and address. A build from a series alone names only it.
        $lines[] = '## Series';
        $lines[] = '';
        $linked = $this->rows($jigsaw->getConfig('linked')) ?: [['name' => $series, 'site_url' => '', 'featured' => true]];
        foreach ($linked as $other) {
            $lines[] = $other['featured']
                ? $link($series, $base.'/earnings-calls/', rtrim($this->line((string) ($show['description'] ?? '')), '.').'. Mondays at 6:00 AM Eastern; its feed: '.$feed)
                : $link((string) $other['name'], (string) $other['site_url']);
        }

        // The calls, in the run's order: each its quarter and part, release date and length, and its notes' opening; then
        // the series' own pages.
        $lines[] = '';
        $lines[] = '## '.$series;
        $lines[] = '';
        $first = $this->rows($jigsaw->getConfig('upcoming'))[0] ?? null;
        $lines[] = $link('All the calls', $base.'/earnings-calls/', count($episodes).' released so far, by quarter (Q1 \'05 to Q2 \'07), a few calls to each quarter; one a week, Mondays at 6:00 AM Eastern'.($episodes === [] && $first ? '; the first goes out on '.$first['date'].', at '.$first['time'] : ''));

        foreach ($episodes as $episode) {
            $notes = (string) preg_replace('/^Q\d \'\d\d, Part \d+\.\s*/', '', (string) $episode['notes_text']);
            $lines[] = $link((string) $episode['title'], $base.'/earnings-calls/episodes/'.$episode['slug'].'/', "{$episode['label']}, released {$episode['released_on']}, {$length((float) $episode['duration'])}. ".$this->sentence($notes));
        }

        $lines[] = $link('About the calls', $base.'/earnings-calls/about/', 'the format, the agenda of a call, and the works it reads');
        $lines[] = $link('Subscribe', $base.'/earnings-calls/subscribe/', 'how to follow the series in a podcast app');

        // The archive the show reads, and the archive's own guide, for any question about a work rather than a call.
        $lines[] = '';
        $lines[] = '## The archive';
        $lines[] = '';
        $lines[] = $link('The Axis of Stevil', $archive.'/', 'the archive itself, every work from 2005 to 2007 on its own page; the source of everything the show reads');
        $lines[] = $link('The archive\'s guide', $archive.'/llms.txt', 'its story, its notable works and how it is laid out; read it for any question about a work rather than a call');
        $lines[] = $link('All works', $archive.'/works/', 'every work, by division');
        $lines[] = $link('Issues', $archive.'/issues/', 'the weekly front pages as they ran, March 2005 to May 2007');

        $lines[] = '';
        $lines[] = '## Optional';
        $lines[] = '';
        $lines[] = $link('Sitemap', $base.'/sitemap.xml', 'every page of the site');
        $lines[] = $link($series.' feed', $feed, 'every released call, as RSS with chapters and a WebVTT transcript for each');

        // When this was written: the site is rebuilt every morning from its source of record, so the file is as current as the build.
        $built = CarbonImmutable::now('UTC');
        $lines[] = '';
        $lines[] = '## Currency of this file';
        $lines[] = '';
        $lines[] = 'Generated: '.$built->toIso8601String().' ('.$built->format('l, F j, Y \a\t H:i').' UTC).';
        $lines[] = '';
        $lines[] = 'This file was written when the site was last built, from the show\'s source of record. The site is rebuilt every morning at 6:05 Eastern, just after that day\'s release, and each build replaces this file.';

        file_put_contents($jigsaw->getDestinationPath().'/llms.txt', implode("\n", $lines)."\n");
    }

    /**
     * The first sentence of a text, for a one-line note.
     */
    private function sentence(string $text): string
    {
        $text = $this->line($text);

        return preg_match('/^.+?[.!?]["”’)]?(?=\s|$)/u', $text, $m) ? $m[0] : $text;
    }

    /**
     * A note on one line, its Markdown and any line breaks taken out.
     */
    private function line(string $text): string
    {
        $text = (string) preg_replace(['/!\[[^\]]*\]\([^)]*\)/', '/[*_`#>]+/'], ['', ''], $text);

        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /**
     * A config value as plain arrays, however it is held.
     *
     * @return array<mixed>
     */
    private function rows(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return is_array($value) ? $value : (array) json_decode((string) json_encode($value), true);
    }
}
