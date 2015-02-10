<?php if( ! defined('ROOT')) die('Rejected.');


$uri_end_parts = str_replace($config->url . '/' . $config->manager->slug . '/', "", $config->url_current);
$uri_end_parts = explode('/', $uri_end_parts);
$uri_end = $uri_end_parts[0];

Config::merge('DASHBOARD.languages', array(
    'MTE' => Mecha::A($speak->MTE)
));


/**
 * Backend Assets
 * --------------
 *
 * Inject the required assets for manager.
 *
 */

Weapon::add('meta', function() use($config) {
    echo O_BEGIN . '<script>!function(a){var b=a.className;a.className=/(^| )no-js( |$)/.test(b)?b.replace(/(^| )no-js( |$)/,"$1js$2"):b+" js"}(document.documentElement);</script>' . O_END;
    if( ! Asset::loaded($config->protocol . JS_LIBRARY_PATH)) {
        echo Asset::javascript($config->protocol . JS_LIBRARY_PATH);
    }
}, 20);

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
        'shell/manager.css' // => from the current shield folder
    ), "", 'manager.min.css');
    if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) {
        echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
    }
}, 10);

Weapon::add('cargo_before', function() use($config, $speak) {
    echo O_BEGIN . Filter::apply('banner:manager', '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '</strong>! &middot; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->log_out . '</a></div>') . O_END;
}, 10);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak, $uri_end) {
    $constants = get_defined_constants(true);
    $constants_js = "";
    foreach($constants['user'] as $constant => $value) {
        $value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
        $constants_js .= $constant . '=\'' . addslashes($value) . '\',';
    }
    echo Asset::javascript('manager/sword/dashboard.js', "", 'dashboard.min.js');
    $output = O_BEGIN . '<script>var ' . rtrim($constants_js, ',') . ';DASHBOARD.segment=\'' . $uri_end . '\';DASHBOARD.languages=' . json_encode(Config::get('DASHBOARD.languages', array())) . ';DASHBOARD.is_html_parser_enabled=document.getElementsByName(\'content_type\').length?document.getElementsByName(\'content_type\')[0].checked:' . ($config->html_parser ? 'true' : 'false') . ';';
    // `DASHBOARD.tab_size` and `DASHBOARD.element_suffix` are now deprecated.
    //  Please use the `TAB` and `ES` variable as declared in the PHP constants.
    $output .= 'DASHBOARD.tab_size=\'' . TAB . '\';DASHBOARD.element_suffix=\'' . ES . '\';';
    echo $output . '</script>' . O_END;
}, 1);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
    echo Asset::javascript(array(
        'manager/sword/editor/editor.min.js',
        'manager/sword/editor/mte.min.js',
        'manager/sword/editor/run.js',
        'manager/sword/ajax.js',
        'manager/sword/row.js',
        'manager/sword/slug.js',
        'manager/sword/check.js',
        'manager/sword/upload.js',
        'manager/sword/tab.js',
        'manager/sword/modal.js',
        'manager/sword/tooltip.js',
        'manager/sword/sortable.js',
        'manager/sword/accordion.js'
    ), "", 'manager.min.js');
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
    File::open(CACHE . DS . 'sitemap.cache')->delete();
    foreach(glob(CACHE . DS . 'feeds.*.cache') as $cache) {
        File::open($cache)->delete();
    }
}

// Articles and pages
Weapon::add('on_article_update', 'do_remove_cache', 10);
Weapon::add('on_page_update', 'do_remove_cache', 10);

// Plugins
Weapon::add('on_plugin_update', function() {
    File::open(CACHE . DS . 'plugins.order.cache')->delete();
});


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

if($detour = File::exist(DECK . DS . 'workers' . DS . 'route.' . $uri_end . '.php')) {
    Config::set('page_type', 'manager');
    require $detour;
}