<?php

$bucket = array();

if($config->total_pages > 0) {
    foreach(Get::extract('pages') as $page) {
        $bucket[] = array(
            'url' => $config->url . '/' . $page['slug'],
            'date' => Date::format($page['time'], 'c'),
            'changefreq' => 'weekly',
            'priority' => (string) '0.5'
        );
    }
}

if($config->total_articles > 0) {
    foreach(Get::extract('articles') as $article) {
        $bucket[] = array(
            'url' => $config->url . '/' . $config->index->slug . '/' . $article['slug'],
            'date' => Date::format($article['time'], 'c'),
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