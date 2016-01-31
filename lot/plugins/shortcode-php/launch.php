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

// Create a new comment
if( ! Guardian::happy(1)) {
    // Disallow `{{php}}` shortcode in comment to prevent PHP script injection
    Filter::add('request:comment', function($lot) {
        if(isset($lot['message'])) {
            $lot['message'] = str_replace(array('{{php}}', '{{/php}}'), "", $lot['message']);
        }
        return $lot;
    });
}