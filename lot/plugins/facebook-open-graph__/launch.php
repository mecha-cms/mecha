<?php

function do_facebook_open_graph() {
    $config = Config::get();
    $T2 = str_repeat(TAB, 2);
    echo O_BEGIN . $T2 . '<!-- Start Facebook Open Graph -->' . NL;
    echo $T2 . '<meta property="og:title" content="' . Text::parse($config->page_title, '->text') . '"' . ES . NL;
    echo $T2 . '<meta property="og:url" content="' . $config->url_current . '"' . ES . NL;
    if($config->page_type && isset($config->{$config->page_type}->description)) {
        $config->description = $config->{$config->page_type}->description;
    }
    echo $T2 . '<meta property="og:description" content="' . Text::parse($config->description, '->text') . '"' . ES . NL;
    if($config->page_type && $config->page_type !== '404' && isset($config->{$config->page_type}->image)) {
        echo $T2 . '<meta property="og:image" content="' . $config->{$config->page_type}->image . '"' . ES . NL;
    } else {
        echo $T2 . '<meta property="og:image" content="' . Filter::colon('favicon:url', $config->url . '/favicon.ico') . '"' . ES . NL;
    }
    echo $T2 . '<meta property="og:site_name" content="' . $config->title . '"' . ES . NL;
    echo $T2 . '<meta property="og:type" content="' . ($config->page_type === 'article' ? 'article' : 'website') . '"' . ES . NL;
    echo $T2 . '<!-- End Facebook Open Graph -->' . O_END;
}

Weapon::add('meta', 'do_facebook_open_graph', 11);