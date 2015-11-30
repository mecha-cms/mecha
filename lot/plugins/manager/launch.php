<?php


// URL segment(s)
$segment = explode('/', $config->url_path);
$segment = $config->page_type === 'manager' ? $segment[1] : $segment[0];


// Loading plug(s)
foreach(glob(__DIR__ . DS . 'workers' . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}


// Loading task(s)
foreach(glob(__DIR__ . DS . 'workers' . DS . '__task.*.php', GLOB_NOSORT) as $task) {
    require $task;
}


/**
 * Backend Asset(s)
 * ----------------
 *
 * Inject the required asset(s) for manager.
 *
 */

Weapon::add('meta', function() use($config) {
    echo O_BEGIN . '<script>!function(a){var b=a.className;a.className=/(^|\s)no-js(\s|$)/.test(b)?b.replace(/(^|\s)no-js(\s|$)/,"$1js$2"):b+" js"}(document.documentElement);</script>' . O_END;
    if( ! Asset::loaded($config->protocol . JS_LIBRARY_PATH)) {
        echo Asset::javascript($config->protocol . JS_LIBRARY_PATH);
    }
}, 20);

Weapon::add('shell_after', function() use($config) {
    $path = __DIR__ . DS . 'assets' . DS . 'shell' . DS;
    echo Asset::stylesheet(array(
        $path . 'row.css',
        $path . 'upload.css',
        $path . 'tab.css',
        $path . 'toggle.css',
        $path . 'modal.css',
        $path . 'tooltip.css',
        $path . 'sortable.css',
        $path . 'accordion.css',
        $path . 'layout.css',
        SHIELD . DS . $config->shield . DS . 'assets' . DS . 'shell' . DS . 'manager.css', // => taken from the current shield folder (if available)
    ), "", 'shell/manager.min.css');
    if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) {
        echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
    }
}, 10);

Weapon::add('cargo_after', function() use($config, $speak) {
    if(Config::get('widget_manager_bar_id', 0) <= 1) {
        echo Widget::manager('BAR'); // include once ...
    }
}, 20);

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak, $segment) {
    $parser = Config::get('html_parser', 'HTML');
    $path = __DIR__ . DS . 'assets' . DS . 'sword' . DS;
    echo Asset::javascript(array(
        $path . 'dashboard.js',
        $path . 'dashboard.task.extend.js',
        $path . 'dashboard.task.file.js',
        $path . 'dashboard.task.query.js',
        $path . 'dashboard.task.session.js',
        $path . 'dashboard.task.slug.js'
    ), "", 'sword/dashboard.min.js');
    $constant = get_defined_constants(true);
    $constant_js = "";
    foreach($constant['user'] as $k => $v) {
        $constant_js .= $k . '=' . json_encode($v) . ',';
    }
    unset($constant);
    $cargo = array(
        'segment' => $segment,
        'languages' => Config::get('DASHBOARD.languages', array()),
        'html_parser' => $parser !== false ? $parser : 'HTML',
        'file_extension_allow' => implode(',', File::$config['file_extension_allow']),
        'url' => array(
            'protocol' => $config->protocol,
            'base' => $config->base,
            'host' => $config->host,
            'root' => $config->url,
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

Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config) {
    Session::kill('recent_file_update');
    $path = __DIR__ . DS . 'assets' . DS . 'sword' . DS;
    echo Asset::javascript(array(
        $path . 'ajax.js',
        $path . 'row.js',
        $path . 'slug.js',
        $path . 'upload.js',
        $path . 'tab.js',
        $path . 'toggle.js',
        $path . 'modal.js',
        $path . 'tooltip.js',
        $path . 'sortable.js',
        $path . 'accordion.js',
        SHIELD . DS . $config->shield . DS . 'assets' . DS . 'sword' . DS . 'manager.js', // => taken from the current shield folder (if available)
    ), "", 'sword/manager.min.js');
}, 10);


/**
 * Footer Link(s)
 * --------------
 */

if($config->page_type === 'manager') {
    // Add default article footer link(s)
    Weapon::add('article_footer', function($article) use($config, $speak) {
        $e = File::E($article->path);
        $comments = count(glob(COMMENT . DS . Date::slug($article->time) . '_*_*.{txt,hold}', GLOB_NOSORT | GLOB_BRACE));
        $comments = $e !== 'draft' ? '<span title="' . $comments . ' ' . ($comments === 1 ? $speak->comment : $speak->comments) . '">' . Jot::icon('comments') . ' ' . $comments . '</span> &middot; ' : "";
        $status = Mecha::alter($e, array(
            'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
            'archive' => Jot::span('info', Jot::icon('history') . ' ' . $speak->archive) . ' &middot; '
        ), "");
        echo $comments . $status . Cell::a($config->manager->slug . '/article/repair/id:' . $article->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/article/kill/id:' . $article->id, $speak->delete);
    }, 20);
    // Add default page footer link(s)
    Weapon::add('page_footer', function($page) use($config, $speak) {
        $status = Mecha::alter(File::E($page->path), array(
            'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
            'archive' => Jot::span('info', Jot::icon('history') . ' ' . $speak->archive) . ' &middot; '
        ), "");
        echo $status . Cell::a($config->manager->slug . '/page/repair/id:' . $page->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/page/kill/id:' . $page->id, $speak->delete);
    }, 20);
}

if($config->page_type === 'manager' || $config->page_type === 'article') {
    // Add default comment footer link(s)
    Weapon::add('comment_footer', function($comment, $article) use($config, $speak) {
        $status = Mecha::alter($comment->state, array(
            'pending' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->pending) . ' &middot; '
        ), "");
        echo $status . Cell::a($config->manager->slug . '/comment/repair/id:' . $comment->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/comment/kill/id:' . $comment->id, $speak->delete);
    }, 20);
}


/**
 * Widget Manager Menu(s) and Bar(s)
 * ---------------------------------
 *
 * [1]. Config::merge('manager_menu', array());
 * [2]. Config::merge('manager_bar', array());
 *
 */

Weapon::add('shield_before', function() {

    $config = Config::get();
    $speak = Config::speak();

    $total = $config->total_comments_backend;
    $destination = LOG . DS . 'comments.total.log';
    if($file = File::exist($destination)) {
        $old = (int) File::open($file)->read();
        $total = ($total > $old) ? ($total - $old) : 0;
    } else {
        File::write($total)->saveTo($destination, 0600);
    }

    // Side Menu
    $menus = array(
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
        $speak->tag => array(
            'icon' => 'tags',
            'url' => $config->manager->slug . '/tag',
            'stack' => 9.04
        ),
        $speak->asset => array(
            'icon' => 'briefcase',
            'url' => $config->manager->slug . '/asset',
            'stack' => 9.06
        )
    );

    // Top Menu
    $bars = array(
        $speak->user => array(
            'icon' => 'user',
            'description' => Guardian::get('author'),
            'stack' => 9
        ),
        $speak->log_out => array(
            'icon' => 'sign-out',
            'url' => $config->manager->slug . '/logout',
            'description' => $speak->log_out,
            'stack' => 30
        )
    );

    // only for `pilot`
    if(Guardian::get('status') === 'pilot') {

        $menus[$speak->config] = array(
            'icon' => 'cogs',
            'url' => $config->manager->slug . '/config',
            'stack' => 9
        );
        $menus[$speak->comment] = array(
            'icon' => 'comments',
            'url' => $config->manager->slug . '/comment',
            'count' => $total,
            'stack' => 9.03
        );
        $menus[$speak->menu] = array(
            'icon' => 'bars',
            'url' => $config->manager->slug . '/menu',
            'stack' => 9.05
        );
        $menus[$speak->field] = array(
            'icon' => 'th-list',
            'url' => $config->manager->slug . '/field',
            'stack' => 9.07
        );
        $menus[$speak->shortcode] = array(
            'icon' => 'coffee',
            'url' => $config->manager->slug . '/shortcode',
            'stack' => 9.08
        );
        $menus[$speak->shield] = array(
            'icon' => 'shield',
            'url' => $config->manager->slug . '/shield',
            'stack' => 9.09
        );
        $menus[$speak->plugin] = array(
            'icon' => 'plug',
            'url' => $config->manager->slug . '/plugin',
            'stack' => 9.1
        );
        $menus[$speak->cache] = array(
            'icon' => 'clock-o',
            'url' => $config->manager->slug . '/cache',
            'stack' => 9.11
        );
        $menus[$speak->backup] = array(
            'icon' => 'life-ring',
            'url' => $config->manager->slug . '/backup',
            'stack' => 9.12
        );
        $bars[$speak->config] = array(
            'icon' => 'cog',
            'url' => $config->manager->slug . '/config',
            'stack' => 9.01
        );

        if($errors = File::exist(ini_get('error_log'))) {
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
 * Widget Manager
 * --------------
 *
 * [1]. Widget::manager('MENU');
 * [2]. Widget::manager('BAR');
 *
 */

Widget::plug('manager', function($type = 'MENU') {
    if( ! Guardian::happy()) return "";
    $T1 = TAB;
    $kin = strtolower($type);
    $id = Config::get('widget_manager_' . $kin . '_id', 0) + 1;
    $html = O_BEGIN . '<div class="widget widget-manager widget-manager-' . $kin . '" id="widget-manager-' . $kin . '-' . $id . '">' . NL;
    if($type === 'MENU') {
        $menus = array();
        if($_menus = Mecha::A(Config::get('manager_menu'))) {
            $_menus = Mecha::eat($_menus)->order('ASC', 'stack', true, 10)->vomit();
            foreach($_menus as $k => $v) {
                // < 1.1.3
                if(is_string($v)) {
                    $menus[$k] = $v;
                } else {
                    $stack = isset($v['stack']) ? $v['stack'] : 10;
                    $_k = (strpos($v['icon'], '<') === false ? Jot::icon($v['icon'], 'fw') : $v['icon']) . ' <span class="label">' . $k . '</span>' . (isset($v['count']) && ($v['count'] === '&infin;' || (float) $v['count'] > 0) ? ' <span class="counter">' . $v['count'] . '</span>' : "");
                    $menus[$_k] = isset($v['url']) ? $v['url'] : null;
                }
            }
        }
        Menu::add('manager', $menus);
        $html .= Menu::manager('ul', $T1);
    }
    if($type === 'BAR') {
        $bars = array();
        if($_bars = Mecha::A(Config::get('manager_bar'))) {
            $_bars = Mecha::eat($_bars)->order('ASC', 'stack', true, 10)->vomit();
            foreach($_bars as $k => $v) {
                if(is_string($v)) {
                    $bar = $v;
                } else {
                    $t = ' data-tooltip="' . Text::parse(isset($v['description']) ? $v['description'] : $k, '->encoded_html') . '"';
                    $bar = isset($v['url']) ? '<a class="item" href="' . Filter::apply('url', Converter::url($v['url'])) . '"' . $t . '>' : '<span class="item a"' . $t . '>';
                    $bar .= isset($v['icon']) ? (strpos($v['icon'], '<') === false ? Jot::icon($v['icon']) : $v['icon']) : $k;
                    $bar .= ' <span class="label">' . $k . '</span>';
                    if(isset($v['count']) && ($v['count'] === '&infin;' || (float) $v['count'] > 0)) {
                        $bar .= ' <span class="counter">' . $v['count'] . '</span>';
                    }
                    $bar .= isset($v['url']) ? '</a>' : '</span>';
                }
                $bars[] = $bar;
            }
        }
        $html .= $T1 . implode(' ', $bars) . NL;
    }
    $html .= '</div>' . O_END;
    $html = Filter::apply('widget', $html);
    Config::set('widget_manager_' . $kin . '_id', $id);
    return Filter::apply('widget:manager.' . $kin, Filter::apply('widget:manager', $html));
});


/**
 * Wizard Loader
 * -------------
 *
 * [1]. Guardian::wizard('menu');
 *
 */

Guardian::plug('wizard', function($name = "", $vars = array(), $folder = 'yap') {
    $name = File::path($name);
    $_file = false;
    $r = __DIR__ . DS . 'languages' . DS;
    if($file = File::exist($r . Config::get('language') . DS . $folder . DS . $name . '.txt')) {
        $_file = $file;
    } else if($file = File::exist($r . 'en_US' . DS . $folder . DS . $name . '.txt')) {
        $_file = $file;
    } else if($file = File::exist(ROOT . DS . $name . '.txt')) {
        $_file = $file;
    } else if($file = File::exist($name . '.txt')) {
        $_file = $file;
    }
    $wizard = $_file ? Text::toPage(File::open($_file)->read(), 'content', 'wizard:') : false;
    return $wizard ? vsprintf($wizard['content'], $vars) : "";
});