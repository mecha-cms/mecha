<?php

// DEPRECATED. Please use `Page::text($text)`
Text::plug('toPage', function($text, $content = 'content', $FP = 'page:', $results = array(), $data = array()) {
    return call_user_func_array('Page::text', func_get_args());
});

// DEPRECATED. Please use `Converter::toArray($text)`
Text::plug('toArray', function($text, $s = S, $indent = '    ') {
    return call_user_func_array('Converter::toArray', func_get_args());
});