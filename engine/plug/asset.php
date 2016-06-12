<?php

// Alias for `Asset::stylesheet()`
Asset::plug('css', function() {
    return call_user_func_array('Asset::stylesheet', func_get_args());
});

// Alias for `Asset::javascript()`
Asset::plug('js', function() {
    return call_user_func_array('Asset::javascript', func_get_args());
});

// Alias(es) for `Asset::image()`
foreach(array('gif', 'jpeg', 'jpg', 'png') as $kin) {
    Asset::plug($kin, function() {
        return call_user_func_array('Asset::image', func_get_args());
    });
}