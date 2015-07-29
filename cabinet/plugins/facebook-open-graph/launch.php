<?php

Weapon::add('meta', function() {
    $config = Config::get();
    $indent = str_repeat(TAB, 2);
    echo O_BEGIN . $indent . '<!-- Start Facebook Open Graph -->' . NL;
    echo $indent . '<meta property="og:title" content="' . trim(strip_tags($config->page_title)) . '"' . ES . NL;
    echo $indent . '<meta property="og:type" content="' . ($config->page_type == 'article' ? 'article' : 'website') . '"' . ES . NL;
    echo $indent . '<meta property="og:url" content="' . $config->url_current . '"' . ES . NL;
    if(isset($config->article->image)) {
        echo $indent . '<meta property="og:image" content="' . $config->article->image . '"' . ES . NL;
    } else if(isset($config->page->image)) {
        echo $indent . '<meta property="og:image" content="' . $config->page->image . '"' . ES . NL;
    }
    echo $indent . '<meta property="og:site_name" content="' . $config->title . '"' . ES . NL;
    if(isset($config->article->description)) {
        $description = $config->article->description;
    } else if(isset($config->page->description)) {
        $description = $config->page->description;
    } else {
        $description = $config->description;
    }
    echo $indent . '<meta property="og:description" content="' . trim(strip_tags($description)) . '"' . ES . NL;
    echo $indent . '<!-- End Facebook Open Graph -->' . O_END;
}, 11);