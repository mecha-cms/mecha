<?php

Time::_('en', '%A, %B %d, %Y');

Hook::set('set', function() {
    $key = strtr(State::get('language') ?? "", '-', '_');
    // Fix for missing language key → default to `en`
    if (!Time::_($key)) {
        Time::_($key, Time::_('en'));
    }
}, 20);