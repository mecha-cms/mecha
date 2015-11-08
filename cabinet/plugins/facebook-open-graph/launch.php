<?php

Weapon::add('meta', function() {
    $config = Config::get();
    $T2 = str_repeat(TAB, 2);
    echo O_BEGIN . $T2 . '<!-- Start Facebook Open Graph -->' . NL;
    echo $T2 . '<meta property="og:title" content="' . Text::parse($config->page_title, '->text') . '"' . ES . NL;
    echo $T2 . '<meta property="og:type" content="' . ($config->page_type == 'article' ? 'article' : 'website') . '"' . ES . NL;
    echo $T2 . '<meta property="og:url" content="' . Filter::apply('url', $config->url_current) . '"' . ES . NL;
    if(isset($config->{$config->page_type}->image)) {
        echo $T2 . '<meta property="og:image" content="' . Filter::apply('url', $config->{$config->page_type}->image) . '"' . ES . NL;
    }
    echo $T2 . '<meta property="og:site_name" content="' . $config->title . '"' . ES . NL;
    if(isset($config->{$config->page_type}->description)) {
        $config->description = $config->{$config->page_type}->description;
    }
    echo $T2 . '<meta property="og:description" content="' . Text::parse($config->description, '->text') . '"' . ES . NL;
    echo $T2 . '<!-- End Facebook Open Graph -->' . O_END;
}, 11);