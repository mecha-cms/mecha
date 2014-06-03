<?php

// Your custom functions goes here ...

function tag_links($source, $connector) {
    $config = Config::get();
    $speak = Config::speak();
    if(is_object($source) && ! empty($source)) {
        $tag_links = array();
        foreach($source as $tag) {
            if($tag && $tag->id !== 0) {
                $tag_links[] = '<a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag->slug . '" rel="tag">' . $tag->name . '</a>';
            }
        }
        return count($tag_links) > 0 ? $speak->tags . ': ' . implode($connector, $tag_links) : "";
    }
}