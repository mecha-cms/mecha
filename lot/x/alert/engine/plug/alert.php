<?php

function alert(...$lot) {
    return count($lot) < 2 ? Alert::get(...$lot) : Alert::set(...$lot);
}