<?php

// < 1.2.0
Weapon::add('plugins_before', function() use(&$config) {
    $c = Config::get();
    if(isset($c->defaults->article_custom_css)) {
        $c->defaults->article_css = $c->defaults->article_custom_css;
        $c->defaults->article_js = $c->defaults->article_custom_js;
    }
    if(isset($c->defaults->page_custom_css)) {
        $c->defaults->page_css = $c->defaults->page_custom_css;
        $c->defaults->page_js = $c->defaults->page_custom_js;
    }
    if(isset($c->spam_keywords)) {
        $c->keywords_spam = $c->spam_keywords;
    }
    if(isset($c->excerpt_length)) {
        $c->excerpt = (object) array(
            'length' => $c->excerpt_length,
            'prefix' => "",
            'suffix' => $c->excerpt_tail,
            'id' => 'more:%d'
        );
    }
    if(is_bool($c->comments)) {
        $c->comments = (object) array(
            'allow' => $c->comments,
            'moderation' => $c->comment_moderation
        );
    }
    if($c->html_parser === false) $c->html_parser = 'HTML';
    if( ! is_object($c->html_parser)) {
        $c->html_parser = (object) array(
            'type' => array('HTML' => 'HTML'),
            'active' => $c->html_parser
        );
    }
    $menus = Get::state_menu(null, false);
    if( ! isset($menus['navigation'])) {
        $menus = array('navigation' => $menus);
        File::serialize($menus)->saveTo(STATE . DS . 'menu.txt', 0600);
    }
    if($tag = Get::state_tag(null, false)) {
        if(isset($tag[0]['id'])) {
            $tags = array();
            foreach($tag as $t) {
                $tags[$t['id']] = array(
                    'name' => $t['name'],
                    'slug' => $t['slug'],
                    'description' => $t['description'],
                    'scope' => 'article'
                );
            }
            File::serialize($tags)->saveTo(STATE . DS . 'tag.txt', 0600);
        }
    }
    if(is_string($c->author)) {
        $c->author = (object) array(
            'name' => $c->author,
            'email' => $c->author_email,
            'url' => $c->author_profile_url
        );
        if($c->page_type === 'manager') {
            Notify::info('<strong>1.2.0</strong> &mdash; In your <a href="' . $c->url . '/' . $c->manager->slug . '/shield">shield</a> files, change all <code>$c->author</code> data to <code>$c->author->name</code>, <code>$c->author_email</code> data to <code>$c->author->email</code> and <code>$c->author_profile_url</code> data to <code>$c->author->url</code>. Then go to the <a href="' . $c->url . '/' . $c->manager->slug . '/config">configuration manager page</a> to kill this message by pressing the <strong>Update</strong> button.');
        }
    }
    Config::set(Mecha::A($c));
    $config = $c;
}, 1);

Weapon::add('on_config_update', function() {
    // Self destruct ...
    File::open(__FILE__)->delete();
    Notify::clear();
    Notify::success(Config::speak('notify_success_updated', Config::speak('config')));
});