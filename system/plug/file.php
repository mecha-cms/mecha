<?php

// DEPRECATED. Please use `File::pocket()`
File::plug('dir', function($paths, $permission = 0777) {
    return File::pocket($paths, $permission);
});