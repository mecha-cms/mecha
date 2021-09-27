<?php

$path = $url['path'] ?? "";
$i = $url['i'] ?? "";
$p = trim($state->path ?? "", '/');
$folder = LOT . DS . 'page' . DS . $path;

// Set proper `i` value in `$url` if we have some page with numeric file/folder name
if ("" !== $i && File::exist([
    $folder . DS . $i . '.archive',
    $folder . DS . $i . '.page'
])) {
    $path .= '/' . $i;
    $folder .= DS . $i;
    $url->i = null;
    $url->path = '/' . $path;
    $i = "";
}

$if_0 = "" === $i && ("" === $path || $path === $p);

$if_1 = File::exist([
    // `.\lot\page\home-name.{archive,page}`
    LOT . DS . 'page' . DS . $p . '.archive',
    LOT . DS . 'page' . DS . $p . '.page'
]);

$if_2 = File::exist([
    // `.\lot\page\page-name\.{archive,page}`
    $folder . DS . '.archive',
    $folder . DS . '.page'
]);

$if_3 = File::exist([
    // `.\lot\page\page-name.{archive,page}`
    $folder . '.archive',
    $folder . '.page'
]);

$if_4 = glob(LOT . DS . 'page' . DS . $p . DS . '*.page', GLOB_NOSORT);
$if_5 = glob($folder . DS . '*.page', GLOB_NOSORT);

$folder = dirname($folder);
$if_6 = File::exist([
    // `.\lot\page\parent-name.{archive,page}`
    $folder . '.archive',
    $folder . '.page',
    $folder . DS . '.archive',
    $folder . DS . '.page'
]);

State::set('is', [
    'error' => "" === $path && !$if_1 || "" !== $path && !$if_3 ? 404 : false,
    'home' => $if_0,
    'page' => "" === $path && $if_1 || "" !== $path && ($if_2 || $if_3 && !$if_5),
    'pages' => "" !== $i || "" === $path && $if_4 || "" !== $path && !$if_2 && $if_5
]);

$count = count("" === $path ? $if_4 : $if_5);

State::set('has', [
    'next' => State::is('pages') && ($count > (($i ?: 1) * ($state->chunk ?? 5))),
    'page' => "" === $path && $if_1 || $if_3,
    'pages' => $count > 0,
    'parent' => false !== strpos($path, '/'), // `foo/bar`
    'prev' => State::is('pages') && $i > 1,
    'i' => State::is('pages') && "" !== $i
]);

State::set([
    'are' => [],
    'can' => [],
    'not' => []
]);