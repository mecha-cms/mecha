<?php


/**
 * Post Manager
 * ------------
 */

Route::accept(array($config->manager->slug . '/(' . $post . ')', $config->manager->slug . '/(' . $post . ')/(:num)'), function($segment = "", $offset = 1) use($config, $speak) {
    $posts = false;
    $s = call_user_func('Get::' . $segment . 's', strtoupper(Request::get('order', 'DESC')), Request::get('filter', ""), 'txt,draft,archive');
    if($files = Mecha::eat($s)->chunk($offset, $config->manager->per_page)->vomit()) {
        $posts = Mecha::walk($files, function($v) use($segment) {
            return call_user_func('Get::' . $segment . 'Header', $v);
        });
    } else {
        if($offset !== 1) Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->{$segment . 's'} . $config->title_separator . $config->manager->title,
        'pages' => $posts,
        'offset' => $offset,
        'pagination' => Navigator::extract($s, $offset, $config->manager->per_page, $config->manager->slug . '/' . $segment),
        'cargo' => 'cargo.post.php'
    ));
    Shield::lot(array('segment' => $segment))->attach('manager');
});


/**
 * Post Repairer/Igniter
 * ---------------------
 */

Route::accept(array($config->manager->slug . '/(' . $post . ')/ignite', $config->manager->slug . '/(' . $post . ')/repair/id:(:num)'), function($segment = "", $id = false) use($config, $speak, $response) {
    $units = array('title', 'slug', 'link', 'content', 'content_type', 'description', 'post' . DS . 'author');
    foreach($units as $k => $v) {
        Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak, $v) {
            include __DIR__ . DS . 'unit' . DS . 'form' . DS . $v . '.php';
        }, $k + 1);
    }
    $units = array('css', 'js');
    foreach($units as $k => $v) {
        Weapon::add('tab_content_2_before', function($page, $segment) use($config, $speak, $v) {
            include __DIR__ . DS . 'unit' . DS . 'form' . DS . $v . '.php';
        }, $k + 1);
    }
    Weapon::add('tab_content_3_before', function($page, $segment) use($config, $speak) {
        include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'fields[].php';
    }, 1);
    if($id && $post = call_user_func('Get::' . $segment, $id, array('content', 'excerpt', 'tags'))) {
        $extension_o = '.' . File::E($post->path);
        if( ! Guardian::happy(1) && Guardian::get('author') !== $post->author) {
            Shield::abort();
        }
        if( ! File::exist(CUSTOM . DS . Date::slug($post->date->unix) . $extension_o)) {
            $post->css_raw = $config->defaults->{$segment . '_css'};
            $post->js_raw = $config->defaults->{$segment . '_js'};
        }
        $title = $speak->editing . ': ' . ($post->state !== 'drafted' ? Cell::a($post->url, $post->title, true) : $post->title) . $config->title_separator . $config->manager->title;
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
            'content_type_raw' => $config->html_parser->active,
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
        'html_parser' => array('active' => $post->content_type_raw),
        'cargo' => 'repair.post.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid time pattern
        if(isset($request['date']) && trim($request['date']) !== "" && ! preg_match('#^\d{4,}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$#', $request['date'])) {
            Notify::error(Config::speak('notify_invalid_format', array($speak->date, '<code>' . date('c') . '</code>')));
            Guardian::memorize($request);
        }
        $rid = (int) date('U', isset($request['date']) && trim($request['date']) !== "" ? strtotime($request['date']) : time());
        // Set post date by submitted time, or by input value if available
        $date = date('c', $rid);
        // General field(s)
        $title = Text::parse(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s'), false), '->text', str_replace('<a>', "", WISE_CELL_I));
        $link = false;
        if(isset($request['link']) && trim($request['link']) !== "") {
            $_ = $request['link'];
            // Allow relative URL protocol
            if(strpos($_, '//') === 0) {
                $_ = $config->scheme . ':' . $_;
            }
            if( ! Guardian::check($_, '->url')) {
                Notify::error($speak->notify_invalid_url);
            } else {
                $link = $request['link'];
            }
        }
        // If you set the post slug value with a `*://` or `//` at the beginning,
        // then Mecha will treat it as an external link value for your post data.
        // The original slug value will be created automatically based on the
        // post title text, but you can edit it later.
        $_ = $request['slug'];
        if(strpos($_, '://') !== false || strpos($_, '//') === 0) {
            $slug = Text::parse($title, '->slug');
            // Allow relative URL protocol
            if(strpos($_, '//') === 0) {
                $_ = $config->scheme . ':' . $_;
            }
            if( ! Guardian::check($_, '->url')) {
                Notify::error($speak->notify_invalid_url);
            } else {
                $link = $request['slug'];
            }
        } else {
            $slug = Text::parse(Request::post('slug', $title, false), '->slug');
        }
        $slug = $slug === '--' ? 'post-' . time() : $slug;
        $content = $request['content'];
        $description = trim($request['description']);
        $author = strip_tags($request['author']);
        $css = trim(Request::post('css', "", false));
        $js = trim(Request::post('js', "", false));
        $field = Request::post('fields', array());
        // Slug must contains at least one letter or one `-`. This validation added
        // to prevent user(s) from inputting a page offset instead of post slug.
        // Because the URL pattern of post's index page is `{$post}/1` and the
        // URL pattern of post's single page is `{$post}/post-slug`
        if(is_numeric($slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }
        // Check for empty post content
        if(trim($content) === "") {
            Notify::error(Config::speak('notify_error__content_empty', strpos($speak->notify_error__content_empty, '%s') === 0 ? $speak->{$segment} : strtolower($speak->{$segment})));
            Guardian::memorize($request);
        }
        $extension = $request['extension'];
        $kind = isset($request['kind']) ? $request['kind'] : array();
        sort($kind);
        // Check for duplicate slug, except for the current old slug.
        // Allow user(s) to change their post slug, but make sure they
        // do not type the slug of another post.
        if($slug !== "" && $slug !== $post->slug && $files = call_user_func('Get::' . $segment . 's', null, "", 'txt,draft,archive')) {
            if(strpos(implode('%', $files), '_' . $slug . '.') !== false) {
                Notify::error(Config::speak('notify_error_slug_exist', $slug));
                Guardian::memorize($request);
            }
            unset($files);
        }
        $_ = POST . DS . $segment . DS . Date::slug($date) . '_' . implode(',', $kind) . '_' . $slug . $extension;
        $request['id'] = $rid;
        $request['date'] = $date;
        $request['kind'] = $kind;
        $request['slug'] = $slug;
        $request['path'] = $_;
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            include __DIR__ . DS . 'task.fields.php';
            $header = array(
                'Title' => $title,
                'Link' => $link,
                'Description' => $description !== "" ? Text::parse($description, '->encoded_json') : false,
                'Author' => $author,
                'Content Type' => Request::post('content_type', 'HTML'),
                'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
            );
            // Ignite
            if( ! $id) {
                Page::header($header)->content($content)->saveTo($_);
                if(( ! empty($css) && $css !== $config->defaults->{$segment . '_css'}) || ( ! empty($js) && $js !== $config->defaults->{$segment . '_js'})) {
                    File::write(Converter::ES($css) . "\n\n" . SEPARATOR . "\n\n" . Converter::ES($js))->saveTo(CUSTOM . DS . Date::slug($date) . $extension);
                    Weapon::fire(array('on_custom_update', 'on_custom_construct'), array($G, $P));
                }
            // Repair
            } else {
                Page::header($header)->content($content)->saveTo($post->path);
                File::open($post->path)->renameTo(File::B($_));
                $custom_ = CUSTOM . DS . Date::slug($post->date->W3C);
                if(file_exists($custom_ . $extension_o)) {
                    Weapon::fire('on_custom_update', array($G, $P));
                    if(trim(File::open($custom_ . $extension_o)->read()) === "" || trim(File::open($custom_ . $extension_o)->read()) === SEPARATOR || (empty($css) && empty($js)) || ($css === $config->defaults->{$segment . '_css'} && $js === $config->defaults->{$segment . '_js'})) {
                        // Always delete empty custom CSS and JavaScript file(s) ...
                        File::open($custom_ . $extension_o)->delete();
                        Weapon::fire('on_custom_destruct', array($G, $P));
                    } else {
                        File::write(Converter::ES($css) . "\n\n" . SEPARATOR . "\n\n" . Converter::ES($js))->saveTo($custom_ . $extension_o);
                        File::open($custom_ . $extension_o)->renameTo(Date::slug($date) . $extension);
                        Weapon::fire('on_custom_repair', array($G, $P));
                    }
                } else {
                    if(( ! empty($css) && $css !== $config->defaults->{$segment . '_css'}) || ( ! empty($js) && $js !== $config->defaults->{$segment . '_js'})) {
                        File::write(Converter::ES($css) . "\n\n" . SEPARATOR . "\n\n" . Converter::ES($js))->saveTo(CUSTOM . DS . Date::slug($date) . $extension_o);
                        Weapon::fire(array('on_custom_update', 'on_custom_construct'), array($G, $P));
                    }
                }
                if($post->slug !== $slug && $php_file = File::exist(File::D($post->path) . DS . $post->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }
                // Rename all response file(s) related to post if post date has been changed
                if(((string) $date !== (string) $post->date->W3C) && $responses = call_user_func('Get::' . $response . 's', null, 'post:' . $id, 'txt,hold')) {
                    foreach($responses as $v) {
                        $parts = explode('_', File::B($v));
                        $parts[0] = Date::slug($date);
                        File::open($v)->renameTo(implode('_', $parts));
                    }
                }
            }
            Notify::success(Config::speak('notify_success_' . ($id ? 'updated' : 'created'), $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . call_user_func('Get::' . $segment . 'Anchor', $_)->url . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
            Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_' . ($id ? 'repair' : 'construct')), array($G, $P));
            Guardian::kick($config->manager->slug . '/' . $segment . '/repair/id:' . Date::format($date, 'U') . $config->url_query);
        }
    }
    Shield::lot(array('segment' => $segment))->attach('manager');
});


/**
 * Post Killer
 * -----------
 */

Route::accept($config->manager->slug . '/(' . $post . ')/kill/id:(:num)', function($segment = "", $id = "") use($config, $speak, $response) {
    if( ! $post = call_user_func('Get::' . $segment, $id, array('content', 'tags'))) {
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
        if($responses = call_user_func('Get::' . $response . 's', null, 'post:' . $id, 'txt,hold')) {
            foreach($responses as $v) {
                File::open($v)->delete();
            }
        }
        $P = array('data' => $request);
        // Do not remove substance data on post destruct
        // because different post(s) may refer to the same substance
        // include __DIR__ . DS . 'task.substance.kill.php';
        // Deleting custom CSS and JavaScript file of post ...
        File::open(CUSTOM . DS . Date::slug($id) . '.txt')->delete();
        File::open(CUSTOM . DS . Date::slug($id) . '.draft')->delete();
        Weapon::fire(array('on_custom_update', 'on_custom_destruct'), array($G, $P));
        // Deleting custom PHP file of post ...
        File::open(File::D($post->path) . DS . $post->slug . '.php')->delete();
        Notify::success(Config::speak('notify_success_deleted', $post->title));
        Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_destruct'), array($G, $G));
        Guardian::kick($config->manager->slug . '/' . $segment);
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $post->title . '</strong>'));
        Notify::warning(Config::speak('notify_confirm_delete_page', strtolower($speak->{$segment}), strtolower($speak->{$response . 's'})));
    }
    Shield::lot(array('segment' => $segment))->attach('manager');
});