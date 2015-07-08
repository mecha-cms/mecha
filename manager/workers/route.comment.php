<?php


/**
 * Comment Manager
 * ---------------
 */

Route::accept(array($config->manager->slug . '/comment', $config->manager->slug . '/comment/(:num)'), function($offset = 1) use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
    $offset = (int) $offset;
    File::write($config->total_comments_backend)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
    if($files = Get::comments(null, 'DESC', 'txt,hold')) {
        $comments = array();
        $comments_id = array();
        foreach($files as $file) {
            $parts = explode('_', File::B($file));
            $comments_id[] = $parts[1];
        }
        rsort($comments_id);
        foreach(Mecha::eat($comments_id)->chunk($offset, $config->manager->per_page)->vomit() as $comment) {
            $comments[] = Get::comment($comment);
        }
        unset($comments_id, $files);
    } else {
        $comments = false;
    }
    Config::set(array(
        'page_title' => $speak->comments . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'responses' => $comments,
        'pagination' => Navigator::extract(Get::comments(null, 'DESC', 'txt,hold'), $offset, $config->manager->per_page, $config->manager->slug . '/comment'),
        'cargo' => DECK . DS . 'workers' . DS . 'comment.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Comment Repair
 * --------------
 */

Route::accept($config->manager->slug . '/comment/repair/id:(:num)', function($id = "") use($config, $speak) {
    if(Guardian::get('status') !== 'pilot' || ! $comment = Get::comment($id)) {
        Shield::abort();
    }
    if( ! isset($comment->content_type)) {
        $comment->content_type = $config->html_parser;
    }
    File::write($config->total_comments_backend)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
    Config::set(array(
        'page_title' => $speak->editing . ': ' . $speak->comment . $config->title_separator . $config->manager->title,
        'response' => Mecha::A($comment),
        'cargo' => DECK . DS . 'workers' . DS . 'repair.comment.php'
    ));
    $G = array('data' => Mecha::A($comment));
    Config::set('html_parser', $comment->content_type);
    if($request = Request::post()) {
        $request['id'] = $id;
        $request['ua'] = isset($comment->ua) ? $comment->ua : 'N/A';
        $request['ip'] = isset($comment->ip) ? $comment->ip : 'N/A';
        $request['message_raw'] = $request['message'];
        $extension = $request['action'] === 'publish' ? '.txt' : '.hold';
        Guardian::checkToken($request['token']);
        // Empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->comment_name));
            Guardian::memorize($request);
        }
        // Invalid email address
        if(trim($request['email']) !== "" && ! Guardian::check($request['email'], '->email')) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }
        $P = array('data' => $request, 'action' => $request['action']);
        if( ! Notify::errors()) {
            $name = $request['name'];
            $email = Text::parse($request['email'], '->ascii');
            $url = Request::post('url', false);
            $message = $request['message'];
            $field = Request::post('fields', array());
            // New asset data
            if(isset($_FILES) && ! empty($_FILES)) {
                $accept = File::$config['file_extension_allow'];
                foreach($_FILES as $k => $v) {
                    if(isset($field[$k]['accept'])) {
                        File::$config['file_extension_allow'] = explode(',', $field[$k]['accept']);
                    }
                    if($v['size'] > 0 && $v['error'] === 0) {
                        File::upload($v, SUBSTANCE);
                        if( ! Notify::errors()) {
                            $field[$k]['value'] = Text::parse($v['name'], '->safe_file_name');
                        }
                    }
                }
                File::$config['file_extension_allow'] = $accept;
                unset($accept);
            }
            // Remove empty field value
            foreach($field as $k => $v) {
                if(isset($v['remove']) && $v['type'][0] === 'f') {
                    // Remove asset field and data
                    File::open(SUBSTANCE . DS . $v['remove'])->delete();
                    Notify::success(Config::speak('notify_file_deleted', '<code>' . $v['remove'] . '</code>'));
                    unset($field[$k]);
                }
                if(( ! isset($v['value']) || $v['value'] === "") || ( ! file_exists(SUBSTANCE . DS . $v['value']) && $v['type'][0] === 'f')) {
                    unset($field[$k]);
                }
            }
            // Update data
            Page::open($comment->path)->header(array(
                'Name' => $name,
                'Email' => $email,
                'URL' => $url,
                'Status' => $request['status'],
                'Content Type' => Request::post('content_type', 'HTML'),
                'UA' => $request['ua'] !== 'N/A' ? $request['ua'] : false,
                'IP' => $request['ip'] !== 'N/A' ? $request['ip'] : false,
                'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
            ))->content($message)->save();
            File::open($comment->path)->renameTo(File::N($comment->path) . $extension);
            Notify::success(Config::speak('notify_success_updated', $speak->comment));
            Weapon::fire('on_comment_update', array($G, $P));
            Weapon::fire('on_comment_repair', array($G, $P));
            Guardian::kick($config->manager->slug . '/comment/repair/id:' . $id);
        }
    }
    Shield::lot('default', $comment)->attach('manager', false);
});


/**
 * Comment Killer
 * --------------
 */

Route::accept($config->manager->slug . '/comment/kill/id:(:num)', function($id = "") use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
    if( ! $comment = Get::comment($id)) {
        Shield::abort(); // File not found!
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $speak->comment . $config->title_separator . $config->manager->title,
        'response' => $comment,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.comment.php'
    ));
    if($request = Request::post()) {
        $P = array('data' => Mecha::A($comment));
        Guardian::checkToken($request['token']);
        File::open($comment->path)->delete();
        // Deleting substance(s)
        if(isset($comment->fields) && is_object($comment->fields)) {
            foreach($comment->fields as $field) {
                $file = SUBSTANCE . DS . $field;
                if(file_exists($file) && is_file($file)) {
                    File::open($file)->delete();
                }
            }
        }
        File::write($config->total_comments_backend - 1)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
        Notify::success(Config::speak('notify_success_deleted', $speak->comment));
        Weapon::fire('on_comment_update', array($P, $P));
        Weapon::fire('on_comment_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/comment');
    } else {
        File::write($config->total_comments_backend)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::attach('manager', false);
});