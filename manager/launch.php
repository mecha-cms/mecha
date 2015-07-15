<?php if( ! defined('ROOT')) die('Rejected!');


// Loading function(s)
foreach(glob(DECK . DS . 'workers' . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}

$uri_end_parts = explode('/', $config->url_path);
$uri_end = $config->page_type === 'manager' ? $uri_end_parts[1] : $uri_end_parts[0];

// Hide `paragraph`, `superscript` and `subscript` button by default
$speak->MTE->buttons->paragraph = false;
$speak->MTE->buttons->superscript = false;
$speak->MTE->buttons->subscript = false;

// Merge language data to the `DASHBOARD`
Config::merge('DASHBOARD.languages.MTE', Mecha::A($speak->MTE));


/**
 * Backend Asset(s)
 * ----------------
 *
 * Inject the required asset(s) for manager.
 *
 */

Weapon::add('meta', function() use($config) {
    $T2 = str_repeat(TAB, 2);
    echo O_BEGIN . $T2 . '<script>!function(a){var b=a.className;a.className=/(^|\s)no-js(\s|$)/.test(b)?b.replace(/(^|\s)no-js(\s|$)/,"$1js$2"):b+" js"}(document.documentElement);</script>' . O_END;
    if( ! Asset::loaded($config->protocol . JS_LIBRARY_PATH)) {
        echo $T2 . Asset::javascript($config->protocol . JS_LIBRARY_PATH);
    }
}, 20);

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(array(
        'manager/assets/shell/editor.css',
        'manager/assets/shell/row.css',
        'manager/assets/shell/check.css',
        'manager/assets/shell/upload.css',
        'manager/assets/shell/tab.css',
        'manager/assets/shell/modal.css',
        'manager/assets/shell/tooltip.css',
        'manager/assets/shell/sortable.css',
        'manager/assets/shell/accordion.css',
        'manager/assets/shell/layout.css',
        'assets/shell/manager.css', // => taken from the current shield folder (if available)
        'shell/manager.css' // => --ibid (tries to find the `shell` folder that is placed outside the `assets` folder)
    ), "", 'shell/manager.min.css');
    if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) {
        echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
    }
}, 10);

Weapon::add('cargo_after', function() use($config, $speak) {
    if(Widget::$id['manager_bar'] <= 1) {
        echo O_BEGIN . Widget::manager('BAR') . O_END; // include once ...
    }
}, 20);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak, $uri_end) {
    echo Asset::javascript(array(
        'manager/assets/sword/dashboard.js',
        'manager/assets/sword/dashboard.task.extend.js',
        'manager/assets/sword/dashboard.task.file.js',
        'manager/assets/sword/dashboard.task.query.js',
        'manager/assets/sword/dashboard.task.session.js',
        'manager/assets/sword/dashboard.task.slug.js'
    ), "", 'sword/dashboard.min.js');
    $constant = get_defined_constants(true);
    $constant_js = "";
    foreach($constant['user'] as $k => $v) {
        $constant_js .= $k . '=' . json_encode($v) . ',';
    }
    unset($constant);
    $cargo = array(
        'segment' => $uri_end,
        'languages' => Config::get('DASHBOARD.languages', array()),
        'is_html_parser_enabled' => Config::get('html_parser') === HTML_PARSER,
        // `DASHBOARD.tab_size` and `DASHBOARD.element_suffix` are now deprecated.
        //  Please use the `TAB` and `ES` variable as declared in the PHP constant(s).
        'html_parser' => HTML_PARSER,
        'tab_size' => TAB,
        'element_suffix' => ES,
        'file_extension_allow' => implode(',', File::$config['file_extension_allow']),
        'url' => array(
            'protocol' => $config->protocol,
            'base' => $config->base,
            'host' => $config->host,
            'url' => $config->url,
            'path' => $config->url_path,
            'current' => $config->url_current,
            'query' => $config->url_query
        )
    );
    $output = O_BEGIN . '<script>var ' . rtrim($constant_js, ',') . ';';
    foreach($cargo as $k => $v) {
        $output .= 'DASHBOARD.' . $k . '=' . json_encode($v) . ';';
    }
    $output .= 'DASHBOARD.url.hash=window.location.hash;';
    echo $output . '</script>' . O_END;
}, 1);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
    $MTE = Mecha::alter(Config::get('html_parser'), array(
        HTML_PARSER => 'mte',
        'HTML' => 'hte'
    ), 'hte'); // default is `hte`
    Session::kill('recent_file_update');
    echo Asset::javascript('manager/assets/sword/editor/editor.min.js');
    echo Asset::javascript('manager/assets/sword/editor/' . $MTE . '.min.js');
    echo Asset::javascript(array(
        'manager/assets/sword/editor/run.js',
        'manager/assets/sword/ajax.js',
        'manager/assets/sword/row.js',
        'manager/assets/sword/slug.js',
        'manager/assets/sword/check.js',
        'manager/assets/sword/upload.js',
        'manager/assets/sword/tab.js',
        'manager/assets/sword/modal.js',
        'manager/assets/sword/tooltip.js',
        'manager/assets/sword/sortable.js',
        'manager/assets/sword/accordion.js'
    ), "", 'sword/manager.min.js');
}, 10);


/**
 * Cache Killer
 * ------------
 *
 * Add global cache killer for article(s) and page(s).
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

// Article(s) and page(s)
Weapon::add('on_article_update', 'do_remove_cache', 10);
Weapon::add('on_page_update', 'do_remove_cache', 10);

// Plugin(s)
Weapon::add('on_plugin_update', function() {
    File::open(CACHE . DS . 'plugins.order.cache')->delete();
});


/**
 * Footer Link(s)
 * --------------
 */

if($config->page_type === 'manager') {
    // Add default article footer link(s)
    Weapon::add('article_footer', function($article) use($config, $speak) {
        $status = Mecha::alter(File::E($article->path), array(
            'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
            'archive' => Jot::span('info', Jot::icon('history') . ' ' . $speak->archive) . ' &middot; '
        ), "");
        echo $status . Cell::a($config->manager->slug . '/article/repair/id:' . $article->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/article/kill/id:' . $article->id, $speak->delete);
    }, 20);
    // Add default page footer link(s)
    Weapon::add('page_footer', function($page) use($config, $speak) {
        $status = Mecha::alter(File::E($page->path), array(
            'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
            'archive' => Jot::span('info', Jot::icon('history') . ' ' . $speak->archive) . ' &middot; '
        ), "");
        echo $status . Cell::a($config->manager->slug . '/page/repair/id:' . $page->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/page/kill/id:' . $page->id, $speak->delete);
    }, 20);
    // Add default comment footer link(s)
    Weapon::add('comment_footer', function($comment, $article) use($config, $speak) {
        $status = Mecha::alter($comment->state, array(
            'pending' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->pending) . ' &middot; '
        ), "");
        echo $status . Cell::a($config->manager->slug . '/comment/repair/id:' . $comment->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/comment/kill/id:' . $comment->id, $speak->delete);
    }, 20);
}


/**
 * Widget Manager Menu(s)
 * ----------------------
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

    // Side Menu
    $menus = array(
        $speak->config => array(
            'icon' => 'cogs',
            'url' => $config->manager->slug . '/config',
            'stack' => 9
        ),
        $speak->article => array(
            'icon' => 'file-text',
            'url' => $config->manager->slug . '/article',
            'stack' => 9.01
        ),
        $speak->page => array(
            'icon' => 'file',
            'url' => $config->manager->slug . '/page',
            'stack' => 9.02
        ),
        $speak->comment => array(
            'icon' => 'comments',
            'url' => $config->manager->slug . '/comment',
            'count' => $total,
            'stack' => 9.03
        ),
        $speak->tag => array(
            'icon' => 'tags',
            'url' => $config->manager->slug . '/tag',
            'stack' => 9.04
        ),
        $speak->menu => array(
            'icon' => 'bars',
            'url' => $config->manager->slug . '/menu',
            'stack' => 9.05
        ),
        $speak->asset => array(
            'icon' => 'briefcase',
            'url' => $config->manager->slug . '/asset',
            'stack' => 9.06
        ),
        $speak->field => array(
            'icon' => 'th-list',
            'url' => $config->manager->slug . '/field',
            'stack' => 9.07
        ),
        $speak->shortcode => array(
            'icon' => 'coffee',
            'url' => $config->manager->slug . '/shortcode',
            'stack' => 9.08
        ),
        $speak->shield => array(
            'icon' => 'shield',
            'url' => $config->manager->slug . '/shield',
            'stack' => 9.09
        ),
        $speak->plugin => array(
            'icon' => 'plug',
            'url' => $config->manager->slug . '/plugin',
            'stack' => 9.1
        ),
        $speak->cache => array(
            'icon' => 'clock-o',
            'url' => $config->manager->slug . '/cache',
            'stack' => 9.11
        ),
        $speak->backup => array(
            'icon' => 'life-ring',
            'url' => $config->manager->slug . '/backup',
            'stack' => 9.12
        )
    );

    // Top Menu
    $bars = array(
        $speak->user => array(
            'icon' => 'user',
            'description' => Guardian::get('author'),
            'stack' => 9
        ),
        $speak->config => array(
            'icon' => 'cog',
            'url' => $config->manager->slug . '/config',
            'stack' => 9.01
        ),
        $speak->log_out => array(
            'icon' => 'sign-out',
            'url' => $config->manager->slug . '/logout',
            'description' => $speak->log_out,
            'stack' => 30
        )
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
        $text = Config::speak($type);
        $text_repair = Config::speak('manager._this_', array($speak->edit, $text));
        $text_kill = Config::speak('manager._this_', array($speak->delete, $text));
        $bars[$text] = array(
            'icon' => 'plus',
            'url' => $config->manager->slug . '/' . $type . '/ignite',
            'description' => Config::speak('manager.title_new_', $text),
            'stack' => 9.03
        );
        $bars[$speak->edit] = array(
            'icon' => 'pencil',
            'url' => $config->manager->slug . '/' . $type . '/repair/id:' . $id,
            'description' => $text_repair,
            'stack' => 9.04
        );
        $bars[$speak->delete] = array(
            'icon' => 'times',
            'url' => $config->manager->slug . '/' . $type . '/kill/id:' . $id,
            'description' => $text_kill,
            'stack' => 9.05
        );
    } else {
        $link  = Cell::a($config->manager->slug . '/article/ignite', Config::speak('manager.title_new_', $speak->article));
        $link .= ' &middot; ';
        $link .= Cell::a($config->manager->slug . '/page/ignite', Config::speak('manager.title_new_', $speak->page));
        $bars[$speak->add] = array(
            'icon' => 'plus',
            'url' => $config->manager->slug . '/article/ignite',
            'description' => $link,
            'stack' => 9.03
        );
    }

    Config::merge('manager_menu', $menus);
    Config::merge('manager_bar', $bars);

});


/**
 * Backend Route(s)
 * ----------------
 *
 * Load the routes.
 *
 */

if($detour = File::exist(DECK . DS . 'workers' . DS . 'route.' . $uri_end . '.php')) {
    require $detour;
}