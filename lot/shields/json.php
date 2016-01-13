<?php

$bucket = array();
$url_base = $config->url . '/feed/json';
$json_order = strtoupper(Request::get('order', 'DESC'));
$json_filter = Request::get('filter', "");
$json_limit = Request::get('limit', 25);

if($pages = Mecha::eat(Get::articles($json_order, $json_filter))->chunk($config->offset, $json_limit)->vomit()) {
    foreach($pages as $path) {
        $bucket[] = Get::articleHeader($path);
    }
}

$json = array(
    'meta' => array(
        'generator' => 'Mecha ' . MECHA_VERSION,
        'title' => $config->title,
        'url' => array(
            'home' => $config->url,
            'previous' => $config->offset > 1 ? Filter::colon('feed:url', $url_base . '/' . ($config->offset - 1)) : null,
            'next' => $config->offset < ceil($config->total_articles / $json_limit) ? Filter::colon('feed:url', $url_base . '/' . ($config->offset + 1)) : null
        ),
        'description' => $config->description,
        'update' => date('c'),
        'author' => (array) $config->author,
        'offset' => $config->offset,
        'total' => $config->total_articles,
        'tags' => Get::articleTags()
    ),
    'item' => null
);

if( ! empty($bucket)) {
    $json['item'] = array();
    foreach($bucket as $i => $item) {
        $json['item'][$i] = array(
            'title' => $item->title,
            'url' => $item->url,
            'date' => $item->date->W3C,
            'update' => Date::format($item->update, 'c'),
            'id' => $item->id,
            'description' => $item->description,
            'kind' => Mecha::A($item->kind)
        );
        Weapon::fire('json_item', array(&$json['item'][$i], $item, $i));
    }
}

Weapon::fire('json_meta', array(&$json['meta']));

echo (isset($_GET['callback']) ? $_GET['callback'] . '(' : "") . json_encode($json) . (isset($_GET['callback']) ? ');' : "");