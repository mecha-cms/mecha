<?php

function do_minify($content) {
    global $config;
    $c = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();
    $url = $config->protocol . $config->host;
    // Minify HTML
    if(isset($c['html_minifier'])) {
        $config->html_minifier = true;
        Config::set('html_minifier', $config->html_minifier);
        $content = Converter::detractSkeleton($content);
    }
    // Minify URL
    if(isset($c['url_minifier']) && Text::check($content)->has('="' . $url)) {
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
    if(isset($c['css_minifier'])) {
        $content = preg_replace_callback('#<style(>| .*?>)([\s\S]*?)<\/style>#i', function($matches) use($config, $c, $url) {
            $css = Converter::detractShell($matches[2]);
            if(isset($c['url_minifier'])) {
                $css = preg_replace('#(?<=[\s:])(src|url)\(' . preg_quote($url, '/') . '#', '$1(', $css);
            }
            return '<style' . $matches[1] . $css . '</style>';
        }, $content);
    }
    // Minify Embedded JavaScript
    if(isset($c['js_minifier'])) {
        $content = preg_replace_callback('#<script(>| .*?>)([\s\S]*?)<\/script>#i', function($matches) {
            $js = Converter::detractSword($matches[2]);
            return '<script' . $matches[1] . $js . '</script>';
        }, $content);
    }
    return $content;
}

if($config->url_path === $config->manager->slug . '/login' || $config->page_type !== 'manager') {
    // Apply `do_minify` filter
    Filter::add('shield:input', 'do_minify', 1);
}