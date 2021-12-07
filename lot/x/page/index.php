<?php

function page(...$lot) {
    return new Page(...$lot);
}

function pages(...$lot) {
    return Pages::from(...$lot);
}

require __DIR__ . D . 'engine' . D . 'use.php';