<?php

function state(...$v) {
    if (count($v) < 2) {
        $v[] = true; // Force to array
        return State::get(...$v);
    }
    return State::set(...$v);
}