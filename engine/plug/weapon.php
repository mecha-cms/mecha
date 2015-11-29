<?php

// Forget all memories on page visit ...
// Clear all notifications on page visit ...
Weapon::add('shield_after', function() {
    Guardian::forget();
    Notify::clear();
});