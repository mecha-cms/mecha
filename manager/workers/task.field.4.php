<?php

$header = array(
    'Title' => $title,
    'Description' => trim($description) !== "" ? Text::parse(trim($description), '->encoded_json') : false,
    'Author' => $author,
    'Content Type' => Request::post('content_type', 'HTML'),
    'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
);