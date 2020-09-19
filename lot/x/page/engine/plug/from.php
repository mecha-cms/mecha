<?php

From::_('page', function(string $in, $eval = false) {
    if (0 !== strpos($in = n($in), YAML\SOH . "\n")) {
        // Add empty header
        $in = YAML\SOH . "\n" . YAML\EOT . "\n\n" . $in;
    }
    $v = static::YAML($in, '  ', true, $eval);
    return $v[0] + ['content' => $v["\t"] ?? null];
});
