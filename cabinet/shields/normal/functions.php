<?php

// Your custom functions goes here ...

Widget::add('tagLinks', function($connector = ', ') {
    $config = Config::get();
    $speak = Config::speak();
    $links = array();
    $source = $config->article->tags;
    if( ! isset($source) || ! is_object($source)) return "";
    foreach($source as $tag) {
        if($tag && $tag->id !== 0) {
            $links[] = '<a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag->slug . '" rel="tag">' . $tag->name . '</a>';
        }
    }
    return ! empty($links) ? $speak->tags . ': ' . implode($connector, $links) : "";
});