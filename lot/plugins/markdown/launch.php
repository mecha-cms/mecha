<?php

require __DIR__ . DS . 'workers' . DS . 'Parsedown' . DS . 'Parsedown.php';
require __DIR__ . DS . 'workers' . DS . 'Parsedown' . DS . 'ParsedownExtra.php';
require __DIR__ . DS . 'workers' . DS . 'ParsedownExtraPlugin' . DS . 'ParsedownExtraPlugin.php';

$c_markdown = Config::get('states.plugin_' . md5(File::B(__DIR__)), array());
$parser = call_user_func(Filter::apply('parser:markdown.instance', 'ParsedownExtraPlugin::instance'));
foreach($c_markdown as $k => $v) {
    if(strpos($k, '__') === 0) continue;
    $parser->{$k} = is_object($v) ? Mecha::A($v) : $v;
}
$parser->element_suffix = ES;
$parser->setBreaksEnabled(isset($c_markdown->__setBreaksEnabled));
$parser->setMarkupEscaped(isset($c_markdown->__setMarkupEscaped));
$parser->setUrlsLinked(isset($c_markdown->__setUrlsLinked));

function do_parse_markdown($input) {
    if( ! is_string($input)) return $input;
    global $config, $parser, $c_markdown;
    $parser = Filter::apply('parser:markdown', $parser, $c_markdown);
    // do parse text to HTML ...
    return $parser->text($input);
}

function do_parse($content, $results = array()) {
    $results = (object) $results;
    if( ! isset($results->content_type) || $results->content_type === 'Markdown') {
        return do_parse_markdown($content);
    }
    return $content;
}

// Re-write `Text::parse($input, '->html')` parser
Text::parser('to_html', 'do_parse_markdown');

// Apply `do_parse` filter
Filter::add(array('content', 'message'), 'do_parse', 1);

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