<?php

function hook(...$lot) {
    return count($lot) < 2 ? Hook::get(...$lot) : Hook::set(...$lot);
}

function kick(?string $to = null, ?int $status = null) {
    $to = Hook::fire('kick', [$to, $status]);
    header('location: ' . ("" === $to ? '/' : ($to ?? '/')), true, $status ?? 301);
    exit;
}