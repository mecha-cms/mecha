<?php

if($config->page_type === 'article') {
    Weapon::add('on_comment_construct', function($G, $P) use($config, $speak) {
        if($article = $G['data']['article']) {
            $cn_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();
            $title = $article['title'];
            $url = $article['url'] . '#' . sprintf($G['data']['comment_id'], Date::format($P['data']['id'], 'U'));
            $topic = sprintf($cn_config['subject'], $title, $url);
            $parser = Request::post('content_type', $config->html_parser);
            $message  = '<blockquote><p>' . sprintf($cn_config['message'], $title, $url) . '</p></blockquote>';
            $message .= '<h3>' . $P['data']['name'] . '</h3>';
            $message .= $parser !== false && $parser !== 'HTML' ? Text::parse($P['data']['message'], '->html') : $P['data']['message'];
            $message .= '<p>';
            $message .= '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $P['data']['id'] . '">' . $speak->edit . '</a>';
            $message .= ' / ';
            $message .= '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $P['data']['id'] . '">' . $speak->delete . '</a>';
            $message .= '</p>';
            // Sending email notification ...
            if( ! Guardian::happy() && Notify::send($P['data']['email'], $config->author->email, $topic, $message, 'comment:')) {
                Weapon::fire('on_comment_notify_construct', array($P, $config->author->email, $topic, $message));
            }
        }
    });
}