<?php

$bucket = array();

if($config->total_pages > 0) {
    foreach(Get::pages() as $page) {
        list($time, $kind, $slug) = explode('_', basename($page, '.' . pathinfo($page, PATHINFO_EXTENSION)));
        $bucket[] = array(
            'url' => $config->url . '/' . $slug,
            'date' => Date::format($time, 'c'),
            'changefreq' => 'weekly',
            'priority' => (string) '0.5'
        );
    }
}

if($config->total_articles > 0) {
    foreach(Get::articles() as $article) {
        list($time, $kind, $slug) = explode('_', basename($article, '.' . pathinfo($article, PATHINFO_EXTENSION)));
        $bucket[] = array(
            'url' => $config->url . '/' . $config->index->slug . '/' . $slug,
            'date' => Date::format($time, 'c'),
            'changefreq' => 'weekly',
            'priority' => (string) '1.0'
        );
    }
}

if(empty($bucket)) exit;

echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach($bucket as $item) {
    echo '<url>';
    echo '<loc>' . $item['url'] . '</loc>';
    echo '<lastmod>' . $item['date'] . '</lastmod>';
    echo '<changefreq>' . $item['changefreq'] . '</changefreq>';
    echo '<priority>' . $item['priority'] . '</priority>';
    echo '</url>';
}

echo '</urlset>';