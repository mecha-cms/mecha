<?php

foreach (['error', 'warning'] as $kin) {
    Message::_($kin, function($text, $vars = [], $preserve_case = false) use($kin) {
        ++Message::$x;
        return Message::set($kin, $text, $vars, $preserve_case);
    });
}