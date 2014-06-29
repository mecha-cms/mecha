<?php

Weapon::add('meta', function() {
    $config = Config::get();
    echo '<!-- Start Facebook Open Graph -->';
    echo '<meta property="og:title" content="' . strip_tags($config->page_title) . '"' . ES;
    echo '<meta property="og:type" content="website"' . ES;
    echo '<meta property="og:url" content="' . $config->url_current . '"' . ES;
    if(isset($config->article->image)) {
        echo '<meta property="og:image" content="' . $config->article->image . '"' . ES;
    } elseif(isset($config->page->image)) {
        echo '<meta property="og:image" content="' . $config->page->image . '"' . ES;
    }
    echo '<meta property="og:site_name" content="' . $config->title . '"' . ES;
    if(isset($config->article->description)) {
        echo '<meta property="og:description" content="' . Text::parse($config->article->description)->to_encoded_html . '"' . ES;
    } elseif(isset($config->page->description)) {
        echo '<meta property="og:description" content="' . Text::parse($config->page->description)->to_encoded_html . '"' . ES;
    } else {
        echo '<meta property="og:description" content="' . Text::parse($config->description)->to_encoded_html . '"' . ES;
    }
    echo '<!-- End Facebook Open Graph -->';
}, 11);