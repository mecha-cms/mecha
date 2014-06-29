<?php

// Your custom functions goes here ...

function article_tag_links($connector) {
    $config = Config::get();
    $speak = Config::speak();
    $source = $config->article->tags;
    if(is_object($source) && ! empty($source)) {
        $links = array();
        foreach($source as $tag) {
            if($tag && $tag->id !== 0) {
                $links[] = '<a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag->slug . '" rel="tag">' . $tag->name . '</a>';
            }
        }
        return count($links) > 0 ? $speak->tags . ': ' . implode($connector, $links) : "";
    }
    return "";
}