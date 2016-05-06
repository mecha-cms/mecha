<?php

HTTP::mime('application/json', $config->charset);

$s = Request::get('scope', 'article');

$json = array(
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

$fn = ! empty($_GET['callback']) ? $_GET['callback'] : "";

echo ($fn ? $fn . '(' : "") . json_encode(Filter::colon($s . ':json', $json)) . ($fn ? ');' : "");