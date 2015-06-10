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
        'cargo' => DECK . DS . 'workers' . DS . 'page.php'
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
        if(strpos($test[0], "\n" . 'Description: ') === false) {
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
        // Check for invalid time pattern
        if(isset($request['date']) && trim($request['date']) !== "" && ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $request['date'])) {
            Notify::error($speak->notify_invalid_time_pattern);
            Guardian::memorize($request);
        }
        $request['id'] = (int) date('U', isset($request['date']) && trim($request['date']) !== "" ? strtotime($request['date']) : time());
        $request['path'] = $page->path;
        $request['state'] = $request['action'] === 'publish' ? 'published' : 'draft';
        $extension = $request['action'] === 'publish' ? '.txt' : '.draft';
        // Set post date by submitted time, or by input value if available
        $date = date('c', $request['id']);
        // General fields
        $title = trim(strip_tags(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s')), '<abbr><b><code><del><dfn><em><i><ins><span><strong><sub><sup><time><u><var>'));
        $slug = Text::parse(Request::post('slug', $title), '->slug');
        $slug = $slug === '--' ? Text::parse($title, '->slug') : $slug;
        $content = Request::post('content', "");
        $description = $request['description'];
        $author = strip_tags($request['author']);
        $css = trim(Request::post('css', ""));
        $js = trim(Request::post('js', ""));
        $field = Request::post('fields', array());
        // Remove empty field value
        foreach($field as $k => $v) {
            if(
                $v['type'][0] === 't' && $v['value'] === "" ||
                $v['type'][0] === 's' && $v['value'] === "" ||
                $v['type'][0] === 'b' && ! isset($v['value']) ||
                $v['type'][0] === 'o' && ( ! isset($v['value']) || $v['value'] === "")
            ) {
                unset($field[$k]);
            }
        }
        // Check for duplicate slug
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
        // Slug must contains at least one letter or one `-`
        if( ! preg_match('#[a-z\-]#i', $slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }
        // Check for empty post content
        if(trim($content) === "") {
            Notify::error($speak->notify_error_post_content_empty);
            Guardian::memorize($request);
        }
        $header = array(
            'Title' => $title,
            'Description' => trim($description) !== "" ? Text::parse(trim($description), '->encoded_json') : false,
            'Author' => $author,
            'Content Type' => Request::post('content_type', 'HTML'),
            'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
        );
        $P = array('data' => $request, 'action' => $request['action']);
        // New
        if( ! $id) {
            // Check for duplicate slug
            if($files = Get::pages('DESC', "", 'txt,draft,archive')) {
                foreach($files as $file) {
                    if(strpos(basename($file), '_' . $slug . '.') !== false) {
                        Notify::error(Config::speak('notify_error_slug_exist', $slug));
                        Guardian::memorize($request);
                        break;
                    }
                }
                unset($files);
            }
            if( ! Notify::errors()) {
                Page::header($header)->content($content)->saveTo(PAGE . DS . Date::format($date, 'Y-m-d-H-i-s') . '__' . $slug . $extension);
                if(( ! empty($css) && $css !== $config->defaults->page_custom_css) || ( ! empty($js) && $js !== $config->defaults->page_custom_js)) {
                    Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension);
                }
                Notify::success(Config::speak('notify_success_created', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_page_update', array($G, $P));
                Weapon::fire('on_page_construct', array($G, $P));
                Guardian::kick($config->manager->slug . '/page/repair/id:' . Date::format($date, 'U'));
            }
        // Repair
        } else {
            // Check for duplicate slug, except for the current old slug.
            // Allow users to change their post slug, but make sure they
            // do not type the slug of another post.
            if($files = Get::pages('DESC', "", 'txt,draft,archive') && $slug !== $page->slug) {
                foreach($files as $file) {
                    if(strpos(basename($file), '_' . $slug . '.') !== false) {
                        Notify::error(Config::speak('notify_error_slug_exist', $slug));
                        Guardian::memorize($request);
                        break;
                    }
                }
                unset($files);
            }
            // Start rewriting ...
            if( ! Notify::errors()) {
                Page::open($page->path)->header($header)->content($content)->save();
                File::open($page->path)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '__' . $slug . $extension);
                $custom_ = CUSTOM . DS . Date::format($page->date->W3C, 'Y-m-d-H-i-s');
                if(File::exist($custom_ . $extension_o)) {
                    if(trim(File::open($custom_ . $extension_o)->read()) === "" || trim(File::open($custom_ . $extension_o)->read()) === SEPARATOR || (empty($css) && empty($js)) || ($css === $config->defaults->page_custom_css && $js === $config->defaults->page_custom_js)) {
                        // Always delete empty custom CSS and JavaScript files ...
                        File::open($custom_ . $extension_o)->delete();
                    } else {
                        Page::content($css)->content($js)->saveTo($custom_ . $extension_o);
                        File::open($custom_ . $extension_o)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . $extension);
                    }
                } else {
                    if(( ! empty($css) && $css !== $config->defaults->page_custom_css) || ( ! empty($js) && $js !== $config->defaults->page_custom_js)) {
                        Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension);
                    }
                }
                if($page->slug !== $slug && $php_file = File::exist(dirname($page->path) . DS . $page->slug . '.php')) {
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
        echo Asset::javascript('manager/sword/editor.compose.js', "", 'editor.compose.min.js');
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
        // Deleting custom CSS and JavaScript file of page ...
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.draft')->delete();
        // Deleting custom PHP file of page ...
        File::open(dirname($page->path) . DS . $page->slug . '.php')->delete();
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