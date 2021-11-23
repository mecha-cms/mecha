<?php

function asset(...$lot) {
    return count($lot) < 2 ? Asset::get(...$lot) : Asset::set(...$lot);
}