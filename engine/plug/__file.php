<?php

// DEPRECATED. Please use `File::pocket()`
File::plug('dir', function($paths, $permission = 0777) {
    return call_user_func_array('File::pocket', func_get_args());
});