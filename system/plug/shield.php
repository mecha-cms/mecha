<?php

// DEPRECATED. Please use `Shield::lot()`
Shield::plug('define', function($key, $value = "") {
    return Shield::lot($key, $value);
});

// DEPRECATED. Please use `Shield::apart()`
Shield::plug('undefine', function($data) {
    return Shield::apart($data);
});