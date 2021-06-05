<?php namespace x\link\r;

function image_source_set($value, \HTML $lot) {
    return \x\link\r\source_set($value, $lot);
}

function source_set($value, \HTML $lot) {
    if (!$value) {
        return $value;
    }
    $out = "";
    foreach (\preg_split('/(\s*,\s*)(?!,)/', $value, null, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
        if (',' === \trim($v)) {
            $out .= $v;
            continue;
        }
        $out .= \URL::long(\rtrim($v, ','), false);
    }
    return $out;
}
