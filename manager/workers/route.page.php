<?php


/**
 * Page Manager
 * ------------
 */

Route::accept(array($config->manager->slug . '/page', $config->manager->slug . '/page/(:num)'), function($offset = 1) use($config, $speak) {
    $pages = false;
    $offset = (int) $offset;
    if($files = Mecha::eat(Get::pages('DESC', "", 'txt,draft,archive'))->chunk($offset, $config->manager->per_page)->vomit()) {
        $pages = array();
        foreach($files as $file) {
            $pages[] = Get::pageHeader($file);
        }
        unset($files);
    } else {
        if($offset !== 1) Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->pages . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::pages('DESC', "", 'txt,draft,archive'), $offset, $config->manager->per_page, $config->manager->slug . '/page'),
        'cargo' => DECK . DS . 'workers' . DS . 'cargo.page.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Page Composer/Updater
 * ---------------------
 */

Route::accept(array($config->manager->slug . '/page/ignite', $config->manager->slug . '/page/repair/id:(:num)'), function($id = false) use($config, $speak) {
    Config::set('cargo', DECK . DS . 'workers' . DS . 'repair.page.php');
    if($id && $page = Get::page($id, array('content', 'excerpt', 'tags', 'comments'))) {
        $extension_o = $page->state === 'published' ? '.txt' : '.draft';
        if(Guardian::get('status') !== 'pilot' && Guardian::get('author') !== $page->author) {
            Shield::abort();
        }
        if( ! isset($page->fields)) {
            $page->fields = array();
        }
        if( ! isset($page->content_type)) {
            $page->content_type = $config->html_parser;
        }
        if( ! File::exist(CUSTOM . DS . date('Y-m-d-H-i-s', $page->date->unix) . $extension_o)) {
            $page->css_raw = $config->defaults->page_custom_css;
            $page->js_raw = $config->defaults->page_custom_js;
        }
        // Remove automatic page description data from page composer
        $test = explode(SEPARATOR, str_replace("\r", "", file_get_contents($page->path)), 2);
        if(strpos($test[0], "\n" . 'Description' . S . ' ') === false) {
            $page->description = "";
        }
        unset($test);
        Config::set(array(
            'page_title' => $speak->editing . ': ' . $page->title . $config->title_separator . $config->manager->title,
            'page' => Mecha::A($page)
        ));
    } else {
        if($id !== false) {
            Shield::abort(); // File not found!
        }
        $page = Mecha::O(array(
            'id' => "",
            'path' => "",
            'state' => 'draft',
            'date' => array('W3C' => ""),
            'title' => $config->defaults->page_title,
            'slug' => "",
            'content_raw' => $config->defaults->page_content,
            'content_type' => $config->html_parser,
            'description' => "",
            'author' => Guardian::get('author'),
            'css_raw' => $config->defaults->page_custom_css,
            'js_raw' => $config->defaults->page_custom_js,
            'fields' => array()
        ));
        Config::set(array(
            'page_title' => Config::speak('manager.title_new_', $speak->page) . $config->title_separator . $config->manager->title,
            'page' => Mecha::A($page)
        ));
    }
    $G = array('data' => Mecha::A($page));
    Config::set('html_parser', $page->content_type);
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $task_connect = $page;
        include DECK . DS . 'workers' . DS . 'task.field.5.php';
        include DECK . DS . 'workers' . DS . 'task.field.6.php';
        $extension = $request['action'] === 'publish' ? '.txt' : '.draft';
        // Check for duplicate slug, except for the current old slug.
        // Allow user(s) to change their post slug, but make sure they
        // do not type the slug of another post.
        if(trim($slug) !== "" && $slug !== $page->slug && $files = Get::pages('DESC', "", 'txt,draft,archive')) {
            foreach($files as $file) {
                if(strpos(File::B($file), '_' . $slug . '.') !== false) {
                    Notify::error(Config::speak('notify_error_slug_exist', $slug));
                    Guardian::memorize($request);
                    break;
                }
            }
            unset($files);
        }
        // Slug must contains at least one letter or one `-`
        if( ! preg_match('#[a-z\-]#i', $slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }
        // Check for empty post content
        if(trim($content) === "") {
            Notify::error(Config::speak('notify_error__content_empty', strpos($speak->notify_error__content_empty, '%s') === 0 ? $speak->page : strtolower($speak->page)));
            Guardian::memorize($request);
        }
        $P = array('data' => $request, 'action' => $request['action']);
        if( ! Notify::errors()) {
            include DECK . DS . 'workers' . DS . 'task.field.2.php';
            include DECK . DS . 'workers' . DS . 'task.field.1.php';
            include DECK . DS . 'workers' . DS . 'task.field.4.php';
            $task_connect_page = $page;
            $task_connect_page_css = $config->defaults->page_custom_css;
            $task_connect_page_js = $config->defaults->page_custom_js;
            // Ignite
            if( ! $id) {
                Page::header($header)->content($content)->saveTo(PAGE . DS . Date::format($date, 'Y-m-d-H-i-s') . '__' . $slug . $extension);
                include DECK . DS . 'workers' . DS . 'task.custom.2.php';
                Notify::success(Config::speak('notify_success_created', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_page_update', array($G, $P));
                Weapon::fire('on_page_construct', array($G, $P));
                Guardian::kick($config->manager->slug . '/page/repair/id:' . Date::format($date, 'U'));
            // Repair
            } else {
                Page::open($page->path)->header($header)->content($content)->save();
                File::open($page->path)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '__' . $slug . $extension);
                include DECK . DS . 'workers' . DS . 'task.custom.1.php';
                if($page->slug !== $slug && $php_file = File::exist(File::D($page->path) . DS . $page->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }
                Notify::success(Config::speak('notify_success_updated', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_page_update', array($G, $P));
                Weapon::fire('on_page_repair', array($G, $P));
                Guardian::kick($config->manager->slug . '/page/repair/id:' . Date::format($date, 'U'));
            }
        }
    }
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('manager/assets/sword/editor.compose.js', "", 'editor.compose.min.js');
    }, 11);
    Shield::lot('default', $page)->attach('manager', false);
});


/**
 * Page Killer
 * -----------
 */

Route::accept($config->manager->slug . '/page/kill/id:(:num)', function($id = "") use($config, $speak) {
    if( ! $page = Get::page($id, array('comments'))) {
        Shield::abort();
    }
    if(Guardian::get('status') !== 'pilot' && Guardian::get('author') !== $page->author) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $page->title . $config->title_separator . $config->manager->title,
        'page' => $page,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.page.php'
    ));
    $G = array('data' => Mecha::A($page));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::open($page->path)->delete();
        $task_connect = $page;
        include DECK . DS . 'workers' . DS . 'task.field.3.php';
        include DECK . DS . 'workers' . DS . 'task.custom.3.php';
        Notify::success(Config::speak('notify_success_deleted', $page->title));
        Weapon::fire('on_page_update', array($G, $G));
        Weapon::fire('on_page_destruct', array($G, $G));
        Guardian::kick($config->manager->slug . '/page');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $page->title . '</strong>'));
        Notify::warning(Config::speak('notify_confirm_delete_page', strtolower($speak->page)));
    }
    Shield::attach('manager', false);
});