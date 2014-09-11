<?php

Weapon::add('meta', function() {
    $config = Config::get();
    echo str_repeat(TAB, 2) . '<!-- Start Facebook Open Graph -->' . NL;
    echo str_repeat(TAB, 2) . '<meta property="og:title" content="' . strip_tags($config->page_title) . '"' . ES . NL;
    echo str_repeat(TAB, 2) . '<meta property="og:type" content="' . ($config->page_type == 'article' ? 'article' : 'website') . '"' . ES . NL;
    echo str_repeat(TAB, 2) . '<meta property="og:url" content="' . $config->url_current . '"' . ES . NL;
    if(isset($config->article->image)) {
        echo str_repeat(TAB, 2) . '<meta property="og:image" content="' . $config->article->image . '"' . ES . NL;
    } elseif(isset($config->page->image)) {
        echo str_repeat(TAB, 2) . '<meta property="og:image" content="' . $config->page->image . '"' . ES . NL;
    }
    echo str_repeat(TAB, 2) . '<meta property="og:site_name" content="' . $config->title . '"' . ES . NL;
    if(isset($config->article->description)) {
        echo str_repeat(TAB, 2) . '<meta property="og:description" content="' . strip_tags($config->article->description) . '"' . ES . NL;
    } elseif(isset($config->page->description)) {
        echo str_repeat(TAB, 2) . '<meta property="og:description" content="' . strip_tags($config->page->description) . '"' . ES . NL;
    } else {
        echo str_repeat(TAB, 2) . '<meta property="og:description" content="' . strip_tags($config->description) . '"' . ES . NL;
    }
    echo str_repeat(TAB, 2) . '<!-- End Facebook Open Graph -->' . NL;
}, 11);