<?php


/**
 * Menu Manager
 * ------------
 */

Route::accept($config->manager->slug . '/menu', function() use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $menus = Get::state_menu($speak->home . S . " /\nRSS" . S . " /feed");
    Config::set(array(
        'page_title' => $speak->menus . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'menu.php'
    ));
    $G = array('data' => array('content' => $menus));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid input
        if(preg_match('#(^|\n)(\t| {1,3})(?:[^ ])#', $request['content'])) {
            Notify::error($speak->notify_invalid_indent_character);
            Guardian::memorize($request);
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::write($request['content'])->saveTo(STATE . DS . 'menu.txt', 0600);
            Notify::success(Config::speak('notify_success_updated', $speak->menu));
            Weapon::fire('on_menu_update', array($G, $P));
            Guardian::kick($config->url_current);
        }
    }
    Shield::define('the_content', $menus)->attach('manager', false);
});