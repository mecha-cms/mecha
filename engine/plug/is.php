<?php

foreach([
    'JSON' => "_\\json",
    'json' => "_\\json" // Alias
] as $k => $v) {
    Is::_($k, $v);
}