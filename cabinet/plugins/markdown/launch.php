<?php

use \Michelf\MarkdownExtra;

include PLUGIN . DS . basename(__DIR__) . DS . 'Michelf' . DS . 'Markdown.php';
include PLUGIN . DS . basename(__DIR__) . DS . 'Michelf' . DS . 'MarkdownExtra.php';

Text::parser('to_html', function($input) {
    if( ! is_string($input)) return $input;
    $parser = new MarkdownExtra;
    $parser->empty_element_suffix = ES;
    $parser->table_align_class_tmpl = 'text-%%'; // Define table alignment class, example: `<td class="text-right">`
    return trim($parser->transform($input));
});