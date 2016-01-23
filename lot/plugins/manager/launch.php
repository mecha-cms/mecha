<?php


// URL segment(s)
$segment = explode('/', $config->url_path);
$segment = $config->page_type === 'manager' ? $segment[1] : $segment[0];

// Loading plug(s)
foreach(glob(__DIR__ . DS . 'workers' . DS . 'kernel' . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}

// Logged in user(s) only
if(Guardian::happy()) {


    /**
     * Backend Asset(s)
     * ----------------
     *
     * Inject the required asset(s) for manager.
     *
     */

    Weapon::add('meta', function() use($config, $speak, $segment) {
        echo O_BEGIN . '<script>!function(a){var b=a.className;a.className=/(^|\s)no-js(\s|$)/.test(b)?b.replace(/(^|\s)no-js(\s|$)/,"$1js$2"):b+" js"}(document.documentElement);</script>' . O_END;
        if( ! Asset::loaded($config->protocol . JS_LIBRARY_PATH)) {
            echo Asset::javascript($config->protocol . JS_LIBRARY_PATH);
        }
        $path = __DIR__ . DS . 'assets' . DS . 'sword' . DS;
        echo Asset::javascript(array(
            $path . 'dashboard.js',
            $path . 'dashboard.task.extend.js',
            $path . 'dashboard.task.file.js',
            $path . 'dashboard.task.query.js',
            $path . 'dashboard.task.session.js',
            $path . 'dashboard.task.slug.js',
            $path . 'dashboard.add.js'
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
            'html_parser' => Config::get('html_parser'),
            'file_extension_allow' => implode(',', File::$config['file_extension_allow']),
            'url' => array(
                'protocol' => $config->url_protocol,
                'base' => $config->url_base,
                'host' => $config->url_host,
                'url' => $config->url_url,
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
    }, 20);

    Weapon::add('shell_after', function() use($config) {
        if( ! Asset::loaded($config->protocol . ICON_LIBRARY_PATH)) {
            echo Asset::stylesheet($config->protocol . ICON_LIBRARY_PATH);
        }
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
            SHIELD . DS . $config->shield . DS . 'assets' . DS . 'shell' . DS . 'manager.css', // => taken from the current shield folder (if any)
        ), "", 'shell/manager.min.css');
    }, 10);

    Weapon::add('cargo_after', function() use($config, $speak) {
        if(Config::get('widget_manager_bar_id', 0) <= 1) {
            echo Widget::manager('BAR'); // include once ...
        }
    }, 20);

    Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config) {
        Session::kill('recent_item_update');
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
            SHIELD . DS . $config->shield . DS . 'assets' . DS . 'sword' . DS . 'manager.js', // => taken from the current shield folder (if any)
        ), "", 'sword/manager.min.js');
    }, 1);


    /**
     * Footer Link(s)
     * --------------
     */

    if($config->page_type === 'manager' || $config->is->post) {
        // Add default comment footer link(s)
        Weapon::add('comment_footer', function($comment, $article) use($config, $speak) {
            $status = Mecha::alter(File::E($comment->path), array(
                'hold' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->pending) . ' &middot; '
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

        $total = $config->__total_comments;
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
        if(Guardian::happy(1)) {

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

        if($config->page_type !== '404' && $config->is->post) {
            $type = $config->page_type;
            $id = $config->{$type}->id;
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

}


// Loading frontend route(s)    
Weapon::add('routes_before', function() use($config, $speak) {
    require __DIR__ . DS . 'workers' . DS . 'route.login.php';
});

// Add log in/out link in shield footer
function do_footer_manager_link($content, $path) {
    global $config, $speak;
    if(File::N($path) === 'block.footer.bar') {
        $s = Guardian::happy() ? '<a href="' . Filter::colon('manager:url', $config->url . '/' . $config->manager->slug . '/logout') . '" rel="nofollow">' . $speak->log_out . '</a>' : '<a href="' . Filter::colon('manager:url', $config->url . '/' . $config->manager->slug . '/login') . '" rel="nofollow">' . $speak->log_in . '</a>';
        return str_replace('<div class="blog-footer-right">', '<div class="blog-footer-right">' . $s, $content);
    }
    return $content;
}

// Apply `do_footer_manager_link` filter
Filter::add('chunk:output', 'do_footer_manager_link');