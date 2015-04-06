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
        $bools = array(
            'comments' => false,
            'comment_moderation' => false,
            'comment_notification_email' => false,
            'widget_year_first' => false,
            'widget_include_css' => false,
            'widget_include_js' => false,
            'html_minifier' => false,
            'html_parser' => 'HTML',
            'resource_versioning' => false
        );
        $pages = array(
            'index' => 'article',
            'tag' => 'tagged',
            'archive' => 'archive',
            'search' => 'search',
            'manager' => 'manager'
        );
        $slugs = array();
        if($files = Get::pages()) {
            foreach($files as $file) {
                list($_time, $_kind, $_slug) = explode('_', basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)), 3);
                $slugs[$_slug] = 1;
            }
        }
        foreach($bools as $bool => $fallback) {
            // Fixes for checkbox inputs
            $request[$bool] = Request::post($bool, $fallback);
        }
        foreach($pages as $page => $default) {
            // Fixes for slug pattern inputs
            $request[$page]['slug'] = Text::parse(Request::post($page . '.slug', $default), '->slug');
            if( // If ...
                // Should be greater than 0
                Request::post($page . '.per_page') < 1 ||
                // Should be a fixed number
                floor(Request::post($page . '.per_page')) != Request::post($page . '.per_page')
            ) {
                Notify::error($speak->notify_invalid_per_page_number);
                Guardian::memorize($request);
            }
            // Check if slug already exist on static pages
            if(isset($slugs[$request[$page]['slug']])) {
                Notify::error(Config::speak('notify_error_slug_exist', array($request[$page]['slug'])));
                Guardian::memorize($request);
            }
        }
        if(Request::post('per_page') < 1 || floor(Request::post('per_page')) != Request::post('per_page')) {
            Notify::error($speak->notify_invalid_per_page_number);
            Guardian::memorize($request);
        }
        // Check for invalid email address
        if(trim($request['author_email']) !== "" && ! Guardian::check($request['author_email'], '->email')) {
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
            foreach(glob(SYSTEM . DS . 'log' . DS . 'asset.*.log') as $asset_cache) {
                File::open($asset_cache)->delete();
            }
            Weapon::fire('on_config_update', array($G, $P));
            Guardian::kick($request['manager']['slug'] . '/config');
        }
    }
    Shield::attach('manager', false);
});