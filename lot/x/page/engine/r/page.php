<?php

function page(...$lot) {
    return new Page(...$lot);
}

function pages(...$lot) {
    return Pages::from(...$lot);
}