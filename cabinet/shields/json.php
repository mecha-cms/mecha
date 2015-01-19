<?php

$bucket = array();

if($pages = Mecha::eat(Get::articles())->chunk($config->offset, 25)->vomit()) {
    foreach($pages as $path) {
        $bucket[] = Get::articleHeader($path);
    }
}

$json = array(
    'meta' => array(
        'title' => $config->title,
        'url' => array(
            'home' => $config->url,
            'previous' => $config->offset > 1 ? $config->url . '/feeds/json/' . ($config->offset - 1) : null,
            'next' => $config->offset < ceil($config->total_articles / 25) ? $config->url . '/feeds/json/' . ($config->offset + 1) : null
        ),
        'updated' => date('c'),
        'author' => array(
            'name' => $config->author,
            'url' => array(
                'profile' => $config->author_profile_url
            )
        ),
        'offset' => $config->offset,
        'total' => $config->total_articles
    ),
    'item' => array()
);

if( ! empty($bucket)) {
    foreach($bucket as $item) {
        $json['item'][] = array(
            'title' => $item->title,
            'url' => $item->url,
            'date' => $item->date->W3C,
            'update' => Date::format($item->update, 'c'),
            'id' => $item->id,
            'summary' => $item->description
        );
    }
}

echo (isset($_GET['callback']) ? $_GET['callback'] . '(' : "") . json_encode($json) . (isset($_GET['callback']) ? ');' : "");