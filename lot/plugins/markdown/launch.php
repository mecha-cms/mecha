<?php

use \Michelf\MarkdownExtra;
require __DIR__ . DS . 'workers' . DS . 'Michelf' . DS . 'Markdown.php';
require __DIR__ . DS . 'workers' . DS . 'Michelf' . DS . 'MarkdownExtra.php';

// Re-write `Text::parse($input, '->html')` parser
Text::parser('to_html', function($input) use($config) {
    if( ! is_string($input)) return $input;
    $s = __DIR__ . DS . 'states' . DS;
    $url = File::open($s . 'url.txt')->read();
    $abbr = File::open($s . 'abbr.txt')->read();
    $parser = new MarkdownExtra;
    $parser->empty_element_suffix = ES;
    $parser->table_align_class_tmpl = 'text-%%'; // table align class, example: `<td class="text-right">`
    return preg_replace(
        array(
            '#<table>#',
            '#<a href="(?!javascript:|[./?\#]|' . preg_quote($config->url, '/') . ')#'
        ),
        array(
            '<table class="table-bordered table-full-width">', // add bordered class on tables
            '<a rel="nofollow" href="' // add `rel="nofollow"` attribute on external links
        ),
    trim($parser->transform($url . "\n\n" . $abbr . "\n\n" . $input)));
});

// Apply filter to `content` and `message` data
Filter::add(array('content', 'message'), function($content, $fields) {
    $fields = (object) $fields;
    if( ! isset($fields->content_type) || $fields->content_type === 'Markdown' || $fields->content_type === 'Markdown Extra') {
        return Text::parse($content, '->html');
    }
    return $content;
}, 1);

// Set new `html_parser` value
if($config->html_parser === 'HTML') {
    $config->html_parser = 'Markdown Extra';
    Config::set('html_parser', $config->html_parser);
}

// Re-write `comment_wizard` value
if($config->html_parser === 'Markdown Extra') {
    Config::set('speak.comment_wizard', $speak->__comment_wizard);
}