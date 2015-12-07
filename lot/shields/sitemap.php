<?php

$bucket = array();

if($config->total_pages > 0) {
    foreach(Get::pages() as $page) {
        list($time, $kind, $slug) = explode('_', File::N($page), 3);
        $bucket[] = (object) array(
            'url' => Filter::colon('page:url', $config->url . '/' . $slug),
            'date' => Filter::colon('page:time', Date::format($time, 'c')),
            'changefreq' => 'weekly',
            'priority' => (string) '0.5'
        );
    }
}

if($config->total_articles > 0) {
    foreach(Get::articles() as $article) {
        list($time, $kind, $slug) = explode('_', File::N($article), 3);
        $bucket[] = (object) array(
            'url' => Filter::colon('article:url', $config->url . '/' . $config->index->slug . '/' . $slug),
            'date' => Filter::colon('article:time', Date::format($time, 'c')),
            'changefreq' => 'weekly',
            'priority' => (string) '1.0'
        );
    }
}

echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

if( ! empty($bucket)) {
    foreach($bucket as $i => $item) {
        echo '<url>';
        echo '<loc>' . $item->url . '</loc>';
        echo '<lastmod>' . $item->date . '</lastmod>';
        echo '<changefreq>' . $item->changefreq . '</changefreq>';
        echo '<priority>' . $item->priority . '</priority>';
        Weapon::fire('sitemap_item', array($item, $i));
        echo '</url>';
    }
}

echo '</urlset>';