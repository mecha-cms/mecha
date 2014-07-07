<?php


/**
 * Inject the Required Assets for Manager
 * --------------------------------------
 */

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(array(
        $config->protocol . 'maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css',
        'manager/shell/editor.css',
        'manager/shell/check.css',
        'manager/shell/upload.css',
        'manager/shell/tab.css',
        'manager/shell/modal.css',
        'manager/shell/tooltip.css',
        'manager/shell/sortable.css',
        'manager/shell/accordion.css',
        'manager/shell/layout.css',
        'shell/manager.css'
    ));
}, 10);

Weapon::add('cargo_before', function() use($config, $speak) {
    echo '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '!</strong> &middot; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->log_out . '</a></div>';
}, 10);

Weapon::add('sword_before', function() {
    echo Asset::javascript('manager/sword/dashboard.js');
}, 10);

Weapon::add('sword_after', function() use($config) {
    echo Asset::javascript(array(
        $config->protocol . 'cdnjs.cloudflare.com/ajax/libs/zepto/1.1.3/zepto.min.js',
        'manager/sword/editor/editor.min.js',
        'manager/sword/editor/mte.min.js',
        'manager/sword/row.js',
        'manager/sword/slug.js',
        'manager/sword/check.js',
        'manager/sword/upload.js',
        'manager/sword/tab.js',
        'manager/sword/modal.js',
        'manager/sword/tooltip.js',
        'manager/sword/sortable.js',
        'manager/sword/accordion.js'
    ));
}, 10);


/**
 * Add Global Cache Killer for Articles and Pages
 * ----------------------------------------------
 */

function remove_pages_cache() {
    global $config;
    $root = ( ! empty($config->base) ? str_replace('/', '.', $config->base) . '.' : "");
    File::open(CACHE . DS . $root . 'sitemap.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.rss.cache.txt')->delete();
}

Weapon::add('on_article_update', 'remove_pages_cache', 10);
Weapon::add('on_page_update', 'remove_pages_cache', 10);


/**
 * Add Default Article and Page Footer Links
 * -----------------------------------------
 */

Weapon::add('article_footer', function($article) {
    $config = Config::get();
    $speak = Config::speak();
    if($config->page_type == 'manager') {
        echo ($article->state == 'draft' ? '<span class="text-info"><i class="fa fa-clock-o"></i> ' . $speak->draft . '</span> &middot; ' : "") . '<a href="' . $config->url . '/' . $config->manager->slug . '/article/repair/id:' . $article->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/article/kill/id:' . $article->id . '">' . $speak->delete . '</a>';
    }
}, 20);

Weapon::add('page_footer', function($page) {
    $config = Config::get();
    $speak = Config::speak();
    if($config->page_type == 'manager') {
        echo ($page->state == 'draft' ? '<span class="text-info"><i class="fa fa-clock-o"></i> ' . $speak->draft . '</span> &middot; ' : "") . '<a href="' . $config->url . '/' . $config->manager->slug . '/page/repair/id:' . $page->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/page/kill/id:' . $page->id . '">' . $speak->delete . '</a>';
    }
}, 20);


/**
 * Add Default Comment Footer Links
 * --------------------------------
 */

Weapon::add('comment_footer', function($comment, $article) {
    $config = Config::get();
    $speak = Config::speak();
    if(Guardian::happy()) {
        echo ($comment->state == 'pending' ? '<span class="text-info"><i class="fa fa-clock-o"></i> ' . $speak->pending . '</span> &middot; ' : "") . '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $comment->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $comment->id . '">' . $speak->delete . '</a>';
    }
}, 20);


/**
 * Load the Routes
 * ---------------
 */

$uri_end = str_replace($config->url . '/' . $config->manager->slug . '/', "", $config->url_current);
$uri_segments = explode('/', $uri_end);
if($detour = File::exist(DECK . DS . 'workers' . DS . 'route.' . $uri_segments[0] . '.php')) {
    Config::set('page_type', 'manager');
    include $detour;
}