<?php

Weapon::add('on_comment_construct', function($G, $P) use($config, $speak) {
    if($config->is->post && $post = $G['data'][$config->page_type]) {
        $c = $config->states->{'plugin_' . md5(File::B(__DIR__))};
        $title = $post['title'];
        $url = $post['url'] . '#' . sprintf($G['data']['comment_id'], Date::format($P['data']['id'], 'U'));
        $topic = sprintf($c->subject, $title, $url);
        $parser = Request::post('content_type', $config->html_parser->active);
        $message  = '<blockquote><p>' . sprintf($c->message, $title, $url) . '</p></blockquote>';
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