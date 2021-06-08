<?php namespace x\link\f;

function image_source_set($value, $key, $name) {
    return \fire("\\x\\link\\f\\source_set", [$value, $key, $name], $this);
}

function source_set($value, $key, $name) {
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
