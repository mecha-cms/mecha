<?php

function cache(...$lot) {
    return Cache::hit(...$lot);
}