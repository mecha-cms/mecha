<?php

Message::plug('error', function($text, $vars = [], $preserve_case = false) {
    ++Message::$x;
    return Message::set('error', $text, $vars, $preserve_case);
});

Message::plug('warning', function($text, $vars = [], $preserve_case = false) {
    ++Message::$x;
    return Message::set('warning', $text, $vars, $preserve_case);
});