<?php

function do_asset_version($url, $source) {
    $path = Asset::path($source);
    if($path && strpos($path, '://') === false && strpos($path, '//') !== 0) {
        return $url . '?v=' . File::T($path);
    }
    return $url;
}

Filter::add('asset:url', 'do_asset_version');