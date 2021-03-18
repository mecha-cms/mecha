<?php

function alert(...$lot) {
    Alert::info(...$lot);
}

$GLOBALS['alert'] = $alert = new Alert;
