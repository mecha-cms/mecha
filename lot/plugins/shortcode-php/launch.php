<?php

function do_shortcode_php($content) {
    if(strpos($content, '{{php}}') === false) return $content;
    global $config, $speak;
    return preg_replace_callback('#\{\{php\}\}([\s\S]*?)\{\{\/php\}\}#', function($matches) use($config, $speak) {
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
if(Request::method('post') && ! Guardian::happy(1)) {
    // Disallow `{{php}}` shortcode in comment to prevent PHP script injection
    Route::over($config->index->slug . '/(:any)', function() {
        if(isset($_POST['message'])) {
            $_POST['message'] = str_replace(array('{{php}}', '{{/php}}'), "", $_POST['message']);
        }
    });
}