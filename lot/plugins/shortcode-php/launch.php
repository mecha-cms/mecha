<?php

function do_shortcode_php($content) {
    if(strpos($content, '{{php}}') === false) return $content;
    global $config, $speak;
    return preg_replace_callback('#(?<!`)\{\{php\}\}(?!`)([\s\S]*?)(?<!`)\{\{\/php\}\}(?!`)#', function($matches) use($config, $speak) {
        return Converter::phpEval($matches[1], array(
            'config' => $config,
            'speak' => $speak
        ));
    }, $content);
}

if( ! Guardian::happy() || Guardian::happy(1)) {
    Filter::add('shortcode', 'do_shortcode_php', 20.1);
}