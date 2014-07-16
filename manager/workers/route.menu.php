<?php


/**
 * Menu Manager
 * ------------
 */

Route::accept($config->manager->slug . '/menu', function() use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if($file = File::exist(STATE . DS . 'menus.txt')) {
        $menus = File::open($file)->read();
    } else {
        $menus = $speak->home . ": /\n" . $speak->about . ": /about";
    }
    Config::set(array(
        'page_title' => $speak->menu . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'menu.php'
    ));
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo '<script>
(function($) {
    new MTE($(\'textarea[name="content"]\')[0], {
        tabSize: \'    \',
        toolbar: false
    });
})(Zepto);
</script>';
    });
    $G = array('data' => array('content' => $menus));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Checks for invalid input
        if(preg_match('#(^|\n)(\t| {1,3})(?:[^ ])#', $request['content'])) {
            Notify::error($speak->notify_invalid_indent_character);
            Guardian::memorize($request);
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::write($request['content'])->saveTo(STATE . DS . 'menus.txt', 0600);
            Notify::success(Config::speak('notify_success_updated', array($speak->menu)));
            Weapon::fire('on_menu_update', array($G, $P));
            Guardian::kick($config->url_current);
        }
    }
    Shield::define('the_content', $menus)->attach('manager', false);
});