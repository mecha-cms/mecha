<?php

// Forget all memor(y|ies) on page visit ...
// Clear all notif(y|ies) on page visit ...
Weapon::add('shield_after', function() {
    Guardian::forget();
    Notify::clear();
});