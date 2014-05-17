<?php

Weapon::add('before', function() {

    $config = Config::get();

    echo "<!-- Start Facebook Open Graph -->\n";
    echo "    <meta property=\"og:title\" content=\"" . $config->page_title . "\">\n";
    echo "    <meta property=\"og:type\" content=\"website\">\n";
    echo "    <meta property=\"og:url\" content=\"" . $config->url_current . "\">\n";
    if(isset($config->page->image)) echo "    <meta property=\"og:image\" content=\"" . $config->page->image . "\">\n";
    echo "    <meta property=\"og:site_name\" content=\"" . $config->title . "\">\n";
    echo "    <meta property=\"og:description\" content=\"" . Text::parse(isset($config->page->description) ? $config->page->description : $config->description)->to_encoded_html . "\">\n";
    echo "    <!-- End Facebook Open Graph -->\n";

});