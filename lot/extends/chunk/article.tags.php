<?php

if( ! empty($article->tags)) {
    $tags = array();
    foreach($article->tags as $tag) {
        if($tag && $tag->id !== 0) {
            $url = Filter::colon('tag:url', $config->url . '/' . $config->tag->slug . '/' . $tag->slug);
            $tags[] = '<a href="' . $url . '" rel="tag">' . $tag->name . '</a>';
        }
    }
    $s = count($tags) > 1 ? $speak->tags : $speak->tag;
    echo ! empty($tags) ? $s . ': ' . implode(', ', $tags) : "";
}