<?php

$post = 'page';
$response = 'comment';

// Repair
if(strpos($config->url_path, '/id:') !== false) {
    Weapon::add('tab_button_before', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'unit' . DS . 'tab' . DS . 'button' . DS . 'new.php';
    }, .9);
    Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'date.hidden.php';
    }, .9);
}

// You can't use index, tag, archive, search, manager and feed slug URL for page(s)
if($slug = Request::post('slug')) {
    $s = array(
        $config->index->slug => 1,
        $config->tag->slug => 1,
        $config->archive->slug => 1,
        $config->search->slug => 1,
        $config->manager->slug => 1,
        'feed' => 1 // hard-coded :(
    );
    if(isset($s[$slug])) {
        Notify::error(Config::speak('notify_error_slug_exist', $slug));
    }
}

require __DIR__ . DS . 'route.post.php';