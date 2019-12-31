<?php

// Alias for `State`
class_alias('State', 'Site');

// Alias for `$state`
$GLOBALS['site'] = $site = $state;

// Default title for the layout
$GLOBALS['t'] = $t = new Anemon([$state->title], ' &#x00B7; ');

// Extend layout state(s) to the global state(s)
if (is_file($state = Layout::$state['path'] . DS . 'state.php')) {
    State::set(require $state);
}