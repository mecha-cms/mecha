<?php

function cookie(...$lot) {
    return count($lot) < 2 ? Cookie::get(...$lot) : Cookie::set(...$lot);
}
