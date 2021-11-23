<?php

From::_('page', function(string $value, $eval = false) {
    if (0 !== strpos($value = n($value), YAML\SOH . "\n")) {
        // Add empty header
        $value = YAML\SOH . "\n" . YAML\EOT . "\n\n" . $value;
    }
    $v = static::YAML($value, '  ', true, $eval);
    return $v[0] + ['content' => $v["\t"] ?? null];
});