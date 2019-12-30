<?php

Session::start();

function session(...$v) {
    return count($v) < 2 ? Session::get(...$v) : Session::set(...$v);
}