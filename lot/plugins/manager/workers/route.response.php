<?php


/**
 * Response Manager
 * ----------------
 */

Route::accept(array($config->manager->slug . '/(' . $response . ')', $config->manager->slug . '/(' . $response . ')/(:num)'), function($segment = "", $offset = 1) use($config, $speak, $post) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    File::write($config->{'__total_' . $segment . 's'})->saveTo(LOG . DS . $segment . 's.total.log', 0600);
    if($files = call_user_func('Get::' . $segment . 's', null, Request::get('filter', ""), 'txt,hold')) {
        $responses_id = Mecha::walk($files, function($v) {
            $parts = explode('_', File::B($v));
            return $parts[1];
        });
        if(strtoupper(Request::get('order', 'DESC')) === 'DESC') {
            rsort($responses_id);
        } else {
            sort($responses_id);
        }
        $responses_id = Mecha::eat($responses_id)->chunk($offset, $config->manager->per_page)->vomit();
        $responses = Mecha::walk($responses_id, function($v) use($segment) {
            return call_user_func('Get::' . $segment, $v);
        });
    } else {
        $files = array();
        $responses = false;
    }
    Config::set(array(
        'page_title' => $speak->{$segment . 's'} . $config->title_separator . $config->manager->title,
        'pages' => $responses,
        'offset' => $offset,
        'pagination' => Navigator::extract($files, $offset, $config->manager->per_page, $config->manager->slug . '/' . $segment),
        'cargo' => 'cargo.response.php'
    ));
    Shield::lot(array('segment' => array($segment, $post)))->attach('manager');
});


/**
 * Response Repairer/Igniter
 * -------------------------
 */

Route::accept(array($config->manager->slug . '/(' . $response . ')/ignite', $config->manager->slug . '/(' . $response . ')/repair/id:(:num)'), function($segment = "", $id = false) use($config, $speak, $post) {
    $units = array('name', 'email', 'url', 'message', 'content_type');
    foreach($units as $k => $v) {
        Weapon::add('tab_content_1_before', function($page, $segment) use($config, $speak, $v) {
            include __DIR__ . DS . 'unit' . DS . 'form' . DS . $v . '.php';
        }, $k + 1);
    }
    Weapon::add('tab_content_2_before', function($page, $segment) use($config, $speak) {
        $segment = $segment[0];
        include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'fields[].php';
    }, 1);
    File::write($config->{'__total_' . $segment . 's'})->saveTo(LOG . DS . $segment . 's.total.log', 0600);
    if($id && $response = call_user_func('Get::' . $segment, $id, array('message'))) {
        if( ! Guardian::happy(1)) {
            Shield::abort();
        }
        $title = $speak->editing . ': ' . ($response->permalink !== '#' ? Cell::a($response->permalink, $speak->{$segment}, true) : $speak->{$segment}) . $config->title_separator . $config->manager->title;
    } else {
        if($id !== false) {
            Shield::abort(); // File not found!
        }
        $response = Mecha::O(array(
            'id' => "",
            'path' => "",
            'post' => "",
            'parent' => "",
            'state' => 'pending',
            'date' => array('W3C' => ""),
            'name_raw' => Guardian::get('author'),
            'email' => Guardian::get('email'),
            'url_raw' => "",
            'status_raw' => Guardian::get('status_raw'),
            'content_type_raw' => $config->html_parser->active,
            'fields_raw' => array(),
            'message_raw' => ""
        ));
        $title = Config::speak('manager.title_new_', $speak->{$segment}) . $config->title_separator . $config->manager->title;
    }
    $G = array('data' => Mecha::A($response));
    Config::set(array(
        'page_title' => $title,
        'page' => $response,
        'html_parser' => array('active' => $response->content_type_raw),
        'cargo' => 'repair.response.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $rid = $id ? $id : time();
        $request['id'] = $rid;
        $request['post'] = Request::post('post');
        $request['parent'] = Request::post('parent');
        $extension = $request['extension'];
        $name = $request['name'];
        $email = $request['email'];
        $url = isset($request['url']) && trim($request['url']) !== "" ? $request['url'] : false;
        $message = $request['message'];
        $field = Request::post('fields', array());
        include __DIR__ . DS . 'task.fields.php';
        // Empty name field
        if(trim($name) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
            Guardian::memorize($request);
        }
        // Invalid email address
        if(trim($email) !== "" && ! Guardian::check($request['email'], '->email')) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }
        $email = Text::parse($email, '->broken_entity');
        // Check for empty message content
        if(trim($message) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->message));
            Guardian::memorize($request);
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            $header = array(
                'Name' => $name,
                'Email' => $email,
                'URL' => $url,
                'Status' => $request['status'],
                'Content Type' => Request::post('content_type', 'HTML'),
                'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
            );
            $_ = RESPONSE . DS . $segment . DS . Date::slug($request['post']) . '_' . Date::slug($rid) . '_' . ($request['parent'] ? Date::slug($request['parent']) : '0000-00-00-00-00-00') . $extension;
            // Ignite
            if( ! $id) {
                Page::header($header)->content($message)->saveTo($_);
            // Repair
            } else {
                Page::open($response->path)->header($header)->content($message)->save();
                File::open($response->path)->renameTo(File::B($_));
            }
            Notify::success(Config::speak('notify_success_' . ($id ? 'updated' : 'created'), $speak->{$segment}) . ($extension === '.txt' ? ' <a class="pull-right" href="' . call_user_func('Get::' . $post . 'Anchor', $request['post'])->url . '#' . $segment . '-' . $rid . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
            Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_' . ($id ? 'repair' : 'construct')), array($G, $P));
            Guardian::kick($config->manager->slug . '/' . $segment . '/repair/id:' . $rid . $config->url_query);
        }
    }
    Shield::lot(array('segment' => array($segment, $post)))->attach('manager');
});


/**
 * Response Killer
 * ---------------
 */

Route::accept($config->manager->slug . '/(' . $response . ')/kill/id:(:num)', function($segment = "", $id = "") use($config, $speak, $post) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! $response = call_user_func('Get::' . $segment, $id)) {
        Shield::abort(); // File not found!
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $speak->{$segment} . $config->title_separator . $config->manager->title,
        'page' => $response,
        'cargo' => 'kill.response.php'
    ));
    if($request = Request::post()) {
        $P = array('data' => Mecha::A($response));
        Guardian::checkToken($request['token']);
        File::open($response->path)->delete();
        $post_o = $post;
        $post = $response;
        // Do not remove substance data on response destruct
        // because different response(s) may refer to the same substance
        // include __DIR__ . DS . 'task.substance.kill.php';
        $post = $post_o;
        File::write($config->{'__total_' . $segment . 's'} - 1)->saveTo(LOG . DS . $segment . 's.total.log', 0600);
        Notify::success(Config::speak('notify_success_deleted', $speak->{$segment}));
        Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/' . $segment);
    } else {
        File::write($config->{'__total_' . $segment . 's'})->saveTo(LOG . DS . $segment . 's.total.log', 0600);
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::lot(array('segment' => array($segment, $post)))->attach('manager');
});