<?php


/**
 * Post Manager
 * ------------
 */

Route::accept(array($config->manager->slug . '/(' . $post . ')', $config->manager->slug . '/(' . $post . ')/(:num)'), function($segment = "", $offset = 1) use($config, $speak) {
    $posts = false;
    $offset = (int) $offset;
    if($files = Mecha::eat(call_user_func('Get::' . $segment . 's', 'DESC', "", 'txt,draft,archive'))->chunk($offset, $config->manager->per_page)->vomit()) {
        $posts = array();
        foreach($files as $file) {
            $posts[] = call_user_func('Get::' . $segment . 'Header', $file);
        }
        unset($files);
    } else {
        if($offset !== 1) Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->{$segment . 's'} . $config->title_separator . $config->manager->title,
        'pages' => $posts,
        'offset' => $offset,
        'pagination' => Navigator::extract(call_user_func('Get::' . $segment . 's', 'DESC', "", 'txt,draft,archive'), $offset, $config->manager->per_page, $config->manager->slug . '/' . $segment),
        'cargo' => 'cargo.post.php'
    ));
    Shield::lot(array('segment' => $segment))->attach('manager');
});


/**
 * Post Repairer/Igniter
 * ---------------------
 */

Route::accept(array($config->manager->slug . '/(' . $post . ')/ignite', $config->manager->slug . '/(' . $post . ')/repair/id:(:num)'), function($segment = "", $id = false) use($config, $speak, $response) {
    if($id && $post = call_user_func('Get::' . $segment, $id, array('content', 'excerpt', 'tags'))) {
        $extension_o = $post->state === 'published' ? '.txt' : '.draft';
        if( ! Guardian::happy(1) && Guardian::get('author') !== $post->author) {
            Shield::abort();
        }
        if( ! File::exist(CUSTOM . DS . Date::slug($post->date->unix) . $extension_o)) {
            $post->css_raw = $config->defaults->{$segment . '_css'};
            $post->js_raw = $config->defaults->{$segment . '_js'};
        }
        $title = $speak->editing . ': ' . $post->title . $config->title_separator . $config->manager->title;
    } else {
        if($id !== false) {
            Shield::abort(); // File not found!
        }
        $post = Mecha::O(array(
            'id' => "",
            'path' => "",
            'state' => 'drafted',
            'date' => array('W3C' => ""),
            'kind' => array(),
            'slug' => "",
            'title_raw' => $config->defaults->{$segment . '_title'},
            'link_raw' => "",
            'description_raw' => "",
            'author_raw' => Guardian::get('author'),
            'content_type_raw' => $config->html_parser,
            'fields_raw' => array(),
            'content_raw' => $config->defaults->{$segment . '_content'},
            'css_raw' => $config->defaults->{$segment . '_css'},
            'js_raw' => $config->defaults->{$segment . '_js'}
        ));
        $title = Config::speak('manager.title_new_', $speak->{$segment}) . $config->title_separator . $config->manager->title;
    }
    $G = array('data' => Mecha::A($post));
    Config::set(array(
        'page_title' => $title,
        'page' => $post,
        'html_parser' => $post->content_type_raw,
        'cargo' => 'repair.post.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        include __DIR__ . DS . 'task.field.5.php';
        $extension = $request['action'] === 'publish' ? '.txt' : '.draft';
        $kind = isset($request['kind']) ? $request['kind'] : array(0);
        sort($kind);
        // Check for duplicate slug, except for the current old slug.
        // Allow user(s) to change their post slug, but make sure they
        // do not type the slug of another post.
        if(trim($slug) !== "" && $slug !== $post->slug && $files = call_user_func('Get::' . $segment . 's', 'DESC', "", 'txt,draft,archive')) {
            foreach($files as $file) {
                if(strpos(File::B($file), '_' . $slug . '.') !== false) {
                    Notify::error(Config::speak('notify_error_slug_exist', $slug));
                    Guardian::memorize($request);
                    break;
                }
            }
            unset($files);
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            include __DIR__ . DS . 'task.field.2.php';
            include __DIR__ . DS . 'task.field.1.php';
            include __DIR__ . DS . 'task.field.4.php';
            // Ignite
            if( ! $id) {
                $_ = POST . DS . $segment . DS . Date::slug($date) . '_' . implode(',', $kind) . '_' . $slug . $extension;
                Page::header($header)->content($content)->saveTo($_);
                include __DIR__ . DS . 'task.custom.2.php';
                Notify::success(Config::speak('notify_success_created', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . call_user_func('Get::' . $segment . 'Anchor', $_)->url . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_construct'), array($G, $P));
                Guardian::kick($config->manager->slug . '/' . $segment . '/repair/id:' . Date::format($date, 'U'));
            // Repair
            } else {
                Page::open($post->path)->header($header)->content($content)->save();
                File::open($post->path)->renameTo(Date::slug($date) . '_' . implode(',', $kind) . '_' . $slug . $extension);
                include __DIR__ . DS . 'task.custom.1.php';
                if($post->slug !== $slug && $php_file = File::exist(File::D($post->path) . DS . $post->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }
                Notify::success(Config::speak('notify_success_updated', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . call_user_func('Get::' . $segment . 'Anchor', $post->path)->url . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_repair'), array($G, $P));
                // Rename all response file(s) related to post if post date has been changed
                if(((string) $date !== (string) $post->date->W3C) && $responses = cal_user_func('Get::' . $response . 's', 'DESC', 'post:' . Date::slug($id), 'txt,hold')) {
                    foreach($responses as $v) {
                        $parts = explode('_', File::B($v));
                        $parts[0] = Date::slug($date);
                        File::open($v)->renameTo(implode('_', $parts));
                    }
                }
                Guardian::kick($config->manager->slug . '/' . $segment . '/repair/id:' . Date::format($date, 'U'));
            }
        }
    }
    Shield::lot(array('segment' => $segment))->attach('manager');
});


/**
 * Post Killer
 * -----------
 */

Route::accept($config->manager->slug . '/(' . $post . ')/kill/id:(:num)', function($segment = "", $id = "") use($config, $speak, $response) {
    if( ! $post = call_user_func('Get::' . $segment, $id, array('content', 'excerpt', 'tags'))) {
        Shield::abort();
    }
    if( ! Guardian::happy(1) && Guardian::get('author') !== $post->author) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $post->title . $config->title_separator . $config->manager->title,
        'page' => $post,
        'cargo' => 'kill.post.php'
    ));
    $G = array('data' => Mecha::A($post));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::open($post->path)->delete();
        // Deleting response(s) ...
        if($responses = call_user_func('Get::' . $response . 's', 'DESC', 'post:' . Date::slug($id), 'txt,hold')) {
            foreach($responses as $v) {
                File::open($v)->delete();
            }
        }
        $P = array('data' => $request);
        include __DIR__ . DS . 'task.field.3.php';
        include __DIR__ . DS . 'task.custom.3.php';
        Notify::success(Config::speak('notify_success_deleted', $post->title));
        Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_destruct'), array($G, $G));
        Guardian::kick($config->manager->slug . '/' . $segment);
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $post->title . '</strong>'));
        Notify::warning(Config::speak('notify_confirm_delete_page', strtolower($speak->{$segment})));
    }
    Shield::lot(array('segment' => $segment))->attach('manager');
});