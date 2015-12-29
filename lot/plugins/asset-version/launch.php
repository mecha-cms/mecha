<?php

function asset_version($url, $source) {
    $path = Asset::path($source);
    if($path && strpos($path, '://') === false) {
        return $url . '?v=' . File::T($path);
    }
    return $url;
}

Filter::add('asset:url', 'asset_version');