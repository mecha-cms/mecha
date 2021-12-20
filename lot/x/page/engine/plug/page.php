<?php

From::_('page', function(string $value, $eval = false) {
    if (0 !== strpos($value = n($value), YAML\SOH . "\n")) {
        // Add empty header
        $value = YAML\SOH . "\n" . YAML\EOT . "\n\n" . $value;
    }
    $v = static::YAML($value, '  ', true, $eval);
    return $v[0] + ['content' => $v["\t"] ?? null];
});

To::_('page', function(array $value) {
    $content = $value['content'] ?? null;
    unset($value['content']);
    $value = [
        0 => $value,
        "\t" => $content
    ];
    return static::YAML($value, '  ', true);
});

function page(...$lot) {
    return Page::from(...$lot);
}