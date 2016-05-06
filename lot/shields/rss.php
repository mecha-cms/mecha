<?php

HTTP::mime('text/xml', $config->charset);

$s = Request::get('scope', 'article');

$rss = array(
    'meta' => array(
        'generator' => 'Mecha ' . MECHA_VERSION,
        'title' => $config->title,
        'slogan' => $config->slogan,
        'url' => array(
            'home' => $config->url,
            'prev' => $pager->prev->anchor ? $pager->prev->url : null,
            'next' => $pager->next->anchor ? $pager->next->url : null,
            'current' => $config->url_current
        ),
        'description' => $config->description,
        'update' => date('c'),
        'author' => (array) $config->author,
        'offset' => $config->offset,
        'total' => $config->{'total_' . $s . 's'},
        'tags' => call_user_func('Get::' . $s . 'Tags')
    ),
    'item' => ! empty($config->{$s . 's'}) ? Mecha::A($config->{$s . 's'}) : null
);

$rss = Filter::colon($s . ':rss', $rss);

echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '<channel>';
echo '<generator>' . $rss['meta']['generator'] . '</generator>';
echo '<title>' . $rss['meta']['title'] . '</title>';
echo '<link>' . $rss['meta']['url']['home'] . '/</link>';
echo '<description>' . $rss['meta']['description'] . '</description>';
echo '<lastBuildDate>' . Date::format($rss['meta']['update'], 'r') . '</lastBuildDate>';
echo '<atom:link rel="self" href="' . $rss['meta']['url']['current'] . '"/>';
echo $rss['meta']['url']['prev'] ? '<atom:link rel="previous" href="' . $rss['meta']['url']['prev'] . '"/>' : "";
echo $rss['meta']['url']['next'] ? '<atom:link rel="next" href="' . $rss['meta']['url']['next'] . '"/>' : "";

if( ! empty($rss['item'])) {
    foreach($rss['item'] as $k => $v) {
        echo '<item>';
        echo '<title><![CDATA[' . $v['title'] . ']]></title>';
        echo '<link>' . $v['url'] . '</link>';
        echo '<description><![CDATA[' . $v['description'] . ']]></description>';
        echo '<pubDate>' . Date::format($v['time'], 'r') . '</pubDate>';
        echo '<guid>' . $v['url'] . '</guid>';
        foreach($v['kind'] as $vv) {
            if($vv = call_user_func('Get::' . $s . 'Tag', 'id:' . $vv)) {
                echo '<category domain="' . Filter::colon('tag:url', $config->url . '/' . $config->tag->slug . '/' . $vv->slug) . '">' . $vv->name . '</category>';
            }
        }
        echo '<source url="' . $v['url'] . '"><![CDATA[' . $rss['meta']['title'] . ': ' . $v['title'] . ']]></source>';
        echo '</item>';
    }
}

echo '</channel>';
echo '</rss>';