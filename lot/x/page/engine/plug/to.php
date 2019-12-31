<?php

To::_('page', function(array $in) {
    $content = $in['content'] ?? null;
    unset($in['content']);
    $in = [
        0 => $in,
        "\t" => $content
    ];
    return static::YAML($in, '  ', true);
});