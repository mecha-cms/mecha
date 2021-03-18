<?php

Session::start();

function session(...$lot) {
    return count($lot) < 2 ? Session::get(...$lot) : Session::set(...$lot);
}
