<?php

Weapon::add('on_comment_construct', function($G, $P) use($config, $speak) {
    if($article = $G['data']['article']) {
        $cn_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();
        $title = $article['title'];
        $url = $article['url'] . '#' . sprintf($G['data']['comment_id'], Date::format($P['data']['id'], 'U'));
        $subject = sprintf($cn_config['subject'], $title, $url);
        $parser = Request::post('content_type', $config->html_parser);
        $mail  = '<blockquote><p>' . sprintf($cn_config['message'], $title, $url) . '</p></blockquote>';
        $mail .= '<h3>' . $P['data']['name'] . '</h3>';
        $mail .= $parser !== false && $parser !== 'HTML' ? Text::parse($P['data']['message'], '->html') : $P['data']['message'];
        $mail .= '<p>';
        $mail .= '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $P['data']['id'] . '">' . $speak->edit . '</a>';
        $mail .= ' / ';
        $mail .= '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $P['data']['id'] . '">' . $speak->delete . '</a>';
        $mail .= '</p>';
        // Sending email notification ...
        if( ! Guardian::happy() && Notify::send($P['data']['email'], $config->author->email, $subject, $mail, 'comment:')) {
            Weapon::fire('on_comment_notification_construct', array($P, $config->author->email, $subject, $mail));
        }
    }
});