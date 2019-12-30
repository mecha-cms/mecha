<?php

function abort(...$v) {
    Guard::abort(...$v);
}

function check(...$v) {
    return Guard::check(...$v);
}

function kick(...$v) {
    Guard::kick(...$v);
}

function token(...$v) {
    return Guard::token(...$v);
}