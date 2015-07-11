<?php


/**
 * Create New Comment
 * ------------------
 */

Weapon::add('shield_before', function() use($config, $speak) {
    $comment_id = 'comment-%d'; // Your comment ID
    $comment_form_id = 'comment-form'; // Your comment form ID
    $article = isset(Config::get('article')->path) ? Get::article(Config::get('article')->path) : false;
    if($article && $config->page_type === 'article' && Request::method('post')) {
        $request = Request::post();
        // Check token
        Guardian::checkToken($request['token'], $config->url_current . '#' . $comment_form_id);
        $extension = $config->comment_moderation && ! Guardian::happy() ? '.hold' : '.txt';
        // Check name
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->comment_name));
        }
        // Check email
        if(trim($request['email']) !== "") {
            if( ! Guardian::check($request['email'], '->email')) {
                Notify::error($speak->notify_invalid_email);
            } else {
                // Disallow passenger(s) from entering your email address in the comment email field
                if( ! Guardian::happy() && $request['email'] === $config->author_email) {
                    Notify::warning(Config::speak('notify_warning_forbidden_input', '<em>' . $request['email'] . '</em>', strtolower($speak->email)));
                }
            }
        } else {
            Notify::error(Config::speak('notify_error_empty_field', $speak->email));
        }
        // Check URL
        if(trim($request['url']) !== "" && ! Guardian::check($request['url'], '->url')) {
            Notify::error($speak->notify_invalid_url);
        }
        // Check message
        if(trim($request['message']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->comment_message));
        }
        // Check challenge
        if( ! Guardian::checkMath($request['math'])) {
            Notify::error($speak->notify_invalid_math_answer);
        }
        // Check name length
        if(Guardian::check($request['name'], '->too_long', 100)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->comment_name));
        }
        // Check email length
        if(Guardian::check($request['email'], '->too_long', 100)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->comment_email));
        }
        // Check URL length
        if(Guardian::check($request['url'], '->too_long', 100)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->comment_url));
        }
        // Check message length
        if(Guardian::check($request['message'], '->too_long', 1700)) {
            Notify::error(Config::speak('notify_error_too_long', $speak->comment_message));
        }
        // Check for spam keyword(s) in comment
        $fucking_words = explode(',', $config->spam_keywords);
        foreach($fucking_words as $spam) {
            $fuck = trim($spam);
            if($fuck !== "") {
                if(
                    $request['email'] === $fuck || // Block by email address
                    $fuck !== 'N/A' && Get::IP() === $fuck || // Block by IP address
                    strpos(strtolower($request['message']), strtolower($fuck)) !== false // Block by message word(s)
                ) {
                    Notify::warning($speak->notify_warning_intruder_detected . ' <strong class="text-error pull-right">' . $fuck . '</strong>');
                    break;
                }
            }
        }
        if( ! Notify::errors()) {
            $post = Date::format($article->time, 'Y-m-d-H-i-s');
            $id = (int) time();
            $parent = Request::post('parent');
            $P = array('data' => $request);
            $name = strip_tags($request['name']);
            $email = Text::parse($request['email'], '->broken_entity');
            $url = Request::post('url', false);
            $parser = strip_tags(Request::post('content_type', $config->html_parser));
            $message = $request['message'];
            $field = Request::post('fields', array());
            include DECK . DS . 'workers' . DS . 'task.field.1.php';
            // Temporarily disallow image(s) in comment to prevent XSS
            $message = strip_tags($message, '<br><img>' . ($parser === 'HTML' ? '<a><abbr><b><blockquote><code><del><dfn><em><i><ins><p><pre><span><strong><sub><sup><time><u><var>' : ""));
            $message = preg_replace('#(\!\[.*?\]\(.*?\))#','`$1`', $message);
            $message = preg_replace('#<img(\s[^<>]*?)>#', '&lt;img$1&gt;', $message);
            Page::header(array(
                'Name' => $name,
                'Email' => $email,
                'URL' => $url,
                'Status' => Guardian::happy() ? 'pilot' : 'passenger',
                'Content Type' => $parser,
                'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false,
                'UA' => Get::UA(),
                'IP' => Get::IP()
            ))->content($message)->saveTo(RESPONSE . DS . $post . '_' . Date::format($id, 'Y-m-d-H-i-s') . '_' . ($parent ? Date::format($parent, 'Y-m-d-H-i-s') : '0000-00-00-00-00-00') . $extension);
            Notify::success(Config::speak('notify_success_submitted', $speak->comment));
            if($extension === '.hold') Notify::info($speak->notify_info_comment_moderation);
            Weapon::fire('on_comment_update', array($P, $P));
            Weapon::fire('on_comment_construct', array($P, $P));
            if($config->comment_notification_email) {
                $mail  = '<p>' . Config::speak('comment_notification', $article->url . '#' . sprintf($comment_id, Date::format($id, 'U'))) . '</p>';
                $mail .= '<p><strong>' . $name . ':</strong></p>';
                $mail .= $parser !== 'HTML' ? Text::parse($message, '->html') : $message;
                $mail .= '<p>' . Date::format($id, 'Y/m/d H:i:s') . '</p>';
                // Sending email notification ...
                if( ! Guardian::happy()) {
                    if(Notify::send($request['email'], $config->author_email, $speak->comment_notification_subject, $mail, 'comment:')) {
                        Weapon::fire('on_comment_notification_construct', array($request, $config->author_email, $speak->comment_notification_subject, $mail));
                    }
                }
            }
            Guardian::kick($config->url_current . ( ! Guardian::happy() && $config->comment_moderation ? '#' . $comment_form_id : '#' . sprintf($comment_id, Date::format($id, 'U'))));
        } else {
            Guardian::kick($config->url_current . '#' . $comment_form_id);
        }
    }
});