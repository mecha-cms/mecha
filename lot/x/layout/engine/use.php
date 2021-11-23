<?php

foreach([
    'excerpt' => function(string $value = null, $html = true, $x = 200) {
        // Make sure to add space at the end of the block tag(s) that will be removed
        // To make `<p>asdf.</p><p>asdf</p>` becomes `asdf. asdf` and not `asdf.asdf`
        $blocks = 'address|article|blockquote|details|div|d[dt]|figure|(?:fig)?caption|footer|h(?:[1-6]|eader|r)|li|main|nav|p(?:re)?|section|summary|t[dh]';
        $s = preg_replace([
            '/\s+/',
            '/\s*(<\/(?:' . $blocks . ')>)\s*/'
        ], [
            ' ',
            '$1 '
        ], $value);
        $s = strip_tags($s, $html ? '<a><abbr><b><br><cite><code><del><dfn><em><i><ins><kbd><mark><q><small><span><strong><sub><sup><time><u><var>' : "");
        if (is_int($x)) {
            $x = [$x, '&#x2026;'];
        }
        $utf8 = extension_loaded('mbstring');
        // <https://stackoverflow.com/a/1193598/1163000>
        if ($html && (false !== strpos($s, '<') || false !== strpos($s, '&'))) {
            $out = "";
            $done = $i = 0;
            $tags = [];
            while ($done < $x[0] && preg_match('/<\/?([a-z\d:.-]+)(?:\s[^>]*)?>|&(?:[a-z\d]+|#\d+|#x[a-f\d]+);|[\x80-\xFF][\x80-\xBF]*/i', $s, $m, PREG_OFFSET_CAPTURE, $i)) {
                $tag = $m[0][0];
                $pos = $m[0][1];
                $str = substr($s, $i, $pos - $i);
                if ($done + strlen($str) > $x[0]) {
                    $out .= substr($str, 0, $x[0] - $done);
                    $done = $x[0];
                    break;
                }
                $out .= $str;
                $done += strlen($str);
                if ($done >= $x[0]) {
                    break;
                }
                if ('&' === $tag[0] || ord($tag) >= 0x80) {
                    $out .= $tag;
                    ++$done;
                } else {
                    // `tag`
                    $n = $m[1][0];
                    // `</tag>`
                    if ('/' === $tag[1]) {
                        $open = array_pop($tags);
                        assert($open === $n); // Check that tag(s) are properly nested!
                        $out .= $tag;
                    // `<tag/>`
                    } else if ('/>' === substr($tag, -2) || preg_match('/<(?:area|base|br|col|command|embed|hr|img|input|link|meta|param|source)(?:\s[^>]*)?>/i', $tag)) {
                        $out .= $tag;
                    // `<tag>`
                    } else {
                        $out .= $tag;
                        $tags[] = $n;
                    }
                }
                // Continue after the tag…
                $i = $pos + strlen($tag);
            }
            // Print rest of the text…
            if ($done < $x[0] && $i < strlen($s)) {
                $out .= substr($s, $i, $x[0] - $done);
            }
            // Close any open tag(s)…
            while ($close = array_pop($tags)) {
                $out .= '</' . $close . '>';
            }
            $out = trim(preg_replace('/\s*<br(\s[^>]*)?>\s*/', ' ', $out));
            $s = trim(strip_tags($s));
            $t = $utf8 ? mb_strlen($s) : strlen($s);
            $out = trim($out) . ($t > $x[0] ? $x[1] : "");
            return "" !== $out ? $out : null;
        }
        $out = $utf8 ? mb_substr($s, 0, $x[0]) : substr($s, 0, $x[0]);
        $t = $utf8 ? mb_strlen($s) : strlen($s);
        $out = trim($out) . ($t > $x[0] ? $x[1] : "");
        return "" !== $out ? $out : null;
    },
    'sentence' => function(string $value = null, string $tail = '.') {
        $value = trim($value);
        if (extension_loaded('mbstring')) {
            return mb_strtoupper(mb_substr($value, 0, 1)) . mb_strtolower(mb_substr($value, 1)) . $tail;
        }
        return ucfirst(strtolower($value)) . $tail;
    },
    'text' => "\\w",
    'title' => function(string $value = null) {
        $value = w($value);
        $out = extension_loaded('mbstring') ? mb_convert_case($value, MB_CASE_TITLE) : ucwords($value);
        // Convert to abbreviation if all case(s) are in upper
        $out = u($out) === $out ? strtr($out, [' ' => ""]) : $out;
        return "" !== $out ? $out : null;
    }
] as $k => $v) {
    To::_($k, $v);
}