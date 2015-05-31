<?php

$bucket = array();
$url_base = rtrim($config->url_current, '\\/-.0123456789');
$rss_order = strtoupper(Request::get('order', 'DESC'));
$rss_filter = Text::parse(Request::get('filter', ""), '->decoded_url');
$rss_limit = Request::get('limit', 25);

$str_replace = array(
    '&cent;' => '¢',
    '&pound;' => '£',
    '&sect;' => '§',
    '&copy;' => '©',
    '&laquo;' => '«',
    '&raquo;' => '»',
    '&reg;' => '®',
    '&deg;' => '°',
    '&plusmn;' => '±',
    '&minus;' => '−',
    '&para;' => '¶',
    '&middot;' => '·',
    '&sup1;' => '¹',
    '&sup2;' => '²',
    '&sup3;' => '³',
    '&frac14;' => '¼',
    '&frac12;' => '½',
    '&frac34;' => '¾',
    '&ndash;' => '–',
    '&mdash;' => '—',
    '&lsquo;' => '‘',
    '&rsquo;' => '’',
    '&sbquo;' => '‚',
    '&ldquo;' => '“',
    '&rdquo;' => '”',
    '&bdquo;' => '„',
    '&dagger;' => '†',
    '&Dagger;' => '‡',
    '&bull;' => '•',
    '&hellip;' => '…',
    '&prime;' => '′',
    '&Prime;' => '″',
    '&euro;' => '€',
    '&trade;' => '™',
    '&asymp;' => '≈',
    '&ne;' => '≠',
    '&le;' => '≤',
    '&ge;' => '≥',
    '&spades;' => '♠',
    '&clubs;' => '♣',
    '&hearts;' => '♥',
    '&diams;' => '♦',
    '&larr;' => '←',
    '&uarr;' => '↑',
    '&rarr;' => '→',
    '&darr;' => '↓',
    '&harr;' => '↔'
);

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
echo $config->offset > 1 ? '<atom:link rel="previous" href="' . $url_base . '/' . ($config->offset - 1) . '"/>' : "";
echo $config->offset < ceil($config->total_articles / $rss_limit) ? '<atom:link rel="next" href="' . $url_base . '/' . ($config->offset + 1) . '"/>' : "";

Weapon::fire('rss_meta');

if( ! empty($bucket)) {
    foreach($bucket as $i => $item) {
        $title = Text::parse(str_replace(array_values($str_replace), array_keys($str_replace), strip_tags($item->title)), '->encoded_html');
        $description = Text::parse(str_replace(array_values($str_replace), array_keys($str_replace), $item->description), '->encoded_html');
        $kind = Mecha::A($item->kind);
        echo '<item>';
        echo '<title>' . $title . '</title>';
        echo '<link>' . $item->url . '</link>';
        echo '<description>' . $description . '</description>';
        echo '<pubDate>' . Date::format($item->time, 'r') . '</pubDate>';
        echo '<guid>' . $item->url . '</guid>';
        if( ! empty($kind)) {
            foreach($kind as $k) {
                $tag = Get::rawTag($k);
                echo '<category domain="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '">' . $tag['name'] . '</category>';
            }
        }
        echo '<source url="' . $item->url . '">' . $config->title . ': ' . $title . '</source>';
        Weapon::fire('rss_item', array($item, $i));
        echo '</item>';
    }
}

echo '</channel>';
echo '</rss>';