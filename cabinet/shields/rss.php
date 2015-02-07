<?php

$bucket = array();
$r = array('#&\#?[a-z0-9]{2,8}\;#i');

if($pages = Mecha::eat(Get::articles())->chunk($config->offset, 25)->vomit()) {
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
echo $config->offset > 1 ? '<atom:link rel="previous" href="' . $config->url . '/feeds/rss/' . ($config->offset - 1) . '"/>' : "";
echo $config->offset < ceil($config->total_articles / 25) ? '<atom:link rel="next" href="' . $config->url . '/feeds/rss/' . ($config->offset + 1) . '"/>' : "";
if( ! empty($bucket)) {
    foreach($bucket as $item) {
        $title = preg_replace($r, "", strip_tags($item->title));
        $description = Text::parse(preg_replace($r, "", $item->description), '->encoded_html');
        $kind = Mecha::A($item->kind);
        echo '<item>';
        echo '<title>' . $title . '</title>';
        echo '<link>' . $item->url . '</link>';
        echo '<description>' . $description . '</description>';
        echo '<pubDate>' . Date::format($item->time, 'r') . '</pubDate>';
        echo '<guid>' . $item->url . '</guid>';
        if( ! empty($kind)) {
            foreach($kind as $k) {
                $kind_data = Get::rawTagsBy($k);
                echo '<category domain="' . $config->url . '/' . $config->tag->slug . '/' . $kind_data['slug'] . '">' . $kind_data['name'] . '</category>';
            }
        }
        echo '<source url="' . $config->url . '/feeds/rss">' . $config->title . ': ' . $title . '</source>';
        echo '</item>';
    }
}
echo '</channel>';
echo '</rss>';