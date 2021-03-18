<?php

To::_('page', function(array $value) {
    $content = $value['content'] ?? null;
    unset($value['content']);
    $value = [
        0 => $value,
        "\t" => $content
    ];
    return static::YAML($value, '  ', true);
});
