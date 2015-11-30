<?php


/**
 * Response Manager
 * ----------------
 */

Route::accept(array($config->manager->slug . '/(' . $response . ')', $config->manager->slug . '/(' . $response . ')/(:num)'), function($segment = "", $offset = 1) use($config, $speak, $post) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $offset = (int) $offset;
    File::write($config->{'total_' . $segment . 's_backend'})->saveTo(LOG . DS . $segment . 's.total.log', 0600);
    if($files = call_user_func('Get::' . $segment . 's', 'DESC', "", 'txt,hold')) {
        $responses = array();
        $responses_id = array();
        foreach($files as $file) {
            $parts = explode('_', File::B($file));
            $responses_id[] = $parts[1];
        }
        rsort($responses_id);
        foreach(Mecha::eat($responses_id)->chunk($offset, $config->manager->per_page)->vomit() as $v) {
            $responses[] = call_user_func('Get::' . $segment, $v);
        }
        unset($responses_id, $files);
    } else {
        $responses = false;
    }
    Config::set(array(
        'page_title' => $speak->{$segment . 's'} . $config->title_separator . $config->manager->title,
        'pages' => $responses,
        'offset' => $offset,
        'pagination' => Navigator::extract(call_user_func('Get::' . $segment . 's', 'DESC', "", 'txt,hold'), $offset, $config->manager->per_page, $config->manager->slug . '/' . $segment),
        'cargo' => 'cargo.response.php'
    ));
    Shield::lot(array('segment' => array($segment, $post)))->attach('manager');
});


/**
 * Response Repairer
 * -----------------
 */

Route::accept($config->manager->slug . '/(' . $response . ')/repair/id:(:num)', function($segment = "", $id = "") use($config, $speak, $post) {
    if( ! Guardian::happy(1) || ! $response = call_user_func('Get::' . $segment, $id)) {
        Shield::abort();
    }
    File::write($config->{'total_' . $segment . 's_backend'})->saveTo(LOG . DS . $segment . 's.total.log', 0600);
    $G = array('data' => Mecha::A($response));
    Config::set(array(
        'page_title' => $speak->editing . ': ' . $speak->{$segment} . $config->title_separator . $config->manager->title,
        'page' => $response,
        'html_parser' => $response->content_type_raw,
        'cargo' => 'repair.response.php'
    ));
    if($request = Request::post()) {
        $request['id'] = $id;
        $request['ua'] = isset($response->ua_raw) ? $response->ua_raw : false;
        $request['ip'] = isset($response->ip_raw) ? $response->ip_raw : false;
        $extension = $request['action'] === 'publish' ? '.txt' : '.hold';
        Guardian::checkToken($request['token']);
        // Empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->{$segment . '_name'}));
            Guardian::memorize($request);
        }
        // Invalid email address
        if(trim($request['email']) !== "" && ! Guardian::check($request['email'], '->email')) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            $name = $request['name'];
            $email = Text::parse($request['email'], '->broken_entity');
            $url = isset($request['url']) && trim($request['url']) !== "" ? $request['url'] : false;
            $message = $request['message'];
            $field = Request::post('fields', array());
            include __DIR__ . DS . 'task.field.2.php';
            include __DIR__ . DS . 'task.field.1.php';
            // Update data
            Page::open($response->path)->header(array(
                'Name' => $name,
                'Email' => $email,
                'URL' => $url,
                'Status' => $request['status'],
                'Content Type' => Request::post('content_type', 'HTML'),
                'UA' => $request['ua'],
                'IP' => $request['ip'],
                'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
            ))->content($message)->save();
            File::open($response->path)->renameTo(File::N($response->path) . $extension);
            Notify::success(Config::speak('notify_success_updated', $speak->{$segment}));
            Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_repair'), array($G, $P));
            Guardian::kick($config->manager->slug . '/' . $segment . '/repair/id:' . $id);
        }
    }
    Shield::lot(array('segment' => array($segment, $post)))->attach('manager');
});


/**
 * Response Killer
 * ---------------
 */

Route::accept($config->manager->slug . '/(' . $response . ')/kill/id:(:num)', function($segment = "", $id = "") use($config, $speak) {
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
        include __DIR__ . DS . 'task.field.3.php';
        $post = $post_o;
        File::write($config->{'total_' . $segment . 's_backend'} - 1)->saveTo(LOG . DS . $segment . 's.total.log', 0600);
        Notify::success(Config::speak('notify_success_deleted', $speak->{$segment}));
        Weapon::fire(array('on_' . $segment . '_update', 'on_' . $segment . '_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/' . $segment);
    } else {
        File::write($config->{'total_' . $segment . 's_backend'})->saveTo(LOG . DS . $segment . 's.total.log', 0600);
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::lot(array('segment' => array($segment, $post)))->attach('manager');
});