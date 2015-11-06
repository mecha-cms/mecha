<?php

Weapon::add('meta', function() {
    $config = Config::get();
    $T2 = str_repeat(TAB, 2);
    echo O_BEGIN . $T2 . '<!-- Start Facebook Open Graph -->' . NL;
    echo $T2 . '<meta property="og:title" content="' . trim(strip_tags($config->page_title)) . '"' . ES . NL;
    echo $T2 . '<meta property="og:type" content="' . ($config->page_type == 'article' ? 'article' : 'website') . '"' . ES . NL;
    echo $T2 . '<meta property="og:url" content="' . Filter::apply('url', $config->url_current) . '"' . ES . NL;
    if(isset($config->article->image)) {
        echo $T2 . '<meta property="og:image" content="' . Filter::apply('url', $config->article->image) . '"' . ES . NL;
    } else if(isset($config->page->image)) {
        echo $T2 . '<meta property="og:image" content="' . Filter::apply('url', $config->page->image) . '"' . ES . NL;
    }
    echo $T2 . '<meta property="og:site_name" content="' . $config->title . '"' . ES . NL;
    if(isset($config->article->description)) {
        $description = $config->article->description;
    } else if(isset($config->page->description)) {
        $description = $config->page->description;
    } else {
        $description = $config->description;
    }
    echo $T2 . '<meta property="og:description" content="' . trim(strip_tags($description)) . '"' . ES . NL;
    echo $T2 . '<!-- End Facebook Open Graph -->' . O_END;
}, 11);