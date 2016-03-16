<?php

require __DIR__ . DS . 'workers' . DS . 'parsedown-extra' . DS . 'Parsedown.php';
require __DIR__ . DS . 'workers' . DS . 'parsedown-extra' . DS . 'ParsedownExtra.php';
require __DIR__ . DS . 'workers' . DS . 'parsedown-extra' . DS . 'ParsedownExtraPlugin.php';

$c_markdown = Config::get('states.plugin_' . md5(File::B(__DIR__)), array());

function do_parse_markdown($input) {
    if( ! is_string($input)) return $input;
    global $config, $c_markdown;
    $parser = new ParsedownExtraPlugin;
    foreach($c_markdown as $k => $v) {
        if(strpos($k, '__') === 0) continue;
        $parser->{$k} = is_object($v) ? Mecha::A($v) : $v;
    }
    $parser->element_suffix = ES;
    $parser->setBreaksEnabled(isset($c_markdown->__setBreaksEnabled));
    $parser->setMarkupEscaped(isset($c_markdown->__setMarkupEscaped));
    $parser->setUrlsLinked(isset($c_markdown->__setUrlsLinked));
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