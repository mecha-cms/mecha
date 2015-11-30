<?php


/**
 * ===================================================================
 *  APPLY FILTER WITH PREFIX
 * ===================================================================
 *
 * -- CODE: ----------------------------------------------------------
 *
 *    Filter::colon('page:title', $content);
 *
 *    // is equal to ...
 *
 *    Filter::apply(array('page:title', 'title'), $content);
 *
 * -------------------------------------------------------------------
 *
 */

Filter::plug('colon', function($name, $value) {
    $arguments = func_get_args();
    if(strpos($name, ':') !== false) {
        $s = explode(':', $name, 2);
        $arguments[0] = array($name, $s[1]);
    }
    return call_user_func_array('Filter::apply', $arguments);
});

// Set response, comment and user `status` as `pilot`, `passenger` and `intruder`
Filter::add(array('response:status', 'comment:status', 'user:status'), function($status) {
    return Mecha::alter($status, array(
        0 => 'intruder',
        1 => 'pilot',
        2 => 'passenger'
    ));
}, 1);

// Decode the obfuscated `email` value
Filter::add(array('response:email', 'comment:email', 'user:email'), function($data) {
    return Text::parse($data, '->decoded_html');
}, 1);