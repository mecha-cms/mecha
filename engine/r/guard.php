<?php

function abort(...$lot) {
    Guard::abort(...$lot);
}

function check(...$lot) {
    return Guard::check(...$lot);
}

function kick(...$lot) {
    Guard::kick(...$lot);
}

function token(...$lot) {
    return Guard::token(...$lot);
}
