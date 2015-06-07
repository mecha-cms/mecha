<?php if( ! defined('ROOT')) die('Rejected.');


// Loading functions
foreach(glob(DECK . DS . 'workers' . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}

$uri_end_parts = explode('/', $config->url_path);
$uri_end = $config->page_type == 'manager' ? $uri_end_parts[1] : $uri_end_parts[0];

// Hide `paragraph`, `superscript` and `subscript` button by default
$speak->MTE->buttons->paragraph = false;
$speak->MTE->buttons->superscript = false;
$speak->MTE->buttons->subscript = false;

// Merge language data to the `DASHBOARD`
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
    echo O_BEGIN . '<script>!function(a){var b=a.className;a.className=/(^|\s)no-js(\s|$)/.test(b)?b.replace(/(^|\s)no-js(\s|$)/,"$1js$2"):b+" js"}(document.documentElement);</script>' . O_END;
    if( ! Asset::loaded($config->protocol . JS_LIBRARY_PATH)) {
        echo Asset::javascript($config->protocol . JS_LIBRARY_PATH);
    }
}, 20);

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(array(
        'manager/shell/editor.css',
        'manager/shell/row.css',
        'manager/shell/check.css',
        'manager/shell/upload.css',
        'manager/shell/tab.css',
        'manager/shell/modal.css',
        'manager/shell/tooltip.css',
        'manager/shell/sortable.css',
        'manager/shell/accordion.css',
        'manager/shell/layout.css',
        'shell/manager.css' // => taken from the current shield folder (if any)
    ), "", 'manager.min.css');
    if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) {
        echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
    }
}, 10);

Weapon::add('cargo_before', function() use($config, $speak) {
    echo O_BEGIN . Filter::apply('author:banner', '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '</strong>! &middot; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->log_out . '</a></div>') . O_END;
}, 10);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak, $uri_end) {
    $constants = get_defined_constants(true);
    $constants_js = "";
    foreach($constants['user'] as $constant => $value) {
        $constants_js .= $constant . '=' . json_encode($value) . ',';
    }
    echo Asset::javascript('manager/sword/dashboard.js', "", 'dashboard.min.js');
    $output = O_BEGIN . '<script>var ' . rtrim($constants_js, ',') . ';DASHBOARD.segment="' . $uri_end . '";DASHBOARD.languages=' . json_encode(Config::get('DASHBOARD.languages', array())) . ';DASHBOARD.is_html_parser_enabled=' . (Config::get('html_parser') == HTML_PARSER ? 'true' : 'false') . ';';
    // `DASHBOARD.tab_size` and `DASHBOARD.element_suffix` are now deprecated.
    //  Please use the `TAB` and `ES` variable as declared in the PHP constants.
    $output .= 'DASHBOARD.tab_size="' . TAB . '";DASHBOARD.element_suffix="' . ES . '";DASHBOARD.file_extension_allow="' . implode(',', File::$config['file_extension_allow']) . '";';
    echo $output . '</script>' . O_END;
}, 1);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
    Session::kill('recent_file_update');
    echo Asset::javascript(array(
        'manager/sword/editor/editor.min.js',
        'manager/sword/editor/mte.min.js',
        'manager/sword/editor/hte.min.js',
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
    File::open(CACHE . DS . 'feed.cache')->delete();
    File::open(CACHE . DS . 'feeds.cache')->delete();
    foreach(glob(CACHE . DS . '{feed,feeds}.*.cache', GLOB_NOSORT | GLOB_BRACE) as $cache) {
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
        $status = Mecha::alter(pathinfo($article->path, PATHINFO_EXTENSION), array(
            'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
            'archive' => Jot::span('info', Jot::icon('archive') . ' ' . $speak->archive) . ' &middot; '
        ), "");
        echo $status . '<a href="' . $config->url . '/' . $config->manager->slug . '/article/repair/id:' . $article->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/article/kill/id:' . $article->id . '">' . $speak->delete . '</a>';
    }
}, 20);

Weapon::add('page_footer', function($page) {
    $config = Config::get();
    $speak = Config::speak();
    if($config->page_type == 'manager') {
        $status = Mecha::alter(pathinfo($page->path, PATHINFO_EXTENSION), array(
            'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
            'archive' => Jot::span('info', Jot::icon('archive') . ' ' . $speak->archive) . ' &middot; '
        ), "");
        echo $status . '<a href="' . $config->url . '/' . $config->manager->slug . '/page/repair/id:' . $page->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/page/kill/id:' . $page->id . '">' . $speak->delete . '</a>';
    }
}, 20);

Weapon::add('comment_footer', function($comment, $article) {
    $config = Config::get();
    $speak = Config::speak();
    if(Guardian::happy()) {
        echo ($comment->state == 'pending' ? Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->pending) . ' &middot; ' : "") . '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $comment->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $comment->id . '">' . $speak->delete . '</a>';
    }
}, 20);


/**
 * Widget Manager Menus
 * --------------------
 */

Weapon::add('shield_before', function() {

    $config = Config::get();
    $speak = Config::speak();

    $total = $config->total_comments_backend;
    $destination = SYSTEM . DS . 'log' . DS . 'comments.total.log';
    if($file = File::exist($destination)) {
        $old = (int) File::open($file)->read();
        $total = ($total > $old) ? ($total - $old) : 0;
    } else {
        File::write($total)->saveTo($destination, 0600);
    }

    $menus = array(
        $speak->config => array('icon' => 'cogs', 'url' => $config->manager->slug . '/config', 'stack' => 9),
        $speak->article => array('icon' => 'file-text', 'url' => $config->manager->slug . '/article', 'stack' => 9.01),
        $speak->page => array('icon' => 'file', 'url' => $config->manager->slug . '/page', 'stack' => 9.02),
        $speak->comment => array(
            'icon' => 'comments',
            'url' => $config->manager->slug . '/comment',
            'count' => $total,
            'stack' => 9.03
        ),
        $speak->tag => array('icon' => 'tags', 'url' => $config->manager->slug . '/tag', 'stack' => 9.04),
        $speak->menu => array('icon' => 'bars', 'url' => $config->manager->slug . '/menu', 'stack' => 9.05),
        $speak->asset => array('icon' => 'briefcase', 'url' => $config->manager->slug . '/asset', 'stack' => 9.06),
        $speak->field => array('icon' => 'th-list', 'url' => $config->manager->slug . '/field', 'stack' => 9.07),
        $speak->shortcode => array('icon' => 'coffee', 'url' => $config->manager->slug . '/shortcode', 'stack' => 9.08),
        $speak->shield => array('icon' => 'shield', 'url' => $config->manager->slug . '/shield', 'stack' => 9.09),
        $speak->plugin => array('icon' => 'plug', 'url' => $config->manager->slug . '/plugin', 'stack' => 9.1),
        $speak->cache => array('icon' => 'clock-o', 'url' => $config->manager->slug . '/cache', 'stack' => 9.11),
        $speak->backup => array('icon' => 'life-ring', 'url' => $config->manager->slug . '/backup', 'stack' => 9.12)
    );

    if($errors = File::exist(SYSTEM . DS . 'log' . DS . 'errors.log')) {
        $total = 0;
        if(filesize($errors) > MAX_ERROR_FILE_SIZE) {
            File::open($errors)->delete();
            $total = '&infin;';
        }
        foreach(explode("\n", File::open($errors)->read()) as $message) {
            if(trim($message) !== "") $total++;
        }
        $menus[$speak->error] = array(
            'icon' => 'exclamation-triangle',
            'url' => $config->manager->slug . '/error',
            'count' => $total,
            'stack' => 9.13
        );
    }

    if($config->page_type === 'article' || $config->page_type === 'page') {
        $type = $config->page_type;
        $id = $type === 'article' ? $config->article->id : $config->page->id;
        $menus[Config::speak('manager._this_', array($speak->edit, $speak->article))] = array(
            'icon' => 'pencil',
            'url' => $config->manager->slug . '/' . $type . '/repair/id:' . $id,
            'stack' => 9.14
        );
        $menus[Config::speak('manager._this_', array($speak->delete, $speak->page))] = array(
            'icon' => 'trash',
            'url' => $config->manager->slug . '/' . $type . '/kill/id:' . $id,
            'stack' => 9.15
        );
    }

    Config::merge('manager_menu', $menus);

});


/**
 * Backend Routes
 * --------------
 *
 * Load the routes.
 *
 */

if($detour = File::exist(DECK . DS . 'workers' . DS . 'route.' . $uri_end . '.php')) {
    require $detour;
}