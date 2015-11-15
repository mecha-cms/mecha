<?php

use \Michelf\MarkdownExtra;
$cargo = __DIR__ . DS . 'assets' . DS . 'cargo' . DS . 'Michelf' . DS;
include $cargo . 'Markdown.php';
include $cargo . 'MarkdownExtra.php';

Text::parser('to_html', function($input) use($config) {
    if( ! is_string($input)) return $input;
    $state = __DIR__ . DS . 'states' . DS;
    $url = File::open($state . 'url.txt')->read();
    $abbr = File::open($state . 'abbr.txt')->read();
    $parser = new MarkdownExtra;
    $parser->empty_element_suffix = ES;
    $parser->table_align_class_tmpl = 'text-%%'; // table align class, example: `<td class="text-right">`
    $input = trim($parser->transform($url . "\n\n" . $abbr . "\n\n" . $input));
    return preg_replace(
        array(
            '#<table>#',
            '#<a href="(?!' . preg_quote($config->url, '/') . '|javascript:|[\.\/\?\#])#'
        ),
        array(
            '<table class="table-bordered table-full-width">', // add bordered class on tables
            '<a rel="nofollow" href="' // add `rel="nofollow"` attribute on external links
        ),
    $input);
});