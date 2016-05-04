<?php

require __DIR__ . DS . 'workers' . DS . 'parsedown-extra' . DS . 'Parsedown.php';
require __DIR__ . DS . 'workers' . DS . 'parsedown-extra' . DS . 'ParsedownExtra.php';
require __DIR__ . DS . 'workers' . DS . 'parsedown-extra' . DS . 'ParsedownExtraPlugin.php';

function do_parse_markdown($input) {
    if( ! is_string($input)) return $input;
    global $config;
    $c_markdown = Config::get('states.plugin_' . md5(File::B(__DIR__)), array());
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

// Re-write `Text::parse($input, '->html')` parser
Text::parser('to_html', 'do_parse_markdown');

// Apply `do_parse` filter
Filter::add(array('content', 'message'), function($content, $header = array()) {
    $header = (object) $header;
    if( ! isset($header->content_type) || $header->content_type === 'Markdown') {
        return do_parse_markdown($content);
    }
    return $content;
}, 1);

// Set new `html_parser` type
Config::merge('html_parser.type', array(
    'Markdown' => 'Parsedown Extra'
));

// refresh ...
$config = Config::get();