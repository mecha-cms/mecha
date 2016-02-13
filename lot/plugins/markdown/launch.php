<?php

use \Michelf\MarkdownExtra;
require __DIR__ . DS . 'workers' . DS . 'Michelf' . DS . 'Markdown.php';
require __DIR__ . DS . 'workers' . DS . 'Michelf' . DS . 'MarkdownExtra.php';

function do_markdown_parse($input) {
    if( ! is_string($input)) return $input;
    global $config;
    $c_markdown = Config::get('states.plugin_' . md5(File::B(__DIR__)), array());
    $parser = new MarkdownExtra;
    $parser->empty_element_suffix = ES;
    // override default configuration value
    foreach($c_markdown as $k => $v) {
        $parser->{$k} = is_object($v) ? (array) $v : $v;
    }
    // do parse input
    $output = $parser->transform($input);
    // add class on table(s)
    $output = str_replace('<table>', '<table class="' . $parser->table_class . '">', $output);
    // modify default footnote class
    $output = str_replace('<div class="footnotes">', '<div class="' . $parser->fn_class . '">', $output);
    // add `rel="nofollow"` attribute on external link(s)
    if(strpos($output, '<a ') !== false) {
        $url = preg_quote($config->url, '/');
        $s = '[a-zA-Z0-9\-_./?\#]';
        $output = preg_replace('#<a href="(?!javascript:|' . $s . '|' . $url . ')#', '<a rel="nofollow" href="', $output);
    }
    return $output;
}

function do_markdown($content, $results = array()) {
    $results = (object) $results;
    if( ! isset($results->content_type) || $results->content_type === 'Markdown') {
        return do_markdown_parse($content);
    }
    return $content;
}

// Re-write `Text::parse($input, '->html')` parser
Text::parser('to_html', 'do_markdown_parse');

// Apply `do_markdown` filter
Filter::add(array('content', 'message'), 'do_markdown', 1);

// Set new `html_parser` type
Config::merge('html_parser.type', array(
    'Markdown' => 'Markdown Extra'
));

// refresh ...
$config = Config::get();

if($config->html_parser->active === 'Markdown') {
    // Re-write `comment_wizard` value
    Config::set('speak.comment_wizard', $speak->__comment_wizard);
    // Create a new comment
    if(Request::method('post') && ! Guardian::happy()) {
        Route::over($config->index->slug . '/(:any)', function() {
            $is_mkd = ! isset($_POST['content_type']) || $_POST['content_type'] === 'Markdown';
            if($is_mkd && isset($_POST['message'])) {
                // **Markdown Extra** does not support syntax to generate `del`, `ins` and `mark` tag
                $_POST['message'] = Text::parse($_POST['message'], '->text', '<br><del><ins><mark>', false);
                // Temporarily disallow image(s) in comment to prevent XSS
                $_POST['message'] = preg_replace('#(\!\[.*?\]\(.*?\))#','`$1`', $_POST['message']);
            }
        });
    }
}