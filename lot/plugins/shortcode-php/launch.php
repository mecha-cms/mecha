<?php

if( ! Guardian::happy() || Guardian::happy(1)) {
    Filter::add('shortcode', function($content) use($config, $speak) {
        if(strpos($content, '{{php}}') === false) return $content;
        return preg_replace_callback('#(?<!`)\{\{php\}\}(?!`)([\s\S]*?)(?<!`)\{\{\/php\}\}(?!`)#', function($matches) use($config, $speak) {
            return Converter::phpEval($matches[1], array(
                'config' => $config,
                'speak' => $speak
            ));
        }, $content);
    }, 20.1);
}