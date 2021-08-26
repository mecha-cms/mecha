<?php

function layout(...$lot) {
    return count($lot) < 2 ? Layout::get(...$lot) : Layout::set(...$lot);
}