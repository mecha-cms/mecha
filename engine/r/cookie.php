<?php

function cookie(...$v) {
    return count($v) < 2 ? Cookie::get(...$v) : Cookie::set(...$v);
}