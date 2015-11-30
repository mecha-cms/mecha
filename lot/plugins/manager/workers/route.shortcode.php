<?php


/**
 * Shortcode Manager
 * -----------------
 */

Route::accept($config->manager->slug . '/shortcode', function() use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $shortcodes = Get::state_shortcode(null, array(), false);
    $G = array('data' => $shortcodes);
    Config::set(array(
        'page_title' => $speak->shortcodes . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.shortcode.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $data = array();
        for($i = 0, $keys = $request['key'], $count = count($keys); $i < $count; ++$i) {
            if(trim($keys[$i]) !== "") {
                $data[$keys[$i]] = $request['value'][$i];
            }
        }
        $P = array('data' => $data);
        File::serialize($data)->saveTo(STATE . DS . 'shortcode.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->shortcode));
        Weapon::fire('on_shortcode_update', array($G, $P));
        Guardian::kick($config->url_current);
    }
    Shield::lot(array(
        'segment' => 'shortcode',
        'files' => Mecha::O($shortcodes)
    ))->attach('manager');
});