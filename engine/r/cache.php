<?php

function cache(...$v) {
    return Cache::hit(...$v);
}