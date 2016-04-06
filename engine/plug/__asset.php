<?php

// DEPRECATED. Please use `Asset::javascript()`
Asset::plug('script', function($path, $addon = "", $merge = false) {
    return call_user_func_array('Asset::javascript', func_get_args());
});