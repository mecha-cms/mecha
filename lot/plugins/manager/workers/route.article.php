<?php

$post = 'article';
$response = 'comment';

// Ignite
if(strpos($config->url_path, '/id:') === false) {
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo '<script>
(function($) {
    $.slug(\'title\', \'slug\', \'-\');
})(window.Zepto || window.jQuery);
</script>';
    }, 11);
// Repair
} else {
    Weapon::add('unit_composer_1_before', function($page, $segment) {
        include __DIR__ . DS . 'unit.composer.1.1.php';
    });
}

// Both
Weapon::add('unit_composer_1_after', function($page, $segment) use($config, $speak) {
    include __DIR__ . DS . 'unit.composer.1.4.php';
    include __DIR__ . DS . 'unit.composer.1.3.php';
});

require __DIR__ . DS . 'route.post.php';