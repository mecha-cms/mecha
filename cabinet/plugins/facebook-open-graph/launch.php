<?php

Weapon::add('meta', function() {
    $config = Config::get();
	$indent = str_repeat(TAB, 2);
    echo $indent . '<!-- Start Facebook Open Graph -->' . NL;
    echo $indent . '<meta property="og:title" content="' . strip_tags($config->page_title) . '"' . ES . NL;
    echo $indent . '<meta property="og:type" content="' . ($config->page_type == 'article' ? 'article' : 'website') . '"' . ES . NL;
    echo $indent . '<meta property="og:url" content="' . $config->url_current . '"' . ES . NL;
    if(isset($config->article->image)) {
        echo $indent . '<meta property="og:image" content="' . $config->article->image . '"' . ES . NL;
    } else if(isset($config->page->image)) {
        echo $indent . '<meta property="og:image" content="' . $config->page->image . '"' . ES . NL;
    }
    echo $indent . '<meta property="og:site_name" content="' . $config->title . '"' . ES . NL;
    if(isset($config->article->description)) {
        echo $indent . '<meta property="og:description" content="' . strip_tags($config->article->description) . '"' . ES . NL;
    } else if(isset($config->page->description)) {
        echo $indent . '<meta property="og:description" content="' . strip_tags($config->page->description) . '"' . ES . NL;
    } else {
        echo $indent . '<meta property="og:description" content="' . strip_tags($config->description) . '"' . ES . NL;
    }
    echo $indent . '<!-- End Facebook Open Graph -->' . NL;
}, 11);