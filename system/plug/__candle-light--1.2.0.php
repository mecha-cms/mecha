<?php

// < 1.2.0
Weapon::add('shield_lot_before', function() {
    $config = Config::get();
    if(isset($config->defaults->article_custom_css)) {
        $config->defaults->article_css = $config->defaults->article_custom_css;
        $config->defaults->article_js = $config->defaults->article_custom_js;
    }
    if(isset($config->defaults->page_custom_css)) {
        $config->defaults->page_css = $config->defaults->page_custom_css;
        $config->defaults->page_js = $config->defaults->page_custom_js;
    }
    if(isset($config->spam_keywords)) {
        $config->keywords_spam = $config->spam_keywords;
        Config::set('keywords_spam', $config->keywords_spam);
    }
    if(isset($config->excerpt_length)) {
        $config->excerpt = (object) array(
            'length' => $config->excerpt_length,
            'tail' => $config->excerpt_tail,
            'id' => 'more:%d'
        );
    }
    if(is_bool($config->comments)) {
        $config->comments = (object) array(
            'allow' => $config->comments,
            'moderation' => $config->comment_moderation
        );
    }
    if(is_string($config->author)) {
        $config->author = (object) array(
            'name' => $config->author,
            'email' => $config->author_email,
            'url' => $config->author_profile_url
        );
        Config::set('author', $config->author);
        if($config->page_type === 'manager') {
            Notify::info('<strong>1.2.0</strong> &mdash; In your <a href="' . $config->url . '/' . $config->manager->slug . '/shield">shield</a> files, change all <code>$config->author</code> data to <code>$config->author->name</code>, <code>$config->author_email</code> data to <code>$config->author->email</code> and <code>$config->author_profile_url</code> data to <code>$config->author->url</code>. Then go to the <a href="' . $config->url . '/' . $config->manager->slug . '/config">configuration manager page</a> to kill this message by pressing the <strong>Update</strong> button.');
        }
    }
}, 1);

Weapon::add('on_config_update', function() {
    // Self destruct ...
    File::open(__FILE__)->delete();
    Notify::clear();
    Notify::success(Config::speak('notify_success_updated', Config::speak('config')));
});