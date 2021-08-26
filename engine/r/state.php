<?php

function state(...$lot) {
    if (count($lot) < 2) {
        $lot[] = true; // Force to array
        return State::get(...$lot);
    }
    return State::set(...$lot);
}