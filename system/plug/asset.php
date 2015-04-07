<?php

// DEPRECATED. Please use `Asset::javascript()`
Asset::plug('script', function($path, $addon = "", $merge = false) {
    return Asset::javascript($path, $addon, $merge);
});