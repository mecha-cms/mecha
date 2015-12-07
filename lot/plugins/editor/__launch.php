<?php

// Load the configuration data
$editor_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();

// Merge language data to the `DASHBOARD`
Weapon::add('shield_before', function() use($editor_config) {
    // Manage editor button(s)
    $editor = Config::speak('MTE');
    foreach($editor->buttons as $k => $v) {
        if( ! Text::check($k)->in(array('yes', 'no', 'ok', 'cancel', 'open', 'close'))) {
            if( ! isset($editor_config['buttons'][$k])) {
                $editor->buttons->{$k} = false;
            }
        }
    }
    // Merge language data to the `DASHBOARD`
    Config::merge('DASHBOARD.languages.MTE', $editor);
}, 1);

// Inject editor's CSS
Weapon::add('shell_after', function() use($editor_config) {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'editor.css', "", 'shell/editor.min.css');
}, 2);

// Inject editor's JavaScript
Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak) {
    echo '<script>(function($) {
    $(\'.MTE[name="content"], .MTE[name="message"]\').attr(\'data-MTE-config\', \'{"toolbar":true,"shortcut":true}\');
})(window.Zepto || window.jQuery);</script>';
    $path = __DIR__ . DS . 'assets' . DS . 'sword' . DS;
    $editor = Config::get('MTE', 'HTE');
    echo Asset::javascript(array(
        $path . 'editor.min.js',
        $path . 'hte.min.js'
    ));
    echo '<script>var MTE = ' . $editor . ';</script>';
    echo Asset::javascript($path . 'run.js', "", 'sword/run.min.js');
}, 2);


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        unset($request['token']); // Remove token from request array
        File::serialize($request)->saveTo(__DIR__ . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick(File::D($config->url_current));
    }
});