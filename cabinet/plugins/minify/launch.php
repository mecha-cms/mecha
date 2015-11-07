<?php

if($config->url_path === $config->manager->slug . '/login' || $config->page_type !== 'manager') {
    Filter::add('shield:input', function($content) use($config) {
        $c = File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'states' . DS . 'config.txt')->unserialize();
        // Minify HTML
        if(isset($c['html_minifier'])) {
            $config->html_minifier = true;
            Config::set('html_minifier', $config->html_minifier);
            $content = Converter::detractSkeleton($content);
        }
        // Minify URL
        if(isset($c['url_minifier'])) {
            if(Text::check($content)->has('="' . $config->url)) {
                $url = $config->protocol . $config->host;
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
        }
        // Minify Embedded CSS
        if(isset($c['css_minifier'])) {
            $content = preg_replace_callback('#<style(>| .*?>)([\s\S]*?)<\/style>#', function($matches) {
                return '<style' . $matches[1] . Converter::detractShell($matches[2]) . '</style>';
            }, $content);
        }
        // Minify Embedded JavaScript
        if(isset($c['js_minifier'])) {
            $content = preg_replace_callback('#<script(>| .*?>)([\s\S]*?)<\/script>#', function($matches) {
                return '<script' . $matches[1] . Converter::detractSword($matches[2]) . '</script>';
            }, $content);
        }
        return $content;
    }, 30);
}