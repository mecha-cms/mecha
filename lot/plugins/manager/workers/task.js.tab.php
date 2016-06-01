<?php

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($tab_id) {
    echo '<script>!function(e){e(\'.tab-button[href$="' . $tab_id . '"]\').trigger("click")}(DASHBOARD.$);</script>';
}, 11);