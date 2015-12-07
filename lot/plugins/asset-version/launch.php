<?php

Filter::add('asset:url', function($url, $source) {
    $path = Asset::path($source);
    if($path && strpos($path, '://') === false) {
        return $url . '?v=' . File::T($path);
    }
    return $url;
});