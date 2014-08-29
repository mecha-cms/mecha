<?php


/**
 * Configuration Manager
 * ---------------------
 */

Route::accept($config->manager->slug . '/config', function() use($config, $speak) {
    Config::set(array(
        'page_title' => $speak->configs . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'config.php'
    ));
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Fixes for checkbox input
        $request['comments'] = Request::post('comments') ? true : false;
        $request['comment_moderation'] = Request::post('comment_moderation') ? true : false;
        $request['email_notification'] = Request::post('email_notification') ? true : false;
        $request['widget_year_first'] = Request::post('widget_year_first') ? true : false;
        $request['resource_versioning'] = Request::post('resource_versioning') ? true : false;
        // Fixes for slug pattern input
        $request['index']['slug'] = Text::parse(Request::post('index.slug', 'article'))->to_slug;
        $request['tag']['slug'] = Text::parse(Request::post('tag.slug', 'tagged'))->to_slug;
        $request['archive']['slug'] = Text::parse(Request::post('archive.slug', 'archive'))->to_slug;
        $request['search']['slug'] = Text::parse(Request::post('search.slug', 'search'))->to_slug;
        $request['manager']['slug'] = Text::parse(Request::post('manager.slug', 'manager'))->to_slug;
        if( // If ...
            // Should be greater than 0
            (int) Request::post('per_page') < 1 ||
            (int) Request::post('index.per_page') < 1 ||
            (int) Request::post('tag.per_page') < 1 ||
            (int) Request::post('archive.per_page') < 1 ||
            (int) Request::post('search.per_page') < 1 ||
            (int) Request::post('manager.per_page') < 1 ||
            // Should be a fixed number
            floor(Request::post('per_page')) != Request::post('per_page') ||
            floor(Request::post('index.per_page')) != Request::post('index.per_page') ||
            floor(Request::post('tag.per_page')) != Request::post('tag.per_page') ||
            floor(Request::post('archive.per_page')) != Request::post('archive.per_page') ||
            floor(Request::post('search.per_page')) != Request::post('search.per_page') ||
            floor(Request::post('manager.per_page')) != Request::post('manager.per_page')
        ) {
            Notify::error($speak->notify_invalid_per_page_number);
            Guardian::memorize($request);
        }
        // Check if slug already exist on static pages
        $slugs = array();
        if($files = Get::pages()) {
            foreach($files as $file) {
                list($_time, $_kind, $_slug) = explode('_', basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)));
                $slugs[$_slug] = 1;
            }
        }
        if(isset($slugs[$request['index']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['index']['slug'])));
            Guardian::memorize($request);
        }
        if(isset($slugs[$request['tag']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['tag']['slug'])));
            Guardian::memorize($request);
        }
        if(isset($slugs[$request['archive']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['archive']['slug'])));
            Guardian::memorize($request);
        }
        if(isset($slugs[$request['search']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['search']['slug'])));
            Guardian::memorize($request);
        }
        if(isset($slugs[$request['manager']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['manager']['slug'])));
            Guardian::memorize($request);
        }
        // Checks for invalid email address
        if(trim($request['author_email']) !== "" && ! Guardian::check($request['author_email'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }
        unset($request['token']); // Remove token from request array
        $G = array('data' => Mecha::A($config));
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::serialize($request)->saveTo(STATE . DS . 'config.txt', 0600);
            Config::load(); // Refresh the configuration data ...
            Notify::success(Config::speak('notify_success_updated', array(Config::speak('config'))));
            Weapon::fire('on_config_update', array($G, $P));
            Guardian::kick($request['manager']['slug'] . '/config');
        }
    }
    Shield::attach('manager', false);
});