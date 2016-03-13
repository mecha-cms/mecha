<?php

require __DIR__ . DS . 'workers' . DS . 'Parsedown' . DS . 'Parsedown.php';
require __DIR__ . DS . 'workers' . DS . 'Parsedown' . DS . 'ParsedownExtra.php';
require __DIR__ . DS . 'workers' . DS . 'ParsedownExtraPlugin' . DS . 'ParsedownExtraPlugin.php';

function do_markdown_parse($input) {
    if( ! is_string($input)) return $input;
    global $config;
    $c_markdown = Config::get('states.plugin_' . md5(File::B(__DIR__)), array());
    $parser = call_user_func(Filter::apply('parser:markdown', 'ParsedownExtraPlugin::instance'));
    $parser->element_suffix = ES;
    $parser->setUrlsLinked(false);
    // do filter ...
    if(Filter::exist('parser:markdown.code_text')) {
        $parser->code_text = function($data) {
            return Filter::apply('parser:markdown.code_text', $data['element']['text'], $data);
        };
    }
    // --ibid
    if(Filter::exist('parser:markdown.code_block_text')) {
        $parser->code_block_text = function($data) {
            return Filter::apply('parser:markdown.code_block_text', $data['element']['text']['text'], $data);
        };
    }
    // override default configuration value
    foreach($c_markdown as $k => $v) {
        if(strpos($k, '__') === 0) continue;
        $parser->{$k} = is_object($v) ? Mecha::A($v) : $v;
    }
    if(isset($c_markdown->__setBreaksEnabled)) {
        $parser->setBreaksEnabled(true);
    }
    if(isset($c_markdown->__setMarkupEscaped)) {
        $parser->setMarkupEscaped(true);
    }
    if(isset($c_markdown->__setUrlsLinked)) {
        $parser->setUrlsLinked(true);
    }
    // do parse input
    return $parser->text($input);
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
    'Markdown' => 'Parsedown Extra'
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
                // **Parsedown Extra** does not support syntax to generate `ins` and `mark` tag
                $_POST['message'] = Text::parse($_POST['message'], '->text', '<br><ins><mark>', false);
                // Temporarily disallow image(s) in comment to prevent XSS
                if(strpos($_POST['message'], '![') !== false) {
                    $_POST['message'] = preg_replace('#(\!\[.*?\](?:\(.*?\)|\[.*?\]))#','`$1`', $_POST['message']);
                }
            }
        });
    }
}