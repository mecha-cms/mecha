<?php

$bucket = array();

if($pages = Mecha::eat(Get::articles())->chunk($config->offset, 25)->vomit()) {
    foreach($pages as $path) {
        $bucket[] = Get::articleHeader($path);
    }
}

$germs = array('#&\#?[a-z0-9]{2,8}\;#i');

echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo '<feed xmlns="http://www.w3.org/2005/Atom">';
echo '<title>' . $config->title . '</title>';
echo '<link href="' . $config->url . '"/>';
echo '<link rel="self" href="' . $config->url_current . '"/>';
echo $config->offset > 1 ? '<link rel="previous" href="' . $config->url . '/feeds/rss/' . ($config->offset - 1) . '"/>' : "";
echo $config->offset < ceil($config->total_articles / 25) ? '<link rel="next" href="' . $config->url . '/feeds/rss/' . ($config->offset + 1) . '"/>' : "";
echo '<updated>' . date('c') . '</updated>';
echo '<author><name>' . $config->author . '</name></author>';
echo '<id>' . $config->url . '/</id>';

if( ! empty($bucket)) {
    foreach($bucket as $item) {
        echo '<entry>';
        echo '<title>' . preg_replace($germs, "", strip_tags($item->title)) . '</title>';
        echo '<link href="' . $item->url . '"/>';
        echo '<id>' . $item->url . '</id>';
        echo '<updated>' . Date::format($item->update, 'c') . '</updated>';
        echo '<summary>' . preg_replace($germs, "", strip_tags($item->description)) . '</summary>';
        echo '</entry>';
    }
}

echo '</feed>';