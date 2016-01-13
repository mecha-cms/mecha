<?php

use \Michelf\MarkdownExtra;
require __DIR__ . DS . 'workers' . DS . 'Michelf' . DS . 'Markdown.php';
require __DIR__ . DS . 'workers' . DS . 'Michelf' . DS . 'MarkdownExtra.php';


// Re-write `Text::parse($input, '->html')` parser
Text::parser('to_html', function($input) {
    if( ! is_string($input)) return $input;
    global $config;
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
            '<table class="table-bordered table-full-width">', // add bordered class on table(s)
            '<a rel="nofollow" href="' // add `rel="nofollow"` attribute on external link(s)
        ),
    trim($parser->transform($url . "\n\n" . $abbr . "\n\n" . $input)));
});

function do_markdown($content, $results) {
    global $config;
    $results = (object) $results;
    if( ! isset($results->content_type) || $results->content_type === 'Markdown' || $results->content_type === 'Markdown Extra') {
        return Text::parse($content, '->html');
    }
    return $content;
}

// Apply `do_markdown` filter
Filter::add(array('content', 'message'), 'do_markdown', 1);

// Set new `html_parser` type
$config->html_parser->type = array_merge(
    (array) $config->html_parser->type,
    array('Markdown' => 'Markdown Extra')
);
Config::set('html_parser.type', $config->html_parser->type);

// Re-write `comment_wizard` value
if($config->html_parser->active === 'Markdown' || $config->html_parser->active === 'Markdown Extra') {
    Config::set('speak.comment_wizard', $speak->__comment_wizard);
}

// Create a new comment
if($config->is->post && Request::method('post')) {
    // Temporarily disallow image(s) in comment to prevent XSS
    if(isset($_POST['message'])) {
        $_POST['message'] = preg_replace('#(\!\[.*?\]\(.*?\))#','`$1`', $_POST['message']);
    }
}