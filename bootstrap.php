<?php

use App\Services;

/** @var $container \Illuminate\Container\Container */
/** @var $events \TightenCo\Jigsaw\Events\EventBus */
$events->beforeBuild([
    Services\Assets::class,
    Services\RemoteData::class,
]);

$events->afterBuild([
    Services\Feed::class,
    Services\Sitemap::class,
    Services\LlmsFile::class,
    Services\Redirects::class,
    Services\Assets::class,
    Services\Pagefind::class,
]);
