<?php

$bucket = array();
$url_base = rtrim($config->url_current, '\\/-.0123456789');
$rss_order = strtoupper(Request::get('order', 'DESC'));
$rss_filter = Request::get('filter', "");
$rss_limit = Request::get('limit', 25);

if($pages = Mecha::eat(Get::articles($rss_order, $rss_filter))->chunk($config->offset, $rss_limit)->vomit()) {
    foreach($pages as $path) {
        $bucket[] = Get::articleHeader($path);
    }
}

echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '<channel>';
echo '<generator>Mecha CMS ' . MECHA_VERSION . '</generator>';
echo '<title>' . $config->title . '</title>';
echo '<link>' . $config->url . '/</link>';
echo '<description>' . $config->description . '</description>';
echo '<lastBuildDate>' . Date::format(time(), 'r') . '</lastBuildDate>';
echo '<atom:link rel="self" href="' . $config->url_current . '"/>';
echo $config->offset > 1 ? '<atom:link rel="previous" href="' . Filter::apply('url', $url_base . '/' . ($config->offset - 1)) . '"/>' : "";
echo $config->offset < ceil($config->total_articles / $rss_limit) ? '<atom:link rel="next" href="' . Filter::apply('url', $url_base . '/' . ($config->offset + 1)) . '"/>' : "";

Weapon::fire('rss_meta');

if( ! empty($bucket)) {
    foreach($bucket as $i => $item) {
        $title = strip_tags($item->title);
        $description = $item->description;
        $kind = Mecha::A($item->kind);
        echo '<item>';
        echo '<title><![CDATA[' . $title . ']]></title>';
        echo '<link>' . $item->url . '</link>';
        echo '<description><![CDATA[' . $description . ']]></description>';
        echo '<pubDate>' . Date::format($item->time, 'r') . '</pubDate>';
        echo '<guid>' . $item->url . '</guid>';
        if( ! empty($kind)) {
            foreach($kind as $k) {
                $tag = Get::rawTag($k);
                echo '<category domain="' . Filter::apply('tag:url', Filter::apply('url', $config->url . '/' . $config->tag->slug . '/' . $tag['slug'])) . '">' . $tag['name'] . '</category>';
            }
        }
        echo '<source url="' . $item->url . '"><![CDATA[' . $config->title . ': ' . $title . ']]></source>';
        Weapon::fire('rss_item', array($item, $i));
        echo '</item>';
    }
}

echo '</channel>';
echo '</rss>';