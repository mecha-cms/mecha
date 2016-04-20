<?php


/**
 * Configuration Manager
 * ---------------------
 */

Route::accept($config->manager->slug . '/config', function() use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->configs . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.config.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $bools = array(
            'comments.allow' => false,
            'comments.moderation' => false,
            'widget_include_css' => false,
            'widget_include_js' => false,
            'html_parser.active' => Request::post('html_parser.active', false)
        );
        $pages = array(
            'index' => 'article',
            'tag' => 'tag',
            'archive' => 'archive',
            'search' => 'search',
            'manager' => 'manager'
        );
        foreach($bools as $bool => $fallback) {
            // Fix(es) for checkbox input(s)
            Mecha::SVR($request, $bool, Request::post($bool, $fallback));
        }
        foreach($pages as $page => $default) {
            // Fix(es) for slug pattern input(s)
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
        }
        if(Request::post('per_page') < 1 || floor(Request::post('per_page')) != Request::post('per_page')) {
            Notify::error($speak->notify_invalid_per_page_number);
            Guardian::memorize($request);
        }
        // Check for invalid email address
        if(trim($request['author']['email']) !== "" && ! Guardian::check($request['author']['email'], '->email')) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }
        unset($request['token']); // Remove token from request array
        $G = array('data' => Mecha::A($config));
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::serialize($request)->saveTo(STATE . DS . 'config.txt', 0600);
            Notify::success(Config::speak('notify_success_updated', $speak->config));
            foreach(glob(CACHE . DS . 'asset.*.log', GLOB_NOSORT) as $asset_cache) {
                File::open($asset_cache)->delete(); // clear cache log ...
            }
            Weapon::fire('on_config_update', array($G, $P));
            Guardian::kick($request['manager']['slug'] . '/config');
        }
    }
    Shield::lot(array('segment' => 'config'))->attach('manager');
});