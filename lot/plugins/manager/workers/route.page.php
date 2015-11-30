<?php

$post = 'page';
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
        include __DIR__ . DS . 'unit.composer.1.2.php';
    });
}

// Both
Weapon::add('unit_composer_1_after', function($page, $segment) use($config, $speak) {
    include __DIR__ . DS . 'unit.composer.1.3.php';
});

// Check for duplicate slug
if($request = Request::post()) {
    $slug = isset($request['slug']) ? $request['slug'] : false;
    if(
        $slug === $config->index->slug ||
        $slug === $config->tag->slug ||
        $slug === $config->archive->slug ||
        $slug === $config->search->slug ||
        $slug === $config->manager->slug
    ) {
        Notify::error(Config::speak('notify_error_slug_exist', $slug));
        Guardian::memorize($request);
    }
}

require __DIR__ . DS . 'route.post.php';