<?php


/**
 * Comments Manager
 * ----------------
 */

Route::accept(array($config->manager->slug . '/comment', $config->manager->slug . '/comment/(:num)'), function($offset = 1) use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $offset = (int) $offset;
    File::write($config->total_comments_backend)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
    if($files = Get::comments(null, 'DESC', 'txt,hold')) {
        $comments = array();
        $comments_id = array();
        foreach($files as $file) {
            $parts = explode('_', basename($file));
            $comments_id[] = $parts[1];
        }
        rsort($comments_id);
        foreach(Mecha::eat($comments_id)->chunk($offset, $config->manager->per_page)->vomit() as $comment) {
            $comments[] = Get::comment($comment);
        }
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
    if(Guardian::get('status') != 'pilot' || ! $comment = Get::comment($id)) {
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
        $extension = $request['action'] == 'publish' ? '.txt' : '.hold';
        Guardian::checkToken($request['token']);
        // Empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_name)));
            Guardian::memorize($request);
        }
        // Invalid email address
        if(trim($request['email']) !== "" && ! Guardian::check($request['email'], '->email')) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }
        $P = array('data' => $request, 'action' => $request['action']);
        if( ! Notify::errors()) {
            // Restrict users from inputting the `SEPARATOR` constant
            // to prevent mistakes in parsing the file content
            $name = Text::ES($request['name']);
            $email = Text::parse($request['email'], '->ascii');
            $url = Text::ES(Request::post('url', '#'));
            $message = Text::ES($request['message']);
            // Update data
            $data  = 'Name: ' . $name . "\n";
            $data .= 'Email: ' . $email . "\n";
            $data .= 'URL: ' . $url . "\n";
            $data .= 'Status: ' . $request['status'] . "\n";
            $data .= 'Content Type: ' . Request::post('content_type', 'HTML') . "\n";
            $data .= 'UA: ' . $request['ua'] . "\n";
            $data .= 'IP: ' . $request['ip'] . "\n";
            $data .= "\n" . SEPARATOR . "\n\n" . $message;
            File::open($comment->path)->write($data)->save(0600)->renameTo(basename($comment->path, '.' . pathinfo($comment->path, PATHINFO_EXTENSION)) . $extension);
            Notify::success(Config::speak('notify_success_updated', array($speak->comment)));
            Weapon::fire('on_comment_update', array($G, $P));
            Weapon::fire('on_comment_repair', array($G, $P));
            Guardian::kick($config->manager->slug . '/comment/repair/id:' . $id);
        }
    }
    Shield::define('default', $comment)->attach('manager', false);
});


/**
 * Comment Killer
 * --------------
 */

Route::accept($config->manager->slug . '/comment/kill/id:(:num)', function($id = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
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
        File::write($config->total_comments_backend - 1)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
        Notify::success(Config::speak('notify_success_deleted', array($speak->comment)));
        Weapon::fire('on_comment_update', array($P, $P));
        Weapon::fire('on_comment_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/comment');
    } else {
        File::write($config->total_comments_backend)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.log', 0600);
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::attach('manager', false);
});