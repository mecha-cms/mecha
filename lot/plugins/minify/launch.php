<?php

function do_minify($content) {
    global $config;
    $c_minify = $config->states->{'plugin_' . md5(File::B(__DIR__))};
    $url = $config->protocol . $config->host;
    // Minify URL
    if(isset($c_minify->url_minify) && Text::check($content)->has('="' . $url)) {
        Config::set('url_minify', true);
        $content = str_replace(
            array(
                '="' . $url . '"',
                '="' . $url . '/',
                '="' . $url . '?',
                '="' . $url . '#'
            ),
            array(
                '="/"',
                '="/',
                '="?',
                '="#'
            ),
        $content);
    }
    // Minify Embedded CSS
    if(isset($c_minify->css_minify)) {
        Config::set('css_minify', true);
        $content = preg_replace_callback('#<style(>| .*?>)([\s\S]*?)<\/style>#i', function($matches) use($config, $c_minify, $url) {
            $css = Converter::detractShell($matches[2]);
            if(isset($c_minify->url_minify)) {
                $css = preg_replace('#(?<=[\s:])(src|url)\(' . preg_quote($url, '/') . '#', '$1(', $css);
            }
            return '<style' . $matches[1] . $css . '</style>';
        }, $content);
    }
    // Minify Embedded JavaScript
    if(isset($c_minify->js_minify)) {
        Config::set('js_minify', true);
        $content = preg_replace_callback('#<script(>| .*?>)([\s\S]*?)<\/script>#i', function($matches) {
            $js = Converter::detractSword($matches[2]);
            return '<script' . $matches[1] . $js . '</script>';
        }, $content);
    }
    // Minify HTML
    if(isset($c_minify->html_minify)) {
        Config::set('html_minify', true);
        $content = Converter::detractSkeleton($content);
    }
    return $content;
}

if($config->url_path === $config->manager->slug . '/login' || $config->page_type !== 'manager') {
    // Apply `do_minify` filter
    Filter::add('shield:input', 'do_minify', 1);
}