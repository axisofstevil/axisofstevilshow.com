<?php

namespace App\Services;

use TightenCo\Jigsaw\Jigsaw;

/**
 * The sitemap (/sitemap.xml): the home page, the calls, the show's own pages and every call, each call with the day it
 * was released and the pages that list them with the newest release; and robots.txt naming it. The 404 and the error
 * page are not pages.
 */
class Sitemap
{
    public function handle(Jigsaw $jigsaw): void
    {
        $base = rtrim((string) $jigsaw->getConfig('baseUrl'), '/');
        $calls = iterator_to_array($jigsaw->getCollection('episodes') ?? []);
        $newest = array_reduce($calls, fn (?string $carry, $episode) => max($carry ?? '', substr((string) $episode->released_at, 0, 10)), null);

        $urls = [[$base.'/', $newest], [$base.'/earnings-calls/', $newest], [$base.'/earnings-calls/about/', null], [$base.'/earnings-calls/subscribe/', null]];

        foreach ($calls as $episode) {
            $urls[] = [$base.$episode->link(), substr((string) $episode->released_at, 0, 10)];
        }

        $entries = array_map(fn (array $url) => '    <url><loc>'.htmlspecialchars($url[0], ENT_XML1).'</loc>'.($url[1] ? '<lastmod>'.$url[1].'</lastmod>' : '').'</url>', $urls);
        $build = $jigsaw->getDestinationPath();
        file_put_contents($build.'/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".implode("\n", $entries)."\n</urlset>\n");
        // A preview's robots.txt shuts crawlers out; the real site's names its sitemap.
        file_put_contents($build.'/robots.txt', $jigsaw->getConfig('preview')
            ? "User-agent: *\nDisallow: /\n"
            : "User-agent: *\nAllow: /\n\nSitemap: {$base}/sitemap.xml\n");
    }
}
