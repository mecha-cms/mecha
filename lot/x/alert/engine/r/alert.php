<?php

function alert(...$v) {
    Alert::info(...$v);
}

$GLOBALS['alert'] = $alert = new Alert;