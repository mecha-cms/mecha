<?php

$bucket = array();

if($pages = Mecha::eat(Get::articles())->chunk($config->offset, 25)->vomit()) {
    foreach($pages as $path) {
        $bucket[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments', 'fields'));
    }
} else {
    exit;
}

echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo '<rss version="2.0">';
echo '<channel>';
echo '<title>' . $config->title . '</title>';
echo '<description>' . $config->slogan . '</description>';
echo '<link>' . $config->url . '</link>';

foreach($bucket as $item) {
    echo '<item>';
    echo '<title>' . Text::parse($item->title)->to_encoded_html . '</title>';
    echo '<description>' . Text::parse($item->description)->to_encoded_html . '</description>';
    echo '<link>' . $item->url . '</link>';
    echo '<guid>' . $item->id . '</guid>';
    echo '<pubDate>' . $item->date->W3C . '</pubDate>';
    echo '</item>';
}

echo '</channel>';
echo '</rss>';