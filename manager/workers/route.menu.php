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
        'page_title' => $speak->menus . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'menu.php'
    ));
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo '<script>
(function($, base) {
    if (typeof MTE == "undefined") return;
    base.fire(\'on_control_begin\', [\'menu\', \'content\']);
    base.editor = new MTE($(\'.MTE\')[0], {
        tabSize: \'    \', // Use 4 spaces for indentation!
        toolbar: false,
        click: function(e, editor, type) {
            base.fire(\'on_control_event_click\', [e, editor, type, [\'menu\', \'content\']]);
        },
        keydown: function(e, editor) {
            base.fire(\'on_control_event_keydown\', [e, editor, [\'menu\', \'content\']]);
        },
        ready: function(editor) {
            base.fire(\'on_control_event_ready\', [editor, [\'menu\', \'content\']]);
        }
    });
    base.editor_content = base.editor;
    base.fire(\'on_control_end\', [\'menu\', \'content\']);
})(Zepto, DASHBOARD);
</script>';
    });
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
            File::write($request['content'])->saveTo(STATE . DS . 'menus.txt', 0600);
            Notify::success(Config::speak('notify_success_updated', array($speak->menu)));
            Weapon::fire('on_menu_update', array($G, $P));
            Guardian::kick($config->url_current);
        }
    }
    Shield::define('the_content', $menus)->attach('manager', false);
});