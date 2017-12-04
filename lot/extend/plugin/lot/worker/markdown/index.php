<?php

function fn_markdown($in = "", $lot = []) {
    if (!isset($lot['type']) || $lot['type'] !== 'Markdown') {
        return $in;
    }
    $x = new ParsedownExtraPlugin;
    foreach (Plugin::state(__DIR__) as $k => $v) {
        $x->{$k} = $v;
    }
    return $x->text($in);
}

function fn_markdown_span($in, $lot = []) {
    if (!isset($lot)) return $in;
    return w(fn_markdown($in, $lot), HTML_WISE_I);
}

From::_('markdown', function($in) {
    return fn_markdown($in, ['type' => 'Markdown']);
});

To::_('markdown', function($in) {
    return $in; // TODO
});

Hook::set('*.title', 'fn_markdown_span', 2);
Hook::set(['*.description', '*.content'], 'fn_markdown', 2);

// Add `markdown` to the allowed file extension(s)
File::$config['extensions'] = array_merge(File::$config['extensions'], [
    'markdown',
    'md',
    'mkd'
]);