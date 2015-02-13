<?php

$bucket = array();$bucket = array();
$url_base = rtrim($config->url_current, '\\/-.0123456789');
$json_order = strtoupper(Request::get('order', 'DESC'));
$json_filter = Text::parse(Request::get('filter', ""), '->decoded_url');
$json_limit = Request::get('limit', 25);

if($pages = Mecha::eat(Get::articles($json_order, $json_filter))->chunk($config->offset, $json_limit)->vomit()) {
    foreach($pages as $path) {
        $bucket[] = Get::articleHeader($path);
    }
}

$json = array(
    'meta' => array(
        'generator' => 'Mecha CMS ' . MECHA_VERSION,
        'title' => $config->title,
        'url' => array(
            'home' => $config->url,
            'previous' => $config->offset > 1 ? $url_base . '/' . ($config->offset - 1) : null,
            'next' => $config->offset < ceil($config->total_articles / $json_limit) ? $url_base . '/' . ($config->offset + 1) : null
        ),
        'description' => $config->description,
        'update' => date('c'),
        'author' => array(
            'name' => $config->author,
            'url' => array(
                'profile' => $config->author_profile_url
            )
        ),
        'offset' => $config->offset,
        'total' => $config->total_articles,
        'tags' => Get::rawTags()
    ),
    'item' => null
);

if( ! empty($bucket)) {
    $json['item'] = array();
    foreach($bucket as $item) {
        $json['item'][] = array(
            'title' => $item->title,
            'url' => $item->url,
            'date' => $item->date->W3C,
            'update' => Date::format($item->update, 'c'),
            'id' => $item->id,
            'description' => $item->description,
            'kind' => Mecha::A($item->kind)
        );
    }
}

echo (isset($_GET['callback']) ? $_GET['callback'] . '(' : "") . json_encode($json) . (isset($_GET['callback']) ? ');' : "");