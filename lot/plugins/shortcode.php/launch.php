<?php

Filter::add('shortcode', function($content) {
    if(strpos($content, '{{php}}') === false) return $content;
    return preg_replace_callback('#(?<!`)\{\{php\}\}(?!`)([\s\S]*?)(?<!`)\{\{\/php\}\}(?!`)#', function($matches) {
        return Converter::phpEval($matches[1]);
    }, $content);
}, 20.1);