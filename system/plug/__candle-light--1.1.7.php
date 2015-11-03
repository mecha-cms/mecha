<?php

Weapon::add('shield_before', function() {
    $config = Config::get();
    if( ! isset($config->defaults->article_css)) { // < 1.1.7
        $config->defaults->article_css = $config->defaults->article_custom_css;
        $config->defaults->article_js = $config->defaults->article_custom_js;
    }
    if( ! isset($config->defaults->page_css)) { // < 1.1.7
        $config->defaults->page_css = $config->defaults->page_custom_css;
        $config->defaults->page_js = $config->defaults->page_custom_js;
    }
    if( ! isset($config->keywords_spam)) { // < 1.1.7
        $config->keywords_spam = $config->spam_keywords;
        Config::set('keywords_spam', $config->keywords_spam);
    }
    if( ! is_object($config->author)) { // < 1.1.7
        $config->author = (object) array(
            'name' => $config->author,
            'email' => $config->author_email,
            'url' => $config->author_profile_url
        );
        Config::set('author', $config->author);
        if($config->page_type === 'manager') {
            Notify::info('<strong>1.1.7</strong> &mdash; Please change all <code>$config->author</code> to <code>$config->author->name</code>, <code>$config->author_email</code> to <code>$config->author->email</code> and <code>$config->author_profile_url</code> to <code>$config->author->url</code> in your <a href="' . $config->url . '/' . $config->manager->slug . '/shield">shield</a> files. Then go to the <a href="' . $config->url . '/' . $config->manager->slug . '/config">configuration manager page</a> to kill this message by pressing the <strong>Update</strong> button.');
        }
    }
});

Weapon::add('on_config_update', function() {
    // Self destruct ...
    File::open(SYSTEM . DS . 'plug' . DS . 'candle-light--1.1.7.php')->delete();
});