<?php


/**
 * Backend Assets
 * --------------
 *
 * Inject the required assets for manager.
 *
 */

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(array(
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
    if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) {
        echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
    }
}, 10);

Weapon::add('cargo_before', function() use($config, $speak) {
    echo O_BEGIN . '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '!</strong> &middot; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->log_out . '</a></div>' . O_END;
}, 10);

Weapon::add('sword_before', function() use($config) {
    echo Asset::javascript('manager/sword/dashboard.js');
    echo O_BEGIN . '<script>' . NL . 'DASHBOARD.tab_size = \'' . TAB . '\';' . NL . 'DASHBOARD.is_html_parser_enabled = ' . (Config::get('article.content_type') == HTML_PARSER || Config::get('page.content_type') == HTML_PARSER || Config::get('response.content_type') == HTML_PARSER ? 'true' : 'false') . ';' . NL . '</script>' . O_END;
}, 10);

Weapon::add('sword_after', function() use($config) {
    echo Asset::javascript(array(
        $config->protocol . JS_LIBRARY_PATH,
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
 * Cache Killer
 * ------------
 *
 * Add global cache killer for articles and pages.
 *
 */

function do_remove_cache() {
    global $config;
    $root = ( ! empty($config->base) ? str_replace('/', '.', $config->base) . '.' : "");
    File::open(CACHE . DS . $root . 'sitemap.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.rss.cache.txt')->delete();
}

Weapon::add('on_article_update', 'do_remove_cache', 10);
Weapon::add('on_page_update', 'do_remove_cache', 10);


/**
 * Footer Links
 * ------------
 *
 * Add default article, page and comment footer links.
 *
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

Weapon::add('comment_footer', function($comment, $article) {
    $config = Config::get();
    $speak = Config::speak();
    if(Guardian::happy()) {
        echo ($comment->state == 'pending' ? '<span class="text-info"><i class="fa fa-clock-o"></i> ' . $speak->pending . '</span> &middot; ' : "") . '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $comment->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $comment->id . '">' . $speak->delete . '</a>';
    }
}, 20);


/**
 * Backend Routes
 * --------------
 *
 * Load the routes.
 *
 */

$uri_end = str_replace($config->url . '/' . $config->manager->slug . '/', "", $config->url_current);
$uri_end_parts = explode('/', $uri_end);
if($detour = File::exist(DECK . DS . 'workers' . DS . 'route.' . $uri_end_parts[0] . '.php')) {
    Config::set('page_type', 'manager');
    require $detour;
}