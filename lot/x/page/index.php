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

$path = $url['path'] ?? "";
$i = $url['i'] ?? "";
$p = trim($state->path ?? "", '/');
$folder = LOT . D . 'page' . D . $path;

// Set proper `i` value in `$url` if we have some page with numeric file/folder name
if ("" !== $i && exist([
    $folder . D . $i . '.archive',
    $folder . D . $i . '.page'
], 1)) {
    $path .= '/' . $i;
    $folder .= D . $i;
    $url->i = null;
    $url->path = '/' . $path;
    $i = "";
}

$if_0 = "" === $i && ("" === $path || $path === $p);

$if_1 = exist([
    // `.\lot\page\home-name.{archive,page}`
    LOT . D . 'page' . D . $p . '.archive',
    LOT . D . 'page' . D . $p . '.page'
], 1);

$if_2 = exist([
    // `.\lot\page\page-name\.{archive,page}`
    $folder . D . '.archive',
    $folder . D . '.page'
], 1);

$if_3 = exist([
    // `.\lot\page\page-name.{archive,page}`
    $folder . '.archive',
    $folder . '.page'
], 1);

$if_4 = glob(LOT . D . 'page' . D . $p . D . '*.page', GLOB_NOSORT);
$if_5 = glob($folder . D . '*.page', GLOB_NOSORT);

$folder = dirname($folder);
$if_6 = exist([
    // `.\lot\page\parent-name.{archive,page}`
    $folder . '.archive',
    $folder . '.page',
    $folder . D . '.archive',
    $folder . D . '.page'
], 1);

State::set('is', [
    'error' => $is_error = "" === $path && !$if_1 || "" !== $path && !$if_3 ? 404 : false,
    'home' => $is_home = $if_0,
    'page' => $is_page = "" === $path && $if_1 || "" !== $path && ($if_2 || $if_3 && !$if_5),
    'pages' => $is_pages = "" !== $i || "" === $path && $if_4 || "" !== $path && !$if_2 && $if_5
]);

$count = count("" === $path ? $if_4 : $if_5);

State::set('has', [
    'next' => $is_pages && ($count > (($i ?: 1) * ($state->chunk ?? 5))),
    'page' => "" === $path && $if_1 || $if_3,
    'pages' => $count > 0,
    'parent' => false !== strpos($path, '/'), // `foo/bar`
    'prev' => $is_pages && $i > 1,
    'i' => $is_pages && "" !== $i
]);

State::set([
    'are' => [],
    'can' => [],
    'not' => []
]);