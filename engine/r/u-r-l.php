<?php

$port = (int) $_SERVER['SERVER_PORT'];
$scheme = 'http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === $port ? 's' : "");
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
$path = ltrim($_GET['_'] ?? "", '/');
$query = explode('&', $_SERVER['QUERY_STRING'], 2)[1] ?? "";

unset($_GET['_']);

// Prevent XSS attack where possible
$path = strtr(trim($path, '/'), [
    '<' => '%3C',
    '>' => '%3E',
    '&' => '%26',
    '"' => '%22'
]);

$a = explode('/', $path);
if (is_numeric(end($a))) {
    $i = '/' . array_pop($a);
    $path = implode('/', $a);
} else {
    $i = null;
}

// Detect if user put this CMS in a sub-folder by checking the `directory` value
$d = trim(($_SERVER['CONTEXT_PREFIX'] ?? "") . strtr(ROOT, [
    GROUND => "",
    DS => '/'
]), '/');

$d = "" !== $d ? '/' . $d : null;
$path = "" !== $path ? '/' . $path : null;
$query = "" !== $query ? '?' . $query : null;
$hash = !empty($_COOKIE['hash']) ? '#' . $_COOKIE['hash'] : null;
$root = $protocol . $host . $d;

$GLOBALS['url'] = $url = new URL([
    'clean' => $root . $path,
    'current' => $root . $path . $i . $query . $hash,
    'd' => $d,
    'ground' => $protocol . $host,
    'hash' => $hash,
    'host' => $host,
    'i' => $i,
    'path' => $path,
    'port' => $port,
    'protocol' => $protocol,
    'query' => $query,
    'root' => $root
]);